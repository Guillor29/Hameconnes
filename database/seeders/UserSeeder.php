<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default user with ID 1 if it doesn't exist
        if (!User::find(1)) {
            User::create([
                'id' => 1,
                'name' => 'Default User',
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
            ]);
        }
    }
}
