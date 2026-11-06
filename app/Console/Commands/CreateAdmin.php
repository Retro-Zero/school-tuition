<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Creates or resets the staff account. Kept out of the seeders on purpose:
 * `db:seed` used to rewrite the admin password every time it ran.
 */
class CreateAdmin extends Command
{
    protected $signature = 'admin:create {username} {--name=مدیر} {--role=admin}';

    protected $description = 'Create (or reset) a staff login and print its password once';

    public function handle(): int
    {
        $username = (string) $this->argument('username');
        $password = Str::password(16);

        $user = User::updateOrCreate(
            ['username' => $username],
            [
                'name' => (string) $this->option('name'),
                'password' => $password,
                'role' => (string) $this->option('role'),
            ],
        );

        $this->info("حساب {$user->username} ساخته/بازنشانی شد.");
        $this->newLine();
        $this->line("رمز عبور: {$password}");
        $this->newLine();
        $this->warn('این رمز فقط یک بار نمایش داده می‌شود. آن را در جای امن نگه دارید.');

        return self::SUCCESS;
    }
}
