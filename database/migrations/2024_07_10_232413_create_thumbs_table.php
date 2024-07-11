<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThumbsTable extends Migration
{
    public function up()
    {
        Schema::create('thumbs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->string('size');
            $table->integer('width');
            $table->integer('height');
            $table->string('src');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('thumbs');
    }
}
