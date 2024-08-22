<?php

namespace App\Console\Commands;

use App\Services\VideoService;
use Illuminate\Console\Command;

class FetchVideos extends Command
{
    protected $signature = 'videos:fetch';
    protected $description = 'Fetch videos from all sources (eporner, redtube)';

    protected $videoService;

    public function __construct(VideoService $videoService)
    {
        parent::__construct();
        ini_set('memory_limit', '512M');
        $this->videoService = $videoService;
    }

    public function handle()
    {
        $this->videoService->fetchEpornerVideos();
        $this->info('Eporner videos fetched successfully.');

        $this->videoService->fetchRedtubeVideos();
        $this->info('Redtube videos fetched successfully.');

        return Command::SUCCESS;
    }
}

// php artisan videos:fetch
