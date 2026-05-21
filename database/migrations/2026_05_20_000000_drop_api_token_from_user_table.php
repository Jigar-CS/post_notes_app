<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbl_user', function (Blueprint $table) {
            // Drop api_token column; we'll use Laravel Sanctum's personal_access_tokens table instead
            if (Schema::hasColumn('tbl_user', 'api_token')) {
                $table->dropColumn('api_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_user', function (Blueprint $table) {
            // Restore if rolled back
            $table->string('api_token')->nullable();
        });
    }
};
