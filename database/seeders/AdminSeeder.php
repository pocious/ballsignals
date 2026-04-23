<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@ballsignals.com'],
            [
                'name'     => 'BallSignals Admin',
                'email'    => 'admin@ballsignals.com',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );
    }
}
