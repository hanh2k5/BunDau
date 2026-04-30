<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign keys to allow truncation if needed, 
        // though migrate:fresh already clears tables.
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        User::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 1. Admin account
        User::create([
            'name'     => 'Quý Quản Lý',
            'email'    => 'quy@gmail.com',
            'password' => Hash::make('1973'),
            'role'     => 'admin',
        ]);

        // 2. Staff accounts
        User::create([
            'name'     => 'Hạnh Quản Lý',
            'email'    => 'hanh@gmail.com',
            'password' => Hash::make('2005'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Hảo Quản Lý',
            'email'    => 'hao@gmail.com',
            'password' => Hash::make('2002'),
            'role'     => 'admin',
        ]);
    }
}
