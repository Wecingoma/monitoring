<?php

namespace Database\Factories;

use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServerFactory extends Factory
{
    protected $model = Server::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['WEB-PROD', 'DB-PROD', 'API-PROD', 'CACHE-PROD', 'WEB-STAGING', 'DB-STAGING', 'MONITOR', 'BACKUP', 'PROXY', 'WORKER']) . '-' . fake()->numberBetween(1, 99),
            'hostname' => fake()->unique()->domainWord() . '.' . fake()->domainName(),
            'ip_address' => fake()->ipv4(),
            'os_type' => fake()->randomElement(['Ubuntu 22.04', 'CentOS 8', 'Debian 12', 'RHEL 9', 'Windows Server 2022']),
            'status' => fake()->randomElement(['online', 'online', 'online', 'offline', 'warning']),
            'user_id' => User::factory(),
            'location' => fake()->randomElement(['Paris DC1', 'Lyon DC2', 'Marseille DC3', 'Cloud EU-West']),
            'description' => fake()->sentence(),
            'cpu_usage' => fake()->randomFloat(2, 5, 95),
            'ram_usage' => fake()->randomFloat(2, 10, 90),
            'disk_usage' => fake()->randomFloat(2, 15, 85),
            'network_usage' => fake()->randomFloat(2, 1, 70),
            'uptime_seconds' => fake()->numberBetween(3600, 2592000),
            'last_check_at' => fake()->dateTimeBetween('-1 hour', 'now'),
        ];
    }
}