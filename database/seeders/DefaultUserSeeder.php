<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create default test user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'figjam@mrwilde.com'],
            [
                'name'              => 'Bobby Wilde',
                'email'             => 'figjam@mrwilde.com',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
    }
}
