<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "name" => env("USER_USERNAME", "resepin"),
            "email" => env("USER_USEREMAIL", "resepin"),
            "password" => Hash::make(env("USER_PASSWORD", "passwordsecretresepin321")),
        ]);
    }
}
