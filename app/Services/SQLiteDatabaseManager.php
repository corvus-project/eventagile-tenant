<?php

declare(strict_types=1);

namespace App\Services;

use Stancl\Tenancy\Contracts\TenantDatabaseManager;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class SQLiteDatabaseManager implements TenantDatabaseManager
{
    const dbpath = 'tenants/';

    public function createDatabase(TenantWithDatabase $tenant): bool
    {
        try {
            $path = $this->path($tenant->database()->getName());

            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0775, true);
            }

            $created = file_put_contents($path, '') !== false;

            if ($created) {
                chmod($path, 0664);
            }

            return $created;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function deleteDatabase(TenantWithDatabase $tenant): bool
    {
        try {
            return unlink($this->path($tenant->database()->getName()));
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function databaseExists(string $name): bool
    {
        return file_exists($this->path($name));
    }

    public function makeConnectionConfig(array $baseConfig, string $databaseName): array
    {
        $baseConfig['database'] = $this->path($databaseName);

        return $baseConfig;
    }

    public function setConnection(string $connection): void
    {
        //
    }

    private function path(string $databaseName): string
    {
        return database_path(self::dbpath . $databaseName);
    }
}
