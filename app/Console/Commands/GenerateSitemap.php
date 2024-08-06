<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;

class GenerateSitemap extends Command
{
    // php artisan generate:sitemap

    protected $signature = 'generate:sitemap';
    protected $description = 'Generate the sitemap for the website';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sitemapPath = public_path('sitemap.xml');

        $urls = [
            [
                'loc' => URL::to('/'),
                'priority' => '1.0',
                'changefreq' => 'daily',
                'lastmod' => now()->toAtomString(),
            ],
            [
                'loc' => URL::to('/tags'),
                'priority' => '0.7',
                'changefreq' => 'daily',
                'lastmod' => now()->toAtomString(),
            ],
            [
                'loc' => URL::to('/search'),
                'priority' => '0.5',
                'changefreq' => 'monthly',
                'lastmod' => now()->toAtomString(),
            ],
        ];

        $videos = \App\Models\Video::all();
        foreach ($videos as $video) {
            $sanitizedTitle = $this->sanitizeTitle($video->title);
            $loc = URL::to("/video/{$video->video_id}/{$sanitizedTitle}");
            $urls[] = [
                'loc' => $loc,
                'priority' => '0.9',
                'changefreq' => 'weekly',
                'lastmod' => $video->updated_at->toAtomString(),
                'video' => [
                    'title' => $video->title,
                    'description' => $video->keywords,
                    'thumbnail_loc' => $video->thumbs->first()->src ?? asset('icon.png'),
                    'duration' => $video->length_sec,
                    'publication_date' => $video->created_at->toAtomString(),
                    'expiration_date' => now()->addYears(1)->toAtomString(),
                    'rating' => $video->rate,
                ],
            ];
        }

        $tags = \App\Models\Tag::all();
        foreach ($tags as $tag) {
            $loc = URL::to("/tag/{$tag->tag_name}");
            $urls[] = [
                'loc' => $loc,
                'priority' => '0.6',
                'changefreq' => 'monthly',
                'lastmod' => now()->toAtomString(),
            ];
        }

        $xml = $this->generateSitemapXML($urls);

        File::put($sitemapPath, $xml);

        $this->info('Sitemap generated successfully.');
    }

    private function generateSitemapXML(array $urls)
    {
        $xml = new \SimpleXMLElement('<urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xml->addAttribute('xmlns:video', 'http://www.google.com/schemas/sitemap-video/1.1');

        foreach ($urls as $url) {
            $urlElement = $xml->addChild('url');
            $urlElement->addChild('loc', $this->escapeForXML($url['loc']));
            $urlElement->addChild('priority', $this->escapeForXML($url['priority']));
            $urlElement->addChild('changefreq', $this->escapeForXML($url['changefreq']));
            $urlElement->addChild('lastmod', $this->escapeForXML($url['lastmod']));

            if (isset($url['video'])) {
                $videoElement = $urlElement->addChild('video:video', '', 'http://www.google.com/schemas/sitemap-video/1.1');
                $videoElement->addChild('video:title', $this->escapeForXML($url['video']['title']));
                $videoElement->addChild('video:description', $this->escapeForXML($url['video']['description']));
                $videoElement->addChild('video:thumbnail_loc', $this->escapeForXML($url['video']['thumbnail_loc']));
                $videoElement->addChild('video:duration', $this->escapeForXML($url['video']['duration']));
                $videoElement->addChild('video:publication_date', $this->escapeForXML($url['video']['publication_date']));
                $videoElement->addChild('video:expiration_date', $this->escapeForXML($url['video']['expiration_date']));
                $videoElement->addChild('video:rating', $this->escapeForXML($url['video']['rating']));
            }
        }

        return $xml->asXML();
    }

    private function sanitizeTitle($title)
    {
        $title = preg_replace('/[^\w\-]/', '-', $title);
        $title = preg_replace('/-+/', '-', $title);
        return trim($title, '-');
    }

    private function escapeForXML($string)
    {
        return htmlspecialchars($string, ENT_XML1, 'UTF-8');
    }
}
