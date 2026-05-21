<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tbl_user') && !Schema::hasColumn('tbl_user', 'api_token')) {
            Schema::table('tbl_user', function (Blueprint $table) {
                $table->string('api_token', 120)->nullable()->after('user_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tbl_user') && Schema::hasColumn('tbl_user', 'api_token')) {
            Schema::table('tbl_user', function (Blueprint $table) {
                $table->dropColumn('api_token');
            });
        }
    }
};
