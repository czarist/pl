<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Thumb;
use App\Models\Video;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

class VideoGalleryController extends Controller
{

    public function index()
    {
        $paginator = Video::with(['tags', 'thumbs'])->paginate(20);

        $videos = $paginator->items(); // Obtém a coleção de itens paginados

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

    public function fetchEpornerVideos()
    {
        $newVideosCount = 0;
        $page = 1;

        while ($newVideosCount < 1000) {
            $response = json_decode(file_get_contents("https://www.eporner.com/api/v2/video/search/?thumbsize=big&page=$page"));

            if (!$response || !isset($response->videos) || empty($response->videos)) {
                break;
            }

            foreach ($response->videos as $videoData) {
                if (Video::where('url', $videoData->url)->exists()) {
                    continue;
                }

                try {
                    $video = Video::create([
                        'provider' => 'eporner',
                        'video_id' => $videoData->id,
                        'title' => $videoData->title,
                        'keywords' => $videoData->keywords,
                        'views' => $videoData->views,
                        'rate' => $videoData->rate,
                        'url' => $videoData->url,
                        'embed_url' => $videoData->embed,
                        'length_sec' => $videoData->length_sec,
                        'length_min' => $videoData->length_min,
                        'default_thumb' => $videoData->default_thumb->src,
                        'added' => $videoData->added,
                    ]);

                    foreach (explode(', ', $videoData->keywords) as $keyword) {
                        try {
                            $tag = Tag::firstOrCreate(['tag_name' => $keyword]);
                            $video->tags()->attach($tag);
                        } catch (\Exception $e) {
                            continue;
                        }
                    }

                    foreach ($videoData->thumbs as $thumbData) {
                        Thumb::create([
                            'video_id' => $video->id,
                            'size' => $thumbData->size,
                            'width' => $thumbData->width,
                            'height' => $thumbData->height,
                            'src' => $thumbData->src,
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

            if ($page > $response->total_pages) {
                break;
            }
        }
    }

    public function fetchRedtubeVideos()
    {
        $newVideosCount = 0;
        $page = 1;

        while ($newVideosCount < 1000) {
            $response = json_decode(file_get_contents("https://api.redtube.com/?data=redtube.Videos.searchVideos&output=json&thumbsize=big&page=$page"));

            if (!$response || !isset($response->videos) || empty($response->videos)) {
                break;
            }

            $totalPages = ceil($response->count / 20);

            foreach ($response->videos as $videoWrapper) {
                $videoData = $videoWrapper->video;

                if (Video::where('url', $videoData->url)->exists()) {
                    continue;
                }

                try {
                    $video = Video::create([
                        'provider' => 'redtube',
                        'video_id' => $videoData->video_id,
                        'title' => $videoData->title,
                        'keywords' => implode(', ', array_map(function ($tag) {return $tag->tag_name;}, $videoData->tags)),
                        'views' => $videoData->views,
                        'rate' => $videoData->rating,
                        'url' => $videoData->url,
                        'embed_url' => $videoData->embed_url,
                        'length_sec' => $this->convertDurationToSeconds($videoData->duration),
                        'length_min' => $videoData->duration,
                        'default_thumb' => $videoData->default_thumb,
                        'added' => $videoData->publish_date,
                    ]);

                    foreach ($videoData->tags as $tagData) {
                        try {
                            $tag = Tag::firstOrCreate(['tag_name' => $tagData->tag_name]);
                            $video->tags()->attach($tag);
                        } catch (\Exception $e) {
                            continue;
                        }
                    }

                    foreach ($videoData->thumbs as $thumbData) {
                        Thumb::create([
                            'video_id' => $video->id,
                            'size' => $thumbData->size,
                            'width' => $thumbData->width,
                            'height' => $thumbData->height,
                            'src' => $thumbData->src,
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

    private function convertDurationToSeconds($duration)
    {
        sscanf($duration, "%d:%d", $minutes, $seconds);
        return $minutes * 60 + $seconds;
    }
}
