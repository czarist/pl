<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteUnusedTags extends Command
{
    protected $signature = 'tags:delete-unused';
    protected $description = 'Exclui tags que não possuem vídeos relacionados';

    public function __construct()
    {
        parent::__construct();
        ini_set('memory_limit', '512M');
    }

    public function handle()
    {
        $this->info('Iniciando o processo de exclusão de tags sem vídeos...');

        $tags = DB::table('tags')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('tag_video')
                      ->whereColumn('tag_video.tag_id', 'tags.id');
            })
            ->get();

        $count = count($tags);

        if ($count > 0) {
            $this->info("Encontradas {$count} tags sem vídeos relacionados. Excluindo...");

            foreach ($tags as $tag) {
                DB::table('tags')->where('id', $tag->id)->delete();
                $this->info("Tag '{$tag->tag_name}' excluída.");
            }

            $this->info('Processo de exclusão concluído.');
        } else {
            $this->info('Nenhuma tag sem vídeos relacionados foi encontrada.');
        }
    }
}


// php artisan tags:delete-unused