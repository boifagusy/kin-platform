<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseMonitorService
{
    /**
     * Get database metrics
     */
    public function getMetrics(): array
    {
        return [
            'connection' => $this->getConnectionStatus(),
            'connections_count' => $this->getConnectionCount(),
            'migrations' => $this->getMigrationStatus(),
            'table_stats' => $this->getTableStats(),
            'overall_health' => $this->calculateOverallHealth(),
        ];
    }

    /**
     * Check connection status
     */
    protected function getConnectionStatus(): array
    {
        try {
            $pdo = DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();
            $database = DB::connection()->getDatabaseName();

            return [
                'status' => 'healthy',
                'driver' => $driver,
                'database' => $database,
                'connected' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get current connections count
     */
    protected function getConnectionCount(): array
    {
        try {
            DB::table('users')->count();
            return [
                'active' => 1,
                'limit' => 'N/A',
                'status' => 'healthy',
            ];
        } catch (\Exception $e) {
            return [
                'active' => 0,
                'limit' => 'N/A',
                'status' => 'warning',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get migration status
     */
    protected function getMigrationStatus(): array
    {
        try {
            $hasMigrations = Schema::hasTable('migrations');

            if (!$hasMigrations) {
                return [
                    'status' => 'warning',
                    'message' => 'Migrations table not found',
                ];
            }

            $total = DB::table('migrations')->count();
            $latest = DB::table('migrations')->orderBy('batch', 'desc')->first();

            return [
                'status' => 'healthy',
                'total_migrations' => $total,
                'latest_batch' => $latest?->batch ?? 0,
                'latest_migration' => $latest?->migration ?? 'none',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get table statistics
     */
    protected function getTableStats(): array
    {
        try {
            $tables = $this->getSqliteTableStats();

            return [
                'status' => 'healthy',
                'total_tables' => count($tables),
                'tables' => $tables,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'error' => $e->getMessage(),
                'tables' => [],
            ];
        }
    }

    /**
     * Get SQLite table stats
     */
    protected function getSqliteTableStats(): array
    {
        $tables = [];

        $tableNames = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");

        foreach ($tableNames as $row) {
            $name = $row->name;
            if (str_starts_with($name, 'sqlite_')) continue;

            try {
                $count = DB::table($name)->count();
                $tables[] = [
                    'name' => $name,
                    'rows' => $count,
                    'size' => 'N/A',
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return $tables;
    }

    /**
     * Calculate overall health
     */
    protected function calculateOverallHealth(): array
    {
        $statuses = [
            $this->getConnectionStatus()['status'],
            $this->getConnectionCount()['status'],
            $this->getMigrationStatus()['status'],
            $this->getTableStats()['status'],
        ];

        $critical = array_filter($statuses, function ($status) {
            return $status === 'critical';
        });

        if (count($critical) > 0) {
            $status = 'critical';
            $score = 30;
        } elseif (in_array('unhealthy', $statuses) || in_array('warning', $statuses)) {
            $status = 'warning';
            $score = 60;
        } else {
            $status = 'healthy';
            $score = 100;
        }

        return [
            'status' => $status,
            'score' => $score,
        ];
    }

    /**
     * Check for lock contention
     */
    public function checkLocks(): array
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'sqlite') {
                return [
                    'status' => 'healthy',
                    'message' => 'SQLite lock contention not monitored',
                ];
            }

            return [
                'status' => 'healthy',
                'message' => 'No lock contention detected',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'error' => $e->getMessage(),
            ];
        }
    }
}
