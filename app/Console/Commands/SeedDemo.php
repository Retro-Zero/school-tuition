<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

/**
 * Rebuilds the database with fictional school data — for screenshots, demos and
 * manual testing. Destructive by design; refuses to run in production.
 */
class SeedDemo extends Command
{
    protected $signature = 'demo:seed {--force : run even when the environment is production}';

    protected $description = 'Fresh database with fictional demo data (login: demo / demo)';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to wipe a production database. Re-run with --force if you are sure.');

            return self::FAILURE;
        }

        $this->call('migrate:fresh', ['--force' => true]);
        $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

        $this->newLine();
        $this->info('Demo is ready — sign in as demo / demo');

        return self::SUCCESS;
    }
}
