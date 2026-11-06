<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Nightly logical backup of the school database.
 *
 * Student financial records are not reproducible: a lost database cannot be
 * rebuilt from anywhere. This keeps N gzipped dumps on disk, and the deployment
 * docs rsync that directory to a second machine.
 */
class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=14 : how many daily dumps to keep}';

    protected $description = 'Dump the database to storage/app/backups and prune old dumps';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $config = config('database.connections.mysql');
        $file = $dir.'/'.now()->format('Y-m-d_His').'-'.preg_replace('/[^a-z0-9_]/i', '', (string) $config['database']).'.sql.gz';

        $binary = (new ExecutableFinder)->find('mariadb-dump')
            ?? (new ExecutableFinder)->find('mysqldump');

        if (! $binary) {
            $this->error('Neither mariadb-dump nor mysqldump was found in PATH.');

            return self::FAILURE;
        }

        $process = new Process([
            $binary,
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--password='.$config['password'],
            '--single-transaction',
            '--quick',
            '--default-character-set=utf8mb4',
            $config['database'],
        ]);

        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Backup failed: '.trim($process->getErrorOutput()));

            return self::FAILURE;
        }

        $gz = gzencode($process->getOutput(), 9);
        File::put($file, $gz);

        $this->info(sprintf('Backup written: %s (%.1f MB)', $file, strlen($gz) / 1048576));

        $dumps = collect(File::files($dir))->sortByDesc->getMTime()->values();
        foreach ($dumps->slice((int) $this->option('keep')) as $old) {
            File::delete($old->getPathname());
            $this->line('Pruned: '.$old->getFilename());
        }

        return self::SUCCESS;
    }
}
