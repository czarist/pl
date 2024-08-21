<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;

class GenerateSitemap extends Command
{
    protected $signature = 'generate:sitemap';
    protected $description = 'Generate the sitemap for the website';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Iniciando a geração do sitemap.');

        $sitemapIndexPath = public_path('sitemap_index.xml');
        $sitemapDir = public_path('sitemaps');

        if (!File::exists($sitemapDir)) {
            $this->info('Diretório de sitemaps não existe. Criando o diretório...');
            File::makeDirectory($sitemapDir);
        } else {
            $this->info('Diretório de sitemaps já existe.');
        }

        $urls = [
            // Seu array de URLs fixos aqui
        ];

        // Adiciona URLs de vídeos e tags
        $this->addVideoUrls($urls);
        $this->addTagUrls($urls);

        $this->info('Total de URLs geradas: ' . count($urls));

        // Divida os URLs em chunks de 100
        $chunks = array_chunk($urls, 100);

        $this->info('Total de chunks de URLs: ' . count($chunks));

        $sitemapIndex = new \SimpleXMLElement('<sitemapindex/>');
        $sitemapIndex->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($chunks as $index => $chunk) {
            $sitemapPath = $sitemapDir . "/sitemap_{$index}.xml";
            $sitemapUrl = URL::to("sitemaps/sitemap_{$index}.xml");

            $this->info("Gerando sitemap para o chunk {$index}...");
            $xml = $this->generateSitemapXML($chunk);

            if (File::put($sitemapPath, $xml)) {
                $this->info("Sitemap gerado com sucesso: {$sitemapPath}");
            } else {
                $this->error("Erro ao salvar o sitemap: {$sitemapPath}");
            }

            $sitemap = $sitemapIndex->addChild('sitemap');
            $sitemap->addChild('loc', $sitemapUrl);
            $sitemap->addChild('lastmod', now()->toAtomString());
        }

        if (File::put($sitemapIndexPath, $sitemapIndex->asXML())) {
            $this->info('Sitemap index gerado com sucesso.');
        } else {
            $this->error('Erro ao salvar o sitemap index.');
        }

        $this->info('Processo de geração do sitemap concluído.');
    }

    private function addVideoUrls(&$urls)
    {
        $this->info('Adicionando URLs de vídeos...');

        $page = 1;
        $perPage = 1000; // Processa 1000 vídeos por vez

        do {
            $videos = DB::table('videos')
                ->orderBy('id')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            $totalVideos = count($videos);
            $this->info("Processando página {$page} com {$totalVideos} vídeos.");

            foreach ($videos as $video) {
                $sanitizedTitle = $this->sanitizeTitle($video->title);
                $loc = URL::to("/video/{$video->video_id}/{$sanitizedTitle}");
                $urls[] = [
                    'loc' => $loc,
                    'priority' => '0.9',
                    'changefreq' => 'weekly',
                    'lastmod' => $video->updated_at,
                    'video' => [
                        'title' => $video->title,
                        'description' => $video->keywords,
                        'thumbnail_loc' => $video->thumbnail_url ?? asset('icon.png'),
                        'duration' => $video->length_sec,
                        'publication_date' => $video->created_at,
                        'expiration_date' => now()->addYears(1)->toAtomString(),
                        'rating' => $video->rate,
                    ],
                ];
            }

            $page++;
        } while ($totalVideos > 0);

        $this->info('Todas as páginas foram processadas.');
    }

    private function addTagUrls(&$urls)
    {
        $this->info('Adicionando URLs de tags...');
        $tags = DB::table('tags')->get();
        $this->info('Total de tags encontradas: ' . count($tags));

        foreach ($tags as $tag) {
            $loc = URL::to("/tag/{$tag->tag_name}");
            $urls[] = [
                'loc' => $loc,
                'priority' => '0.6',
                'changefreq' => 'monthly',
                'lastmod' => now()->toAtomString(),
            ];
        }
    }

    private function generateSitemapXML(array $urls)
    {
        $this->info('Gerando XML para o chunk...');
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

// php artisan generate:sitemap
