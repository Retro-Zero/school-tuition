<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Only reference data. Staff accounts are created with `php artisan admin:create`
 * so re-seeding can never reset someone's password.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            TaxonomySeeder::class,
        ]);
    }
}
