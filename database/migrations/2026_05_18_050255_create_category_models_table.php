<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_category', function (Blueprint $table) {
            $table->increments('category_id');
            $table->string('category_name', 100);
            $table->string('category_slug', 100);
            $table->integer('category_status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_category');
    }
};