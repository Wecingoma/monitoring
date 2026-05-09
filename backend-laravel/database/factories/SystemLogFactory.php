<?php

namespace Database\Factories;

use App\Models\SystemLog;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

class SystemLogFactory extends Factory
{
    protected $model = SystemLog::class;

    public function definition(): array
    {
        return [
            'server_id' => null,
            'level' => fake()->randomElement(['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug']),
            'source' => fake()->randomElement(['nginx', 'postgresql', 'redis', 'kernel', 'sshd', 'crond', 'systemd', 'app']),
            'facility' => fake()->randomElement(['auth', 'syslog', 'daemon', 'kern', 'local0', 'local1']),
            'message' => fake()->sentence(),
            'metadata' => null,
            'log_index' => 'logs-' . fake()->date('Y.m.dd'),
            'logged_at' => fake()->dateTimeBetween('-24 hours', 'now'),
        ];
    }
}