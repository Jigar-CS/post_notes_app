<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_post', function (Blueprint $table) {
            $table->increments('post_id');
            $table->integer('user_id')->unsigned();
            $table->integer('category_id')->unsigned()->nullable();
            $table->string('title', 255);
            $table->longText('content');
            $table->string('featured_image', 255)->nullable();
            $table->integer('is_public')->default(1); // 1 = Public, 0 = Private
            $table->timestamp('created_at')->useCurrent();
            $table->integer('post_status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_post');
    }
};