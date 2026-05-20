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

        // 3. Insert System Roles
        DB::table('tbl_master_role')->insert([
            ['role_name' => 'Administrator', 'role_status' => 1],
            ['role_name' => 'Author', 'role_status' => 1],
            ['role_name' => 'Contributor', 'role_status' => 1],
        ]);
    }
}