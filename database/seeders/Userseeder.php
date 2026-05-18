<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Don't forget to import the User model
use Illuminate\Support\Facades\Hash; // Import Hash to encrypt the password

class Userseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 public function run(): void
{
    User::create([
        'name'      => 'Admin User',
        'ec_number' => '240336',
        'email'     => 'admin@example.com', // Add this line
        'password'  => Hash::make('musam'),
    ]);
}
}
