<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_post_tag', function (Blueprint $table) {
            $table->increments('post_tag_id');
            $table->integer('post_id')->unsigned()->nullable();
            $table->integer('note_id')->unsigned()->nullable();
            $table->integer('tag_id')->unsigned();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_post_tag');
    }
};