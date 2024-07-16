<?php

namespace App\Http\Controllers;

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

    private function normalizeTitle($title)
    {
        $title = preg_replace('/[^A-Za-z0-9]+/', '-', $title);
        $title = strtolower($title);
        return trim(preg_replace('/-+/', '-', $title), '-');
    }
}
