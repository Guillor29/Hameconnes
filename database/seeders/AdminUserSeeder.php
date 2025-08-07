<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user (Guillaume)
        User::updateOrCreate(
            ['email' => 'guillaume.rv29@gmail.com'],
            [
                'name' => 'Guillaume',
                'password' => Hash::make('azerty'),
                'role' => 'admin',
            ]
        );
    }
}
