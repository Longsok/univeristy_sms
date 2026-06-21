<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'soklong@rupp.edu.kh'],
            [
                'name'      => 'Admin long',
                'password'  => Hash::make('long12345'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );
        
    }
}