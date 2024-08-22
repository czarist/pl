<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VideoService;

class CheckInactiveVideos extends Command
{
    protected $signature = 'videos:check-inactive';
    protected $description = 'Check and delete inactive videos from the database';

    protected $videoService;

    public function __construct(VideoService $videoService)
    {
        parent::__construct();
        ini_set('memory_limit', '512M');
        $this->videoService = $videoService;
    }

    public function handle()
    {
        $this->videoService->checkAndDeleteInactiveVideos();
        $this->info('Check for inactive videos completed.');
        return Command::SUCCESS;
    }
}


// php artisan videos:check-inactive