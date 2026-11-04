<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Download the production MySQL database from the VPS to this computer.
 *
 *   php artisan backup:pull           newest nightly backup (scp, read-only on the server)
 *   php artisan backup:pull --fresh   run mysqldump on the server now and stream it here
 *
 * Settings live in config/services.php under 'prod_backup' (PROD_BACKUP_* in .env).
 */
class PullProductionBackup extends Command
{
    protected $signature = 'backup:pull
                            {--fresh : Run mysqldump on the server now instead of downloading the newest nightly backup}';

    protected $description = 'Download the production database (nightly backup or a fresh dump) into storage/app/backups';

    /** A real dump of this app is tens of KB gzipped; anything below this is treated as broken. */
    private const MIN_BYTES = 1024;

    private const SSH_OPTIONS = ['-o', 'BatchMode=yes', '-o', 'ConnectTimeout=15'];

    public function handle(): int
    {
        if ($this->laravel->environment('production')) {
            $this->error('backup:pull is meant for your local computer and refuses to run when APP_ENV=production.');

            return self::FAILURE;
        }

        $config = $this->validatedConfig();
        if ($config === null) {
            return self::FAILURE;
        }
        [$host, $database, $remoteDir] = $config;

        $dir = storage_path('app'.DIRECTORY_SEPARATOR.'backups');
        File::ensureDirectoryExists($dir);

        $mode = $this->option('fresh') ? 'fresh' : 'nightly';
        $fileName = sprintf('%s_%s_%s.sql.gz', $database, now()->format('Y-m-d_His'), $mode);
        $finalPath = $dir.DIRECTORY_SEPARATOR.$fileName;
        $partPath = $finalPath.'.part'; // written first, renamed only when everything checks out

        $ok = false;
        try {
            $ok = $mode === 'fresh'
                ? $this->pullFresh($host, $database, $partPath)
                : $this->pullNightly($host, $database, $remoteDir, $dir, basename($partPath));

            if ($ok) {
                $ok = $this->looksValid($partPath);
            }
            if ($ok && ! rename($partPath, $finalPath)) {
                $this->error("Could not rename {$partPath} to {$finalPath}.");
                $ok = false;
            }
        } finally {
            // Never leave a half-written or broken file behind.
            if (! $ok && file_exists($partPath)) {
                @unlink($partPath);
            }
        }

        if (! $ok) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Backup saved:');
        $this->line('  '.$finalPath);
        $this->line(sprintf('  %.2f MB', filesize($finalPath) / 1024 / 1024));

        return self::SUCCESS;
    }

    /**
     * Read and check the settings. They end up inside shell commands, so only
     * a small set of safe characters is allowed.
     *
     * @return array{0:string,1:string,2:string}|null
     */
    private function validatedConfig(): ?array
    {
        $host = (string) config('services.prod_backup.ssh');
        $database = (string) config('services.prod_backup.database');
        $remoteDir = rtrim((string) config('services.prod_backup.remote_dir'), '/');

        $rules = [
            'PROD_BACKUP_SSH' => [$host, '/^([A-Za-z0-9_][A-Za-z0-9._-]*@)?[A-Za-z0-9][A-Za-z0-9.-]*$/', 'like root@139.84.228.69'],
            'PROD_BACKUP_DATABASE' => [$database, '/^[A-Za-z0-9_]+$/', 'letters, digits and _ only'],
            'PROD_BACKUP_REMOTE_DIR' => [$remoteDir, '#^/[A-Za-z0-9._/-]+$#', 'an absolute path with letters, digits, . _ - / only'],
        ];

        $valid = true;
        foreach ($rules as $name => [$value, $pattern, $hint]) {
            if ($value === '') {
                $this->error("{$name} is not set in .env.");
                $valid = false;
            } elseif (! preg_match($pattern, $value) || str_contains($value, '..')) {
                $this->error("{$name} contains characters that are not allowed ({$hint}).");
                $valid = false;
            }
        }

        return $valid ? [$host, $database, $remoteDir] : null;
    }

    /** Find the newest nightly backup of the database and copy it with scp. */
    private function pullNightly(string $host, string $database, string $remoteDir, string $localDir, string $localName): bool
    {
        // Nightly files are named db-<database>-YYYY-MM-DD.sql.gz, so sorting by name = sorting by date.
        $pattern = "{$remoteDir}/db-{$database}-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].sql.gz";
        $find = new Process(['ssh', ...self::SSH_OPTIONS, $host, "ls -1 {$pattern} 2>/dev/null | sort | tail -n 1"]);
        $find->setTimeout(60);
        $find->run();

        if (! $find->isSuccessful()) {
            $this->error('Could not connect to the server or list the backups:');
            $this->line(trim($find->getErrorOutput()));

            return false;
        }

        $remoteFile = trim($find->getOutput());
        if ($remoteFile === '') {
            $this->error("No nightly backup found matching {$pattern}");

            return false;
        }

        $this->info("Downloading newest nightly backup: {$host}:{$remoteFile}");

        // scp runs inside the backups folder with a bare file name, so a Windows
        // path like C:\... is never mistaken for a "host:path".
        $scp = new Process(['scp', ...self::SSH_OPTIONS, '-q', "{$host}:{$remoteFile}", $localName], $localDir);
        $scp->setTimeout(3600);
        $scp->run();

        if (! $scp->isSuccessful()) {
            $this->error('scp failed:');
            $this->line(trim($scp->getErrorOutput()));

            return false;
        }

        return true;
    }

    /** Run mysqldump on the server now and stream the gzipped output straight into a local file. */
    private function pullFresh(string $host, string $database, string $partPath): bool
    {
        // pipefail: if mysqldump fails, the whole pipeline fails (not just gzip's exit code).
        $remote = "bash -c 'set -o pipefail; mysqldump --single-transaction --quick --routines --triggers {$database} | gzip -c'";

        $this->info("Running mysqldump of '{$database}' on {$host} and streaming it here...");

        $out = fopen($partPath, 'wb');
        if ($out === false) {
            $this->error("Cannot write to {$partPath}");

            return false;
        }

        $process = new Process(['ssh', ...self::SSH_OPTIONS, $host, $remote]);
        $process->setTimeout(3600);
        $process->start();

        $stderr = '';
        // Iterating clears Process's internal buffer as it goes, so large dumps don't pile up in memory.
        foreach ($process as $type => $chunk) {
            if ($type === Process::OUT) {
                fwrite($out, $chunk);
            } else {
                $stderr .= $chunk;
            }
        }
        fclose($out);

        if (! $process->isSuccessful()) {
            $this->error("mysqldump over ssh failed (exit code {$process->getExitCode()}):");
            $this->line(trim($stderr));

            return false;
        }

        return true;
    }

    /** Reject empty / almost empty files and anything that isn't gzip. */
    private function looksValid(string $path): bool
    {
        clearstatcache(true, $path);
        $size = file_exists($path) ? filesize($path) : 0;

        if ($size < self::MIN_BYTES) {
            $this->error("The downloaded file is only {$size} bytes, which is too small to be a real backup.");

            return false;
        }

        $handle = fopen($path, 'rb');
        $magic = fread($handle, 2);
        fclose($handle);

        if ($magic !== "\x1f\x8b") {
            $this->error('The downloaded file is not a gzip file.');

            return false;
        }

        return true;
    }
}
