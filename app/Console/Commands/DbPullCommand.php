<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class DbPullCommand extends Command
{
    protected $signature = 'db:pull
        {--dump-only : Only save the SQL dump file, don\'t import}
        {--no-drop : Don\'t drop local tables before importing}';

    protected $description = 'Pull the production database from the Kubernetes cluster';

    private const K8S_NAMESPACE = 'mariadb';

    private const K8S_POD = 'mariadb-cluster-0';

    private const PROD_DB_HOST = 'mariadb-cluster.mariadb.svc.cluster.local';

    private const PROD_DB_NAME = 'airbnb_tool';

    public function handle(): int
    {
        $dumpPath = database_path('dump.sql');

        $this->info('Fetching production database credentials...');

        $credentials = $this->getProductionCredentials();

        if (! $credentials) {
            $this->error('Could not retrieve production database credentials.');

            return self::FAILURE;
        }

        [$username, $password] = $credentials;

        $this->info('Dumping production database...');

        $dumpCommand = sprintf(
            'kubectl exec -n %s %s -- mariadb-dump -h %s -u %s -p%s --single-transaction --routines --triggers %s',
            self::K8S_NAMESPACE,
            self::K8S_POD,
            self::PROD_DB_HOST,
            escapeshellarg($username),
            escapeshellarg($password),
            self::PROD_DB_NAME,
        );

        $result = Process::timeout(300)->run($dumpCommand);

        if (! $result->successful()) {
            $this->error('Failed to dump production database:');
            $this->error($result->errorOutput());

            return self::FAILURE;
        }

        $dump = $result->output();
        $sizeMb = round(strlen($dump) / 1024 / 1024, 2);
        $this->info("Dump received: {$sizeMb} MB");

        file_put_contents($dumpPath, $dump);
        $this->info("Dump saved to: {$dumpPath}");

        if ($this->option('dump-only')) {
            return self::SUCCESS;
        }

        return $this->importLocally($dumpPath);
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function getProductionCredentials(): ?array
    {
        $result = Process::timeout(30)->run(
            'kubectl exec -n airbnb-tool deploy/airbnb-tool -- php artisan tinker --execute="echo config(\'database.connections.mariadb.username\') . \':\' . config(\'database.connections.mariadb.password\');"'
        );

        if (! $result->successful()) {
            return null;
        }

        $output = trim($result->output());
        $parts = explode(':', $output, 2);

        if (count($parts) !== 2 || empty($parts[0])) {
            return null;
        }

        return $parts;
    }

    private function importLocally(string $dumpPath): int
    {
        $connection = config('database.default');

        if (! in_array($connection, ['mysql', 'mariadb'])) {
            $this->warn("Local database is '{$connection}'. Automatic import only works with MySQL/MariaDB.");
            $this->info("You can import manually from: {$dumpPath}");

            return self::SUCCESS;
        }

        $config = config("database.connections.{$connection}");

        $this->info("Importing into local '{$connection}' database...");

        $importBinary = $this->findBinary(['mariadb', 'mysql']);

        if ($importBinary) {
            return $this->importViaCli($importBinary, $config, $dumpPath);
        }

        return $this->importViaPdo($config, $dumpPath);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function importViaCli(string $binary, array $config, string $dumpPath): int
    {
        if (! $this->option('no-drop')) {
            $this->info('Dropping and recreating local database...');

            $adminBinary = $this->findBinary(['mariadb-admin', 'mysqladmin']);

            if ($adminBinary) {
                $dropCmd = $this->buildClientCommand($adminBinary, $config, sprintf(
                    'drop --force %s 2>/dev/null; %s',
                    escapeshellarg($config['database']),
                    $this->buildClientCommand($adminBinary, $config, 'create '.escapeshellarg($config['database'])),
                ));

                Process::timeout(30)->run($dropCmd);
            }
        }

        $importCmd = sprintf(
            '%s < %s',
            $this->buildClientCommand($binary, $config, $config['database']),
            escapeshellarg($dumpPath),
        );

        $result = Process::timeout(300)->run($importCmd);

        if (! $result->successful()) {
            $this->error('Import failed:');
            $this->error($result->errorOutput());

            return self::FAILURE;
        }

        $this->info('Database imported successfully!');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function importViaPdo(array $config, string $dumpPath): int
    {
        $this->info('No CLI client found, importing via PDO...');

        $dsn = sprintf('mysql:host=%s;port=%s', $config['host'], $config['port']);

        try {
            $pdo = new \PDO($dsn, $config['username'], $config['password'] ?? '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $this->error('Could not connect to local database: '.$e->getMessage());

            return self::FAILURE;
        }

        $database = $config['database'];

        if (! $this->option('no-drop')) {
            $this->info('Dropping and recreating local database...');
            $pdo->exec("DROP DATABASE IF EXISTS `{$database}`");
            $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }

        $pdo->exec("USE `{$database}`");

        $sql = file_get_contents($dumpPath);

        try {
            $pdo->exec($sql);
        } catch (\PDOException $e) {
            $this->error('Import failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Database imported successfully!');

        return self::SUCCESS;
    }

    private function findBinary(array $names): ?string
    {
        foreach ($names as $name) {
            $result = Process::run("which {$name}");

            if ($result->successful()) {
                return trim($result->output());
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function buildClientCommand(string $binary, array $config, string $suffix): string
    {
        $parts = [$binary];

        if (! empty($config['host'])) {
            $parts[] = '-h '.escapeshellarg($config['host']);
        }

        if (! empty($config['port'])) {
            $parts[] = '-P '.escapeshellarg((string) $config['port']);
        }

        if (! empty($config['username'])) {
            $parts[] = '-u '.escapeshellarg($config['username']);
        }

        if (! empty($config['password'])) {
            $parts[] = '-p'.escapeshellarg($config['password']);
        }

        $parts[] = $suffix;

        return implode(' ', $parts);
    }
}
