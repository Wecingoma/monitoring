<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('monitoring:sync', function () {
    $this->info('Synchronisation Zabbix + ES...');
    Artisan::call('sync:zabbix');
    Artisan::call('sync:elasticsearch');
    $this->info('Synchronisation terminée !');
})->purpose('Synchronise les données réelles (Zabbix/Elastic)');

Artisan::command('monitoring:live', function () {
    $this->info('=== MonitorIA Live Sync Started ===');
    $lastZabbix = 0;
    $lastEs = 0;
    $lastThreshold = 0;

    while (true) {
        $now = time();

        if ($now - $lastZabbix >= 60) {
            $this->info('[' . date('H:i:s') . '] Syncing Zabbix...');
            Artisan::call('sync:zabbix');
            $lastZabbix = $now;
        }

        if ($now - $lastThreshold >= 60) {
            $this->info('[' . date('H:i:s') . '] Checking thresholds...');
            Artisan::call('monitoring:check-thresholds');
            $lastThreshold = $now;
        }

        if ($now - $lastEs >= 300) {
            $this->info('[' . date('H:i:s') . '] Syncing Elasticsearch...');
            Artisan::call('sync:elasticsearch');
            $lastEs = $now;
        }

        sleep(5);
    }
})->purpose('Run live monitoring sync loop');
