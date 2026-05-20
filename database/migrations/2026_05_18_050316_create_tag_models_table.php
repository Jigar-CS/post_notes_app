<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_tag', function (Blueprint $table) {
            $table->increments('tag_id');
            $table->string('tag_name', 100)->unique();
            $table->integer('tag_status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_tag');
    }
};