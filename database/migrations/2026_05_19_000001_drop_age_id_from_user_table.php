<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tbl_user') && Schema::hasColumn('tbl_user', 'age_id')) {
            Schema::table('tbl_user', function (Blueprint $table) {
                $table->dropColumn('age_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tbl_user') && !Schema::hasColumn('tbl_user', 'age_id')) {
            Schema::table('tbl_user', function (Blueprint $table) {
                $table->integer('age_id')->unsigned()->nullable();
            });
        }
    }
};
