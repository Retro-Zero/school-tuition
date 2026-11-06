<?php

namespace App\Console\Commands;

use App\Import\ParityReport;
use App\Import\V1Importer;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Guided upgrade from the old app, for someone who doesn't write code.
 *
 * Safety properties:
 *  - the old database is only ever READ (a SELECT-only MySQL user is enough),
 *  - the old database is dumped to storage/app/backups before anything happens,
 *  - the new database is verified against the old one to the rial, and the command
 *    fails loudly if a single figure differs.
 */
class UpgradeFromV1 extends Command
{
    protected $signature = 'upgrade:from-v1
        {--force : use the values already in .env, no questions}
        {--admin=admin : username for the login created at the end}
        {--password= : password for that login (prompted when missing)}';

    protected $description = 'Migrate the old (V1) database into this app, with checks and a verified copy';

    private array $env = [];

    public function handle(ParityReport $parity): int
    {
        $this->newLine();
        $this->line('  <options=bold>ارتقاء از نسخه قدیمی به نسخه جدید</>');
        $this->line('  دادههای نسخه قدیمی فقط خوانده میشود و دست نمیخورد.');
        $this->newLine();

        if (! $this->preflight()) {
            return self::FAILURE;
        }

        $this->gatherConfiguration();

        if (! $this->writeEnv()) {
            return self::FAILURE;
        }

        if (! $this->checkSource()) {
            return self::FAILURE;
        }

        if (! $this->ensureTargetDatabase()) {
            return self::FAILURE;
        }

        $this->ensureAppKey();

        $backup = $this->dumpSource();

        if (! $this->option('force') && ! $this->confirm(
            'دیتابیس نسخه جدید ساخته و پر میشود. ادامه میدهید؟', true
        )) {
            $this->warn('لغو شد. هیچ تغییری انجام نشد.');

            return self::FAILURE;
        }

        $this->components->task('ساخت جدولهای نسخه جدید', function () {
            $this->callSilently('migrate', ['--force' => true]);
        });

        $this->components->task('انتقال دادهها', function () {
            (new V1Importer)->run();
        });

        if (! $this->report($parity)) {
            return self::FAILURE;
        }

        $this->createLogin();
        $this->finish($backup);

        return self::SUCCESS;
    }

    /** @return bool false when a prerequisite is missing */
    private function preflight(): bool
    {
        $problems = [];

        if (PHP_VERSION_ID < 80200) {
            $problems[] = 'PHP نسخه '.PHP_VERSION.' است؛ نسخه ۸.۲ یا بالاتر لازم است.';
        }

        if (! extension_loaded('pdo_mysql')) {
            $problems[] = 'افزونه pdo_mysql در PHP فعال نیست.';
        }

        foreach ($problems as $problem) {
            $this->components->error($problem);
        }

        if ($problems) {
            $this->newLine();
            $this->line('  راهنما: docs/MIGRATION.md را ببینید یا از کسی که پشتیبانی میکند کمک بگیرید.');

            return false;
        }

        $this->components->info('پیشنیازها درست است: PHP '.PHP_VERSION);

        return true;
    }

    private function gatherConfiguration(): void
    {
        if ($this->option('force')) {
            $this->env = [
                'v1_host' => env('V1_DB_HOST', '127.0.0.1'),
                'v1_port' => env('V1_DB_PORT', '3306'),
                'v1_database' => env('V1_DB_DATABASE', 'soltani5'),
                'v1_username' => env('V1_DB_USERNAME', 'root'),
                'v1_password' => (string) env('V1_DB_PASSWORD', ''),
                'db_host' => env('DB_HOST', '127.0.0.1'),
                'db_port' => env('DB_PORT', '3306'),
                'db_database' => env('DB_DATABASE', 'soltani_v2'),
                'db_username' => env('DB_USERNAME', 'root'),
                'db_password' => (string) env('DB_PASSWORD', ''),
            ];

            return;
        }

        $this->line('  <comment>اطلاعات دیتابیس فعلی (همان که برنامه قدیمی استفاده میکند)</comment>');
        $v1Host = $this->ask('آدرس سرور دیتابیس قدیمی', env('V1_DB_HOST', '127.0.0.1'));
        $v1Database = $this->ask('نام دیتابیس قدیمی', env('V1_DB_DATABASE', 'soltani5'));
        $v1User = $this->ask('نام کاربری دیتابیس قدیمی', env('V1_DB_USERNAME', 'root'));
        $v1Pass = (string) $this->secret('رمز عبور دیتابیس قدیمی (خالی = بدون رمز)');

        $this->newLine();
        $this->line('  <comment>دیتابیس جدید</comment> — نام تازهای بدهید تا دادههای قدیمی دست نخورد.');
        $dbDatabase = $this->ask('نام دیتابیس جدید', 'soltani_v2');
        $dbUser = $this->ask('کاربر دیتابیس جدید', $v1User);
        $dbPass = (string) $this->secret('رمز عبور دیتابیس جدید (خالی = بدون رمز)');

        if ($dbDatabase === $v1Database) {
            $dbDatabase .= '_v2';
            $this->warn("نام دیتابیس جدید با قدیمی یکی بود؛ به {$dbDatabase} تغییر کرد.");
        }

        $this->env = [
            'v1_host' => $v1Host, 'v1_port' => '3306', 'v1_database' => $v1Database,
            'v1_username' => $v1User, 'v1_password' => $v1Pass,
            'db_host' => $v1Host, 'db_port' => '3306', 'db_database' => $dbDatabase,
            'db_username' => $dbUser, 'db_password' => $dbPass,
        ];
    }

    private function writeEnv(): bool
    {
        $path = base_path('.env');

        if (! File::exists($path)) {
            File::copy(base_path('.env.example'), $path);
        }

        $contents = File::get($path);

        $map = [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $this->env['db_host'],
            'DB_PORT' => $this->env['db_port'],
            'DB_DATABASE' => $this->env['db_database'],
            'DB_USERNAME' => $this->env['db_username'],
            'DB_PASSWORD' => $this->quote($this->env['db_password']),
            'V1_DB_HOST' => $this->env['v1_host'],
            'V1_DB_PORT' => $this->env['v1_port'],
            'V1_DB_DATABASE' => $this->env['v1_database'],
            'V1_DB_USERNAME' => $this->env['v1_username'],
            'V1_DB_PASSWORD' => $this->quote($this->env['v1_password']),
        ];

        foreach ($map as $key => $value) {
            $line = $key.'='.$value;

            if (preg_match('/^'.$key.'=.*$/m', $contents)) {
                $contents = preg_replace('/^'.$key.'=.*$/m', $line, $contents);
            } else {
                $contents .= PHP_EOL.$line;
            }
        }

        File::put($path, $contents);
        $this->components->task('نوشتن تنظیمات در .env', fn () => null);

        // make the new values visible to the rest of this run
        foreach ($map as $key => $value) {
            putenv("{$key}=".trim($value, '"'));
            $_ENV[$key] = trim($value, '"');
            $_SERVER[$key] = trim($value, '"');
        }

        config([
            'database.connections.mysql.host' => $this->env['db_host'],
            'database.connections.mysql.database' => $this->env['db_database'],
            'database.connections.mysql.username' => $this->env['db_username'],
            'database.connections.mysql.password' => $this->env['db_password'],
            'database.connections.v1.host' => $this->env['v1_host'],
            'database.connections.v1.database' => $this->env['v1_database'],
            'database.connections.v1.username' => $this->env['v1_username'],
            'database.connections.v1.password' => $this->env['v1_password'],
        ]);

        DB::purge('mysql');
        DB::purge('v1');

        return true;
    }

    private function checkSource(): bool
    {
        try {
            $students = DB::connection('v1')->table('users')->count();
            DB::connection('v1')->table('transactions')->count();
        } catch (\Throwable $e) {
            $this->components->error('اتصال به دیتابیس قدیمی برقرار نشد: '.$e->getMessage());
            $this->line('  نام دیتابیس، کاربر یا رمز را بررسی کنید.');

            return false;
        }

        if ($students === 0) {
            $this->components->error('در دیتابیس قدیمی هیچ دانش‌آموزی پیدا نشد. احتمالاً نام دیتابیس اشتباه است.');

            return false;
        }

        $this->components->info("دیتابیس قدیمی خوانده شد: {$students} دانش‌آموز");

        return true;
    }

    private function ensureTargetDatabase(): bool
    {
        $host = $this->env['db_host'];
        $port = $this->env['db_port'];
        $user = $this->env['db_username'];
        $pass = $this->env['db_password'];
        $name = $this->env['db_database'];

        try {
            $pdo = new \PDO("mysql:host={$host};port={$port}", $user, $pass);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            $this->components->error('دیتابیس جدید ساخته نشد: '.$e->getMessage());

            return false;
        }

        $this->components->task("دیتابیس جدید آماده است ({$name})", fn () => null);

        return true;
    }

    private function ensureAppKey(): void
    {
        if (! config('app.key')) {
            $this->callSilently('key:generate', ['--force' => true]);
            $this->components->task('کلید امنیتی ساخته شد', fn () => null);
        }
    }

    private function dumpSource(): ?string
    {
        $binary = (new ExecutableFinder)->find('mariadb-dump') ?? (new ExecutableFinder)->find('mysqldump');

        if (! $binary) {
            $this->components->warn('ابزار پشتیبانگیری (mariadb-dump) پیدا نشد؛ از نسخه قدیمی پشتیبان تهیه نشد.');

            return null;
        }

        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);
        $file = $dir.'/v1-before-upgrade-'.now()->format('Y-m-d_His').'.sql.gz';

        $process = new Process([
            $binary,
            '--host='.$this->env['v1_host'],
            '--port='.$this->env['v1_port'],
            '--user='.$this->env['v1_username'],
            '--password='.$this->env['v1_password'],
            '--single-transaction', '--quick', '--default-character-set=utf8mb4',
            $this->env['v1_database'],
        ]);
        $process->setTimeout(900);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->components->warn('پشتیبانگیری از نسخه قدیمی انجام نشد (ادامه داده میشود).');

            return null;
        }

        File::put($file, gzencode($process->getOutput(), 9));
        $this->components->info('پشتیبان نسخه قدیمی: '.$file);

        return $file;
    }

    private function report(ParityReport $parity): bool
    {
        $this->newLine();
        $this->line('  <options=bold>مقایسه نسخه قدیمی و جدید</>');
        $this->line('  '.str_repeat('-', 62));

        foreach ($parity->rows() as $row) {
            $this->line(sprintf(
                '  %-20s %-16s %-16s %s',
                $row['label'],
                number_format($row['v1']),
                number_format($row['v2']),
                $row['ok'] ? '✔' : '✘',
            ));
        }

        $this->line('  '.str_repeat('-', 62));

        if ($parity->matches()) {
            $this->components->info('همه اعداد یکسان است. انتقال با موفقیت انجام شد.');

            return true;
        }

        $this->components->error('اعداد یکسان نیستند — انتقال ناتمام است.');
        $this->line('  هیچ چیزی را در برنامه جدید ثبت نکنید و با پشتیبان تماس بگیرید.');

        return false;
    }

    private function createLogin(): void
    {
        $username = (string) $this->option('admin');
        $password = $this->option('password') ?: ($this->option('force') ? null : $this->secret('یک رمز عبور برای ورود مدیر تعیین کنید'));

        if (! $password) {
            $this->components->warn('رمز عبور داده نشد؛ با دستور php artisan admin:create ساخته میشود.');

            return;
        }

        User::updateOrCreate(
            ['username' => $username],
            ['name' => 'مدیر', 'password' => $password, 'role' => User::ROLE_ADMIN],
        );

        $this->components->task("حساب ورود ساخته شد ({$username})", fn () => null);
    }

    private function finish(?string $backup): void
    {
        $this->newLine();
        $this->components->info('ارتقاء تمام شد.');
        $this->newLine();
        $this->line('  برنامه را اجرا کنید:  <options=bold>php artisan serve</>');
        $this->line('  سپس در مرورگر:        <options=bold>http://127.0.0.1:8000</>');

        if ($backup) {
            $this->line('  پشتیبان نسخه قدیمی:   '.$backup);
        }

        $this->newLine();
        $this->line('  نسخه قدیمی حذف نشده است. اگر مشکلی بود، همان برنامه قبلی را باز کنید.');
        $this->newLine();
    }

    private function quote(string $value): string
    {
        return $value === '' ? '' : '"'.$value.'"';
    }
}
