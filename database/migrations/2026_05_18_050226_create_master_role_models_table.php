<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_master_role', function (Blueprint $table) {
            $table->increments('role_id');
            $table->string('role_name', 50); // Will hold 'Administrator', 'Author', 'Contributor'
            $table->integer('role_status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_master_role');
    }
};