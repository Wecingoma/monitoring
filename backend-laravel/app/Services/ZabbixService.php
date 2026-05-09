<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ZabbixService
{
    private string $apiUrl;
    private string $username;
    private string $password;
    private int $timeout;
    private ?string $authToken = null;

    public function __construct()
    {
        $this->apiUrl = config('monitoring.zabbix.api_url');
        $this->username = config('monitoring.zabbix.username');
        $this->password = config('monitoring.zabbix.password');
        $this->timeout = config('monitoring.zabbix.timeout', 30);
    }

    public function authenticate(): ?string
    {
        if ($this->authToken && Cache::has('zabbix_auth_token')) {
            return $this->authToken;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json-rpc'])
                ->post($this->apiUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'user.login',
                    'params' => [
                        'username' => $this->username,
                        'password' => $this->password,
                    ],
                    'id' => 1,
                ]);

            $data = $response->json();

            if (isset($data['result'])) {
                $this->authToken = $data['result'];
                Cache::put('zabbix_auth_token', $this->authToken, now()->addMinutes(30));
                return $this->authToken;
            }

            Log::error('Zabbix authentication failed', ['error' => $data['error'] ?? 'Unknown error']);
            return null;
        } catch (\Exception $e) {
            Log::error('Zabbix connection error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function getHosts(array $params = []): array
    {
        $token = $this->authenticate();
        if (!$token) {
            return [];
        }

        try {
            $defaultParams = [
                'output' => ['extend'],
                'selectInterfaces' => ['extend'],
                'selectGroups' => ['extend'],
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json-rpc'])
                ->post($this->apiUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'host.get',
                    'params' => array_merge($defaultParams, $params),
                    'auth' => $token,
                    'id' => 1,
                ]);

            $data = $response->json();
            return $data['result'] ?? [];
        } catch (\Exception $e) {
            Log::error('Zabbix getHosts error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getTriggers(array $params = []): array
    {
        $token = $this->authenticate();
        if (!$token) {
            return [];
        }

        try {
            $defaultParams = [
                'output' => 'extend',
                'selectHosts' => ['hostid', 'name', 'status'],
                'selectItems' => ['itemid', 'name', 'key_'],
                'sortfield' => 'priority',
                'sortorder' => 'DESC',
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json-rpc'])
                ->post($this->apiUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'trigger.get',
                    'params' => array_merge($defaultParams, $params),
                    'auth' => $token,
                    'id' => 1,
                ]);

            $data = $response->json();
            return $data['result'] ?? [];
        } catch (\Exception $e) {
            Log::error('Zabbix getTriggers error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getProblems(array $params = []): array
    {
        $token = $this->authenticate();
        if (!$token) {
            return [];
        }

        try {
            $defaultParams = [
                'output' => 'extend',
                'selectHosts' => ['hostid', 'name'],
                'selectRelatedObject' => 'extend',
                'recent' => true,
                'sortfield' => ['eventid'],
                'sortorder' => 'DESC',
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json-rpc'])
                ->post($this->apiUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'problem.get',
                    'params' => array_merge($defaultParams, $params),
                    'auth' => $token,
                    'id' => 1,
                ]);

            $data = $response->json();
            return $data['result'] ?? [];
        } catch (\Exception $e) {
            Log::error('Zabbix getProblems error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getMetrics(string $hostId, array $itemKeys = []): array
    {
        $token = $this->authenticate();
        if (!$token) {
            return [];
        }

        try {
            $params = [
                'output' => ['extend'],
                'hostids' => $hostId,
                'history' => 0,
                'sortfield' => 'clock',
                'sortorder' => 'DESC',
                'limit' => 100,
            ];

            if (!empty($itemKeys)) {
                $params['search'] = ['key_' => implode(',', $itemKeys)];
                $params['searchWildcardsEnabled'] = true;
            }

            $itemsResponse = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json-rpc'])
                ->post($this->apiUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'item.get',
                    'params' => $params,
                    'auth' => $token,
                    'id' => 1,
                ]);

            $items = $itemsResponse->json()['result'] ?? [];

            $metrics = [];
            foreach ($items as $item) {
$historyResponse = Http::timeout($this->timeout)
                        ->withHeaders(['Content-Type' => 'application/json-rpc'])
                        ->post($this->apiUrl, [
                            'jsonrpc' => '2.0',
                            'method' => 'history.get',
                            'params' => [
                                'output' => 'extend',
                                'itemids' => [$item['itemid']],
                                'sortfield' => 'clock',
                                'sortorder' => 'DESC',
                                'limit' => 60,
                            ],
                            'auth' => $token,
                            'id' => 1,
                        ]);

                $history = $historyResponse->json()['result'] ?? [];
                $metrics[$item['key_']] = [
                    'name' => $item['name'],
                    'key' => $item['key_'],
                    'lastvalue' => $item['lastvalue'] ?? null,
                    'units' => $item['units'] ?? '',
                    'history' => array_map(function ($h) {
                        return [
                            'value' => $h['value'],
                            'timestamp' => $h['clock'],
                        ];
                    }, $history),
                ];
            }

            return $metrics;
        } catch (\Exception $e) {
            Log::error('Zabbix getMetrics error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getHostAvailability(): array
    {
        $token = $this->authenticate();
        if (!$token) {
            return [];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json-rpc'])
                ->post($this->apiUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'host.get',
                    'params' => [
                        'output' => ['hostid', 'name', 'status'],
                        'selectInterfaces' => ['interfaceid', 'available', 'ip', 'type'],
                        'filter' => [],
                    ],
                    'auth' => $token,
                    'id' => 1,
                ]);

            $data = $response->json();
            $hosts = $data['result'] ?? [];

            $availability = [
                'total' => count($hosts),
                'online' => 0,
                'offline' => 0,
                'unknown' => 0,
            ];

            foreach ($hosts as $host) {
                $mainInterface = $host['interfaces'][0] ?? null;
                if ($mainInterface) {
                    switch ($mainInterface['available']) {
                        case '1': $availability['online']++; break;
                        case '2': $availability['offline']++; break;
                        default: $availability['unknown']++; break;
                    }
                } else {
                    $availability['unknown']++;
                }
            }

            return $availability;
        } catch (\Exception $e) {
            Log::error('Zabbix getHostAvailability error', ['message' => $e->getMessage()]);
            return ['total' => 0, 'online' => 0, 'offline' => 0, 'unknown' => 0];
        }
    }
}