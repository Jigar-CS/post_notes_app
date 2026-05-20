<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_master_country', function (Blueprint $table) {
            $table->increments('country_id');
            $table->string('country_name', 50); // Will hold 'India' or 'NRI'
            $table->integer('country_status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_master_country');
    }
};