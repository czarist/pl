<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTable extends Migration
{
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('provider'); // e.g., 'eporner' or 'redtube'
            $table->string('video_id');
            $table->string('title');
            $table->text('keywords')->nullable();
            $table->integer('views');
            $table->float('rate');
            $table->string('url');
            $table->string('embed_url');
            $table->integer('length_sec');
            $table->string('length_min');
            $table->string('default_thumb')->nullable();
            $table->timestamp('added');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('videos');
    }
}
