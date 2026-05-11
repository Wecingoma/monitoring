<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Models\Alert;
use Illuminate\Console\Command;

class CheckThresholdsCommand extends Command
{
    protected $signature = 'monitoring:check-thresholds';
    protected $description = 'Check metric thresholds and generate alerts';

    private const THRESHOLDS = [
        'cpu_usage' => [
            'critical' => 90,
            'warning' => 75,
        ],
        'ram_usage' => [
            'critical' => 90,
            'warning' => 80,
        ],
        'disk_usage' => [
            'critical' => 95,
            'warning' => 85,
        ],
    ];

    public function handle(): int
    {
        $servers = Server::all();
        $alertCount = 0;

        foreach ($servers as $server) {
            foreach (self::THRESHOLDS as $field => $thresholds) {
                $value = (float) ($server->{$field} ?? 0);
                if ($value <= 0) continue;

                $severity = null;
                if ($value >= $thresholds['critical']) {
                    $severity = 'critical';
                } elseif ($value >= $thresholds['warning']) {
                    $severity = 'warning';
                }

                if (!$severity) continue;

                $metricLabel = match ($field) {
                    'cpu_usage' => 'CPU',
                    'ram_usage' => 'RAM',
                    'disk_usage' => 'Disque',
                    default => $field,
                };

                $existing = Alert::where('server_id', $server->id)
                    ->where('source', 'ia')
                    ->where('severity', $severity)
                    ->where('title', 'like', "%{$metricLabel}%")
                    ->where('status', 'active')
                    ->exists();

                if (!$existing) {
                    Alert::create([
                        'title' => "{$metricLabel} {$server->name} à {$value}%",
                        'description' => "Le serveur {$server->name} a une utilisation {$metricLabel} de {$value}%, ce qui dépasse le seuil {$severity} de " . $thresholds[$severity] . "%",
                        'severity' => $severity,
                        'status' => 'active',
                        'source' => 'ia',
                        'server_id' => $server->id,
                        'user_id' => 1,
                    ]);
                    $alertCount++;
                }
            }
        }

        if ($alertCount > 0) {
            $this->info("Generated {$alertCount} new threshold alerts");
        }

        return self::SUCCESS;
    }
}
