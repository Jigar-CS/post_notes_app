<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_note', function (Blueprint $table) {
            $table->increments('note_id');
            $table->integer('user_id')->unsigned();
            $table->integer('category_id')->unsigned()->nullable();
            $table->string('title', 255);
            $table->longText('content');
            $table->timestamp('created_at')->useCurrent();
            $table->integer('note_status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_note');
    }
};