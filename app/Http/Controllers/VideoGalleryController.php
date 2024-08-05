<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Thumb;
use App\Models\Video;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

class VideoGalleryController extends Controller
{

    public function index(int $page = 1)
    {
        $default_title = 'Home' . " Page " . $page;
        $default_description = "the best free adult content";
        $paginator = Video::with(['tags', 'thumbs'])
            ->orderBy('views', 'desc')
            ->paginate(20, ['*'], 'page', $page);

        $mappedVideos = $this->mapVideos($paginator->items());

        return view('gallery', [
            'videos' => new LengthAwarePaginator(
                $mappedVideos,
                $paginator->total(),
                $paginator->perPage(),
                $paginator->currentPage(),
                ['path' => Request::url(), 'query' => Request::query()]
            ),
            'default_title' => $default_title,
            'default_description' => $default_description,
        ]);
    }

    public function galleryByTag(string $tag, int $page = 1)
    {
        $tagModel = Tag::where('tag_name', $tag)->first();

        if (!$tagModel) {
            return redirect('/404');
        }

        $default_title = 'Tag: ' . $tag . " Page $page";
        $default_description = 'Content tagged with ' . $tag;

        $paginator = Video::whereHas('tags', function ($query) use ($tag) {
            $query->where('tag_name', $tag);
        })
            ->with(['tags', 'thumbs'])
            ->orderBy('views', 'desc')
            ->paginate(20, ['*'], 'page', $page);

        $mappedVideos = $this->mapVideos($paginator->items());

        return view('gallery', [
            'videos' => new LengthAwarePaginator(
                $mappedVideos,
                $paginator->total(),
                $paginator->perPage(),
                $paginator->currentPage(),
                ['path' => Request::url(), 'query' => Request::query()]
            ),
            'tag' => $tag,
            'default_title' => $default_title,
            'default_description' => $default_description,
        ]);
    }

    public function searchVideos(int $page = 1)
    {
        $searchTerm = Request::input('search');
        if (!$searchTerm) {
            return redirect()->back();
        }

        $default_title = 'Search Results for: ' . $searchTerm . ' - Page ' . $page;
        $default_description = 'Videos matching the search term: ' . $searchTerm;

        $paginator = Video::where('title', 'like', '%' . $searchTerm . '%')
            ->orWhere('keywords', 'like', '%' . $searchTerm . '%')
            ->orWhereHas('tags', function ($query) use ($searchTerm) {
                $query->where('tag_name', 'like', '%' . $searchTerm . '%');
            })
            ->with(['tags', 'thumbs'])
            ->orderBy('views', 'desc')
            ->paginate(20, ['*'], 'page', $page);

        $mappedVideos = $this->mapVideos($paginator->items());

        return view('gallery', [
            'videos' => new LengthAwarePaginator(
                $mappedVideos,
                $paginator->total(),
                $paginator->perPage(),
                $paginator->currentPage(),
                ['path' => Request::url(), 'query' => Request::query()]
            ),
            'searchTerm' => $searchTerm,
            'default_title' => $default_title,
            'default_description' => $default_description,
        ]);
    }

    private function mapVideos(array $videos)
    {
        return array_map(function ($video) {
            $thumbs = $video->thumbs;
            $defaultThumb = $thumbs->get(2) ? $thumbs->get(2)->src : ($thumbs->get(1) ? $thumbs->get(1)->src : ($thumbs->get(0) ? $thumbs->get(0)->src : $video->default_thumb));

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
                'default_thumb' => $defaultThumb,
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
    }

    public function tags()
    {
        $tags = Tag::orderBy('tag_name', 'asc')->get();

        $alphaTags = $tags->filter(function ($tag) {
            return preg_match('/^[a-zA-Z]/', $tag->tag_name);
        });

        $nonAlphaTags = $tags->filter(function ($tag) {
            return !preg_match('/^[a-zA-Z]/', $tag->tag_name);
        });

        $groupedTags = $alphaTags->groupBy(function ($tag) {
            return strtoupper(substr($tag->tag_name, 0, 1));
        });

        if ($nonAlphaTags->isNotEmpty()) {
            $groupedTags['#'] = $nonAlphaTags;
        }

        $letters = $groupedTags->keys()->toArray();

        $usedThumbs = [];

        $formattedTags = $groupedTags->map(function ($tags) use (&$usedThumbs) {
            return $tags->map(function ($tag) use (&$usedThumbs) {
                $thumb = Thumb::join('videos', 'thumbs.video_id', '=', 'videos.id')
                    ->join('tag_video', 'videos.id', '=', 'tag_video.video_id')
                    ->where('tag_video.tag_id', $tag->id)
                    ->whereNotIn('thumbs.id', $usedThumbs)
                    ->select('thumbs.*')
                    ->first();

                if ($thumb) {
                    $usedThumbs[] = $thumb->id;
                }

                return [
                    'tag_title' => str_replace('-', ' ', $tag->tag_name),
                    'tag' => $tag->tag_name,
                    'thumb_src' => $thumb ? $thumb->src : null,
                ];
            });
        });

        $default_title = 'Tag Page - Best Tags';
        $default_description = 'Explore the best tags for our video content.';

        return view('tags.index', [
            'groupedTags' => $formattedTags,
            'letters' => $letters,
            'default_title' => $default_title,
            'default_description' => $default_description,
        ]);
    }

    public function show(string $video_id)
    {
        $video = Video::with(['tags', 'thumbs'])->where('video_id', $video_id)->first();
        $isRelated = true;
        if (!$video) {
            return redirect('/404');
        }

        $default_title = $video->title;
        $default_description = $video->keywords;
        $page_thumb = $video->thumbs->first()->src ?? asset('icon.png');

        $relatedVideos = Video::whereHas('tags', function ($query) use ($video) {
            return $query->whereIn('tag_name', $video->tags->pluck('tag_name'));
        })
            ->where('video_id', '!=', $video->video_id)
            ->with(['tags', 'thumbs'])
            ->limit(9)
            ->get();

        $isVideoPage = true;

        return view('video.show', compact('video', 'relatedVideos', 'isRelated', 'default_title', 'default_description', 'page_thumb', 'isVideoPage'));
    }

    private function normalizeTitle(string $title)
    {
        $title = preg_replace('/[^A-Za-z0-9]+/', '-', $title);
        $title = strtolower($title);
        return trim(preg_replace('/-+/', '-', $title), '-');
    }
}
