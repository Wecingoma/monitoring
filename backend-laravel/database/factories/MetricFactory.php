<?php

namespace Database\Factories;

use App\Models\Metric;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

class MetricFactory extends Factory
{
    protected $model = Metric::class;

    public function definition(): array
    {
        return [
            'server_id' => Server::factory(),
            'type' => fake()->randomElement(['cpu', 'ram', 'disk', 'network', 'uptime', 'load']),
            'value' => fake()->randomFloat(2, 1, 100),
            'unit' => fake()->randomElement(['%', 'MB', 'Mbps', 's', '']),
            'metadata' => null,
            'recorded_at' => fake()->dateTimeBetween('-24 hours', 'now'),
        ];
    }
}