<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::insert([
            'name' => 'Metembang Bali',
            'email' => 'metembang-bali@gmail.com',
            'password' => Hash::make('metembang2022')
        ]);
    }
}
