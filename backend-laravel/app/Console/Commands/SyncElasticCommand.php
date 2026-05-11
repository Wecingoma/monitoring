<?php

namespace App\Console\Commands;

use App\Models\SystemLog;
use App\Models\Server;
use App\Services\ElasticService;
use Illuminate\Console\Command;

class SyncElasticCommand extends Command
{
    protected $signature = 'sync:elasticsearch {--hours=24 : Hours of logs to sync}';
    protected $description = 'Synchronize logs from Elasticsearch into the local database';

    private ElasticService $elastic;

    public function __construct(ElasticService $elastic)
    {
        parent::__construct();
        $this->elastic = $elastic;
    }

    public function handle(): int
    {
        if (!$this->elastic->isAvailable()) {
            $this->error('Elasticsearch is not available.');
            return self::FAILURE;
        }

        $hours = (int) $this->option('hours');
        $this->info("Syncing logs from Elasticsearch (last {$hours} hours)...");

        $results = $this->elastic->searchLogs([
            'query' => '*',
            'size' => 500,
        ]);

        $synced = 0;
        foreach ($results['logs'] ?? [] as $log) {
            $serverName = $log['host'] ?? $log['server'] ?? $log['hostname'] ?? null;
            $serverId = null;
            if ($serverName) {
                $serverId = Server::where('hostname', $serverName)
                    ->orWhere('name', $serverName)
                    ->value('id');
            }

            $level = strtolower($log['level'] ?? $log['log']['level'] ?? 'info');
            $message = $log['message'] ?? $log['log']['message'] ?? '';
            $source = $log['source'] ?? $log['service'] ?? $log['log']['logger'] ?? 'elasticsearch';
            $timestamp = $log['@timestamp'] ?? $log['timestamp'] ?? now()->toIso8601String();

            if (empty($message)) {
                continue;
            }

            SystemLog::firstOrCreate(
                [
                    'source' => $source,
                    'message' => substr($message, 0, 65535),
                    'logged_at' => $timestamp,
                    'level' => $level,
                ],
                [
                    'server_id' => $serverId,
                    'facility' => $log['facility'] ?? 'syslog',
                    'metadata' => null,
                    'log_index' => $log['_index'] ?? 'synced',
                ]
            );
            $synced++;
        }

        $this->info("Synced {$synced} logs from Elasticsearch.");

        $stats = $this->elastic->getLogStats($hours);
        $this->info('Log stats by level:');
        foreach ($stats['by_level'] ?? [] as $bucket) {
            $this->line("  {$bucket['level']}: {$bucket['count']}");
        }

        return self::SUCCESS;
    }
}