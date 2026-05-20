<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_post', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_post', 'created_by')) {
                $table->integer('created_by')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('tbl_post', 'updated_by')) {
                $table->integer('updated_by')->nullable()->after('created_at');
            }
        });

        Schema::table('tbl_note', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_note', 'created_by')) {
                $table->integer('created_by')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('tbl_note', 'updated_by')) {
                $table->integer('updated_by')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tbl_post', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_post', 'updated_by')) { $table->dropColumn('updated_by'); }
            if (Schema::hasColumn('tbl_post', 'created_by')) { $table->dropColumn('created_by'); }
        });

        Schema::table('tbl_note', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_note', 'updated_by')) { $table->dropColumn('updated_by'); }
            if (Schema::hasColumn('tbl_note', 'created_by')) { $table->dropColumn('created_by'); }
        });
    }
};
