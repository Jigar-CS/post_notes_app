<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_user', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('username', 100);
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            
            // Dropdown Foreign References
            $table->integer('country_id')->unsigned();
            $table->integer('role_id')->unsigned();
            
            $table->integer('user_status')->default(1); // 1 = Active, 0 = Inactive
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_user');
    }
};