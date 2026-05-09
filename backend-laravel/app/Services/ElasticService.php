<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ElasticService
{
    private string $baseUrl;
    private string $indexPattern;
    private int $timeout;
    private bool $available = false;
    private ?string $username = null;
    private ?string $password = null;
    private bool $sslVerify = false;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('monitoring.elasticsearch.hosts', 'https://192.168.126.131:9200'), '/');
        $this->indexPattern = config('monitoring.elasticsearch.index', 'logs-*');
        $this->timeout = config('monitoring.elasticsearch.timeout', 30);
        $this->username = config('monitoring.elasticsearch.username');
        $this->password = config('monitoring.elasticsearch.password');
        $this->sslVerify = (bool) config('monitoring.elasticsearch.ssl_verify', false);

        $this->available = $this->ping();
    }

    private function getGuzzle(): \GuzzleHttp\Client
    {
        $config = [
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'verify' => $this->sslVerify,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        if ($this->username && $this->password) {
            $config['auth'] = [$this->username, $this->password];
        }

        return new \GuzzleHttp\Client($config);
    }

    private function ping(): bool
    {
        try {
            $client = $this->getGuzzle();
            $response = $client->get('/');
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::warning('Elasticsearch unavailable', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function getClusterInfo(): array
    {
        if (!$this->available) {
            return ['available' => false];
        }

        try {
            $response = $this->getGuzzle()->get('/');
            $info = json_decode($response->getBody(), true);
            return [
                'available' => true,
                'cluster_name' => $info['cluster_name'] ?? '',
                'version' => $info['version']['number'] ?? '',
                'cluster_uuid' => $info['cluster_uuid'] ?? '',
            ];
        } catch (\Exception $e) {
            Log::error('Elasticsearch info error', ['message' => $e->getMessage()]);
            return ['available' => false];
        }
    }

    public function searchLogs(array $params = []): array
    {
        if (!$this->available) {
            return ['total' => 0, 'from' => 0, 'size' => 0, 'logs' => []];
        }

        try {
            $query = $params['query'] ?? '*';
            $from = $params['from'] ?? 0;
            $size = $params['size'] ?? 50;
            $filters = $params['filters'] ?? [];
            $sort = $params['sort'] ?? ['@timestamp' => ['order' => 'desc']];

            $must = [];
            if ($query !== '*') {
                $must[] = ['query_string' => ['query' => $query]];
            }

            foreach ($filters as $field => $value) {
                if (is_array($value)) {
                    $must[] = ['terms' => [$field => $value]];
                } else {
                    $must[] = ['term' => [$field => $value]];
                }
            }

            $body = [
                'query' => [
                    'bool' => [
                        'must' => $must ?: [['match_all' => (object)[]]],
                    ],
                ],
                'sort' => $sort,
                'from' => $from,
                'size' => $size,
            ];

            $response = $this->getGuzzle()->post("/{$this->indexPattern}/_search", ['json' => $body]);
            $data = json_decode($response->getBody(), true);
            $hits = $data['hits']['hits'] ?? [];
            $total = $data['hits']['total']['value'] ?? 0;

            return [
                'total' => $total,
                'from' => $from,
                'size' => $size,
                'logs' => array_map(function ($hit) {
                    return array_merge(
                        ['_id' => $hit['_id'], '_index' => $hit['_index']],
                        $hit['_source']
                    );
                }, $hits),
            ];
        } catch (\Exception $e) {
            Log::error('Elasticsearch searchLogs error', ['message' => $e->getMessage()]);
            return ['total' => 0, 'from' => 0, 'size' => 0, 'logs' => []];
        }
    }

    public function getCriticalLogs(int $hours = 24, int $limit = 100): array
    {
        if (!$this->available) {
            return [];
        }

        try {
            $body = [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['range' => ['@timestamp' => ['gte' => "now-{$hours}h"]]],
                            ['terms' => ['log.level' => ['ERROR', 'FATAL', 'CRITICAL', 'emergency', 'alert', 'critical', 'error']]],
                        ],
                    ],
                ],
                'sort' => ['@timestamp' => ['order' => 'desc']],
                'size' => $limit,
            ];

            $response = $this->getGuzzle()->post("/{$this->indexPattern}/_search", ['json' => $body]);
            $data = json_decode($response->getBody(), true);

            return array_map(function ($hit) {
                return array_merge(
                    ['_id' => $hit['_id'], '_index' => $hit['_index']],
                    $hit['_source']
                );
            }, $data['hits']['hits'] ?? []);
        } catch (\Exception $e) {
            Log::error('Elasticsearch getCriticalLogs error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getRecentEvents(int $hours = 1, int $limit = 50): array
    {
        if (!$this->available) {
            return [];
        }

        try {
            $body = [
                'query' => [
                    'range' => ['@timestamp' => ['gte' => "now-{$hours}h"]],
                ],
                'sort' => ['@timestamp' => ['order' => 'desc']],
                'size' => $limit,
            ];

            $response = $this->getGuzzle()->post("/{$this->indexPattern}/_search", ['json' => $body]);
            $data = json_decode($response->getBody(), true);

            return array_map(function ($hit) {
                return array_merge(['_id' => $hit['_id']], $hit['_source']);
            }, $data['hits']['hits'] ?? []);
        } catch (\Exception $e) {
            Log::error('Elasticsearch getRecentEvents error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getLogStats(int $hours = 24): array
    {
        if (!$this->available) {
            return ['by_level' => [], 'by_source' => [], 'over_time' => []];
        }

        try {
            $body = [
                'query' => [
                    'range' => ['@timestamp' => ['gte' => "now-{$hours}h"]],
                ],
                'size' => 0,
                'aggs' => [
                    'by_level' => ['terms' => ['field' => 'log.level.keyword', 'size' => 10]],
                    'by_source' => ['terms' => ['field' => 'log.logger.keyword', 'size' => 10]],
                    'logs_over_time' => ['date_histogram' => ['field' => '@timestamp', 'fixed_interval' => '1h']],
                ],
            ];

            $response = $this->getGuzzle()->post("/{$this->indexPattern}/_search", ['json' => $body]);
            $data = json_decode($response->getBody(), true);
            $aggs = $data['aggregations'] ?? [];

            return [
                'by_level' => array_map(fn($b) => ['level' => $b['key'], 'count' => $b['doc_count']], $aggs['by_level']['buckets'] ?? []),
                'by_source' => array_map(fn($b) => ['source' => $b['key'], 'count' => $b['doc_count']], $aggs['by_source']['buckets'] ?? []),
                'over_time' => array_map(fn($b) => ['timestamp' => $b['key_as_string'], 'count' => $b['doc_count']], $aggs['logs_over_time']['buckets'] ?? []),
            ];
        } catch (\Exception $e) {
            Log::error('Elasticsearch getLogStats error', ['message' => $e->getMessage()]);
            return ['by_level' => [], 'by_source' => [], 'over_time' => []];
        }
    }

    public function ingestLog(array $logData): bool
    {
        if (!$this->available) {
            return false;
        }

        try {
            $body = array_merge($logData, ['@timestamp' => date('c')]);
            $this->getGuzzle()->post("/logs-" . date('Y.m.dd') . '/_doc', ['json' => $body]);
            return true;
        } catch (\Exception $e) {
            Log::error('Elasticsearch ingestLog error', ['message' => $e->getMessage()]);
            return false;
        }
    }
}