<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; 

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('video_id');
            $table->string('title');
            $table->text('keywords')->nullable();
            $table->integer('views')->default(0);
            $table->decimal('rate', 3, 2)->default(0);
            $table->string('url');
            $table->string('embed_url');
            $table->integer('length_sec')->nullable();
            $table->string('length_min')->nullable();
            $table->string('default_thumb')->nullable();
            $table->timestamp('added')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
    }
}
