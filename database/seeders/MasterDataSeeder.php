<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Insert Countries
        DB::table('tbl_master_country')->insert([
            ['country_name' => 'India', 'country_status' => 1],
            ['country_name' => 'NRI', 'country_status' => 1],
        ]);

        // 2. (age master removed) - previously inserted age brackets here

        DB::table('tbl_post')->insert([
    [
        'user_id' => 1,
        'created_by' => 1,
        'category_id' => 1,
        'title' => 'My First Blog Post',
        'content' => 'This is the content body of the first seeded post.',
        'is_public' => 1,
        'post_status' => 1
    ]
    ]);
    }
}