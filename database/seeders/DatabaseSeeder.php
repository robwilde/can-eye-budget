<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DefaultUserSeeder::class,
            DefaultAccountCategoriesSeeder::class,
            CategorySeeder::class,
            AccountSeeder::class,
        ]);
    }
}
