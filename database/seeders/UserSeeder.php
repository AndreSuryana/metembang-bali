<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create dummy user account
        User::create([
            'name' => 'Test User',
            'email' => 'testuser1@gmail.com',
            'password' => 'password123'
        ]);
    }
}
