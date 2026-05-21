<?php

namespace Database\Seeders;

use App\Models\UserModel as User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a basic user in `tbl_user` for testing (no factory)
        try {
            User::create([
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'country_id' => 1,
                'role_id' => 1,
                'user_status' => 1
            ]);
        } catch (\Exception $e) {
            // ignore if already exists
        }

        // Call MasterDataSeeder to populate master data
        $this->call(MasterDataSeeder::class);
    }
}
