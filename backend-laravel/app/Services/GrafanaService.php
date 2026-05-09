<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GrafanaService
{
    private string $url;
    private string $username;
    private string $password;
    private int $timeout;
    private bool $available = false;

    public function __construct()
    {
        $this->url = config('monitoring.grafana.url', 'http://192.168.126.131:3000');
        $this->username = config('monitoring.grafana.username', 'admin');
        $this->password = config('monitoring.grafana.password', 'admin');
        $this->timeout = config('monitoring.grafana.timeout', 30);

        try {
            Http::timeout($this->timeout)
                ->withBasicAuth($this->username, $this->password)
                ->get("{$this->url}/api/health");

            $this->available = true;
        } catch (\Exception $e) {
            Log::warning('Grafana unavailable', ['message' => $e->getMessage()]);
            $this->available = false;
        }
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    private function client()
    {
        return Http::timeout($this->timeout)
            ->withBasicAuth($this->username, $this->password)
            ->acceptJson();
    }

    public function getHealth(): array
    {
        if (!$this->available) {
            return ['available' => false, 'status' => 'unavailable'];
        }

        try {
            $response = $this->client()->get("{$this->url}/api/health");
            return array_merge(['available' => true], $response->json());
        } catch (\Exception $e) {
            Log::error('Grafana health check error', ['message' => $e->getMessage()]);
            return ['available' => false, 'status' => 'error'];
        }
    }

    public function getDashboards(): array
    {
        if (!$this->available) {
            return [];
        }

        try {
            $response = $this->client()->get("{$this->url}/api/search", [
                'type' => 'dash-db',
            ]);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Grafana getDashboards error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getDashboard(string $uid): array
    {
        if (!$this->available) {
            return [];
        }

        try {
            $response = $this->client()->get("{$this->url}/api/dashboards/uid/{$uid}");
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Grafana getDashboard error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getDatasources(): array
    {
        if (!$this->available) {
            return [];
        }

        try {
            $response = $this->client()->get("{$this->url}/api/datasources");
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Grafana getDatasources error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getOrganizations(): array
    {
        if (!$this->available) {
            return [];
        }

        try {
            $response = $this->client()->get("{$this->url}/api/orgs");
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Grafana getOrganizations error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getUsers(): array
    {
        if (!$this->available) {
            return [];
        }

        try {
            $response = $this->client()->get("{$this->url}/api/org/users", [
                'per_page' => 100,
            ]);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Grafana getUsers error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getAlerts(): array
    {
        if (!$this->available) {
            return [];
        }

        try {
            $response = $this->client()->get("{$this->url}/api/alerts");
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Grafana getAlerts error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function createDashboard(array $dashboard): array
    {
        if (!$this->available) {
            return ['status' => 'error', 'message' => 'Grafana unavailable'];
        }

        try {
            $response = $this->client()->post("{$this->url}/api/dashboards/db", [
                'dashboard' => $dashboard,
                'overwrite' => true,
            ]);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Grafana createDashboard error', ['message' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function createDatasource(array $datasource): array
    {
        if (!$this->available) {
            return ['status' => 'error', 'message' => 'Grafana unavailable'];
        }

        try {
            $response = $this->client()->post("{$this->url}/api/datasources", $datasource);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Grafana createDatasource error', ['message' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}