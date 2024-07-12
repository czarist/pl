<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Thumb;
use App\Models\Video;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

class VideoGalleryController extends Controller
{

    public function index($page = 1)
    {
        $paginator = Video::with(['tags', 'thumbs'])->paginate(20, ['*'], 'page', $page);

        $videos = $paginator->items();

        $mappedVideos = array_map(function ($video) {
            return [
                'id' => $video->id,
                'provider' => $video->provider,
                'video_id' => $video->video_id,
                'title' => $video->title,
                'keywords' => $video->keywords,
                'views' => $video->views,
                'rate' => $video->rate,
                'url' => $video->url,
                'url_title' => $this->normalizeTitle($video->title),
                'embed_url' => $video->embed_url,
                'length_sec' => $video->length_sec,
                'length_min' => $video->length_min,
                'default_thumb' => $video->default_thumb,
                'added' => $video->added,
                'tags' => $video->tags->pluck('tag_name')->toArray(),
                'thumbs' => $video->thumbs->map(function ($thumb) {
                    return [
                        'size' => $thumb->size,
                        'width' => $thumb->width,
                        'height' => $thumb->height,
                        'src' => $thumb->src,
                    ];
                })->toArray(),
            ];
        }, $videos);

        return view('gallery', [
            'videos' => new LengthAwarePaginator(
                $mappedVideos,
                $paginator->total(),
                $paginator->perPage(),
                $paginator->currentPage(),
                ['path' => Request::url(), 'query' => Request::query()]
            ),
        ]);
    }

    public function show(string $video_id)
    {
        $video = Video::with(['tags', 'thumbs'])->where('video_id', $video_id)->first();

        if (!$video) {
            return redirect('/404');
        }

        return view('video.show', compact('video'));
    }

    public function fetchEpornerVideos()
    {
        $newVideosCount = 0;
        $page = 1;

        while ($newVideosCount < 200) {
            $response = $this->curlRequest("https://www.eporner.com/api/v2/video/search/?thumbsize=big&page=$page");

            if (!$response || !isset($response['videos']) || empty($response['videos'])) {
                break;
            }

            foreach ($response['videos'] as $videoData) {
                if (Video::where('url', $videoData['url'])->exists()) {
                    continue;
                }

                try {
                    $rate = min($videoData['rate'], 5);

                    $video = Video::create([
                        'provider' => 'eporner',
                        'video_id' => $videoData['id'],
                        'title' => $this->convertToUtf8Eporner($videoData['title']),
                        'keywords' => $this->convertToUtf8Eporner($videoData['keywords']),
                        'views' => $videoData['views'],
                        'rate' => $rate,
                        'url' => $videoData['url'],
                        'embed_url' => $videoData['embed'],
                        'length_sec' => $videoData['length_sec'],
                        'length_min' => $videoData['length_min'],
                        'default_thumb' => $videoData['default_thumb']['src'],
                        'added' => $videoData['added'],
                    ]);

                    foreach (explode(', ', $videoData['keywords']) as $keyword) {
                        $processedTag = $this->processTag($this->convertToUtf8Eporner($keyword));
                        if ($processedTag) {
                            try {
                                $tag = Tag::firstOrCreate(['tag_name' => $processedTag]);
                                $video->tags()->attach($tag);
                            } catch (\Exception $e) {
                                continue;
                            }
                        }
                    }

                    foreach ($videoData['thumbs'] as $thumbData) {
                        Thumb::create([
                            'video_id' => $video->id,
                            'size' => $thumbData['size'],
                            'width' => $thumbData['width'],
                            'height' => $thumbData['height'],
                            'src' => $thumbData['src'],
                        ]);
                    }

                    $newVideosCount++;

                    if ($newVideosCount >= 1000) {
                        break 2;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            $page++;

            if ($page > $response['total_pages']) {
                break;
            }
        }
    }

    public function fetchRedtubeVideos()
    {
        $newVideosCount = 0;
        $page = 1;

        while ($newVideosCount < 200) {
            $response = $this->curlRequest("https://api.redtube.com/?data=redtube.Videos.searchVideos&output=json&thumbsize=big&page=$page");

            if (!$response || !isset($response['videos']) || empty($response['videos'])) {
                break;
            }

            $totalPages = ceil($response['count'] / 20);

            foreach ($response['videos'] as $videoWrapper) {
                $videoData = $videoWrapper['video'];

                if (Video::where('url', $videoData['url'])->exists()) {
                    continue;
                }

                try {
                    $rate = min($videoData['rating'], 5);

                    $video = Video::create([
                        'provider' => 'redtube',
                        'video_id' => $videoData['video_id'],
                        'title' => $this->convertToUtf8($videoData['title']),
                        'keywords' => implode(', ', array_map(function ($tag) {
                            return $this->processTag($this->convertToUtf8($tag['tag_name']));
                        }, $videoData['tags'])),
                        'views' => $videoData['views'],
                        'rate' => $rate,
                        'url' => $videoData['url'],
                        'embed_url' => $videoData['embed_url'],
                        'length_sec' => $this->convertDurationToSeconds($videoData['duration']),
                        'length_min' => $videoData['duration'],
                        'default_thumb' => $videoData['default_thumb'],
                        'added' => $videoData['publish_date'],
                    ]);

                    foreach ($videoData['tags'] as $tagData) {
                        $processedTag = $this->processTag($this->convertToUtf8($tagData['tag_name']));
                        if ($processedTag) {
                            try {
                                $tag = Tag::firstOrCreate(['tag_name' => $processedTag]);
                                $video->tags()->attach($tag);
                            } catch (\Exception $e) {
                                continue;
                            }
                        }
                    }

                    foreach ($videoData['thumbs'] as $thumbData) {
                        Thumb::create([
                            'video_id' => $video->id,
                            'size' => $thumbData['size'],
                            'width' => $thumbData['width'],
                            'height' => $thumbData['height'],
                            'src' => $thumbData['src'],
                        ]);
                    }

                    $newVideosCount++;

                    if ($newVideosCount >= 1000) {
                        break 2;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            $page++;

            if ($page > $totalPages) {
                break;
            }
        }
    }

    private function curlRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    private function convertToUtf8($string)
    {
        $detectedEncoding = mb_detect_encoding($string, mb_detect_order(), true);
        if ($detectedEncoding) {
            return iconv($detectedEncoding, "UTF-8//IGNORE", $string);
        }

        return $string;
    }

    private function convertToUtf8Eporner($string)
    {
        $string = $this->convertToUtf8($string);
        $detectedEncoding = mb_detect_encoding($string, mb_detect_order(), true);
        if ($detectedEncoding) {
            $utf8String = iconv($detectedEncoding, "UTF-8//IGNORE", $string);
        } else {
            $utf8String = $string;
        }

        $latinChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $replacedString = preg_replace_callback('/[^\x20-\x7E]/', function ($matches) use ($latinChars) {
            return $latinChars[random_int(0, strlen($latinChars) - 1)];
        }, $utf8String);

        return $replacedString;
    }

    private function convertDurationToSeconds($duration)
    {
        sscanf($duration, "%d:%d", $minutes, $seconds);
        return $minutes * 60 + $seconds;
    }

    private function processTag($tag)
    {
        $words = explode(' ', $tag);
        if (count($words) > 2) {
            return false;
        }
        if (count($words) == 2) {
            return implode('-', $words);
        }
        return $tag;
    }

    private function normalizeTitle($title)
    {
        $title = preg_replace('/[^A-Za-z0-9]+/', '-', $title);
        $title = strtolower($title);
        return trim(preg_replace('/-+/', '-', $title), '-');
    }
}
