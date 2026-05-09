<?php

return [
    'zabbix' => [
        'api_url' => env('ZABBIX_API_URL', 'http://192.168.126.131/zabbix/api_jsonrpc.php'),
        'username' => env('ZABBIX_USERNAME', 'Admin'),
        'password' => env('ZABBIX_PASSWORD', 'zabbix'),
        'timeout' => env('ZABBIX_TIMEOUT', 30),
    ],

    'elasticsearch' => [
        'hosts' => env('ELASTICSEARCH_HOSTS', 'https://192.168.126.131:9200'),
        'index' => env('ELASTICSEARCH_INDEX', 'logs-*'),
        'username' => env('ELASTICSEARCH_USERNAME', 'elastic'),
        'password' => env('ELASTICSEARCH_PASSWORD', ''),
        'ssl_verify' => env('ELASTICSEARCH_SSL_VERIFY', false),
        'timeout' => env('ELASTICSEARCH_TIMEOUT', 30),
    ],

    'grafana' => [
        'url' => env('GRAFANA_URL', 'http://192.168.126.131:3000'),
        'username' => env('GRAFANA_USERNAME', 'admin'),
        'password' => env('GRAFANA_PASSWORD', 'admin'),
        'timeout' => env('GRAFANA_TIMEOUT', 30),
    ],

    'ai' => [
        'service_url' => env('AI_SERVICE_URL', 'http://localhost:5000'),
        'timeout' => env('AI_SERVICE_TIMEOUT', 30),
    ],
];