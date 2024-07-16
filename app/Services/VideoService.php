<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\Thumb;
use App\Models\Video;

class VideoService
{
    public function checkAndDeleteInactiveVideos()
    {
        $videos = Video::orderBy('provider')->get();

        foreach ($videos as $video) {
            $videoId = $video->video_id;
            $provider = $video->provider;
            $isActive = false;

            if ($provider === 'eporner') {
                $response = $this->curlRequest("https://www.eporner.com/api/v2/video/id/?id={$videoId}");
                if (isset($response['id'])) {
                    $isActive = true;
                }
            } elseif ($provider === 'redtube') {
                $response = $this->curlRequest("https://api.redtube.com/?data=redtube.Videos.isVideoActive&video_id={$videoId}&output=json");
                if (isset($response['active']) && $response['active']['is_active'] === true) {
                    $isActive = true;
                }
            }

            if (!$isActive) {
                $video->tags()->detach();
                $video->thumbs()->delete();
                $video->delete();
                echo "\e[31mVideo '{$video->title}' deleted due to inactivity.\e[0m\n";
            } else {
                echo "\e[32mVideo '{$video->title}' is still active.\e[0m\n";
            }
        }
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
                        'title' => $this->convertToUtf8($videoData['title']),
                        'keywords' => $this->convertToUtf8($videoData['keywords']),
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
                        $processedTag = $this->processTag($this->convertToUtf8($keyword));
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

                    echo "\e[36mVideo '{$video->title}' successfully stored.\e[0m\n";

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

                    echo "\e[36mVideo '{$video->title}' successfully stored.\e[0m\n";

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
}
