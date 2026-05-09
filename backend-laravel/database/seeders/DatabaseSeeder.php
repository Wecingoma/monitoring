<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Server;
use App\Models\Alert;
use App\Models\Metric;
use App\Models\SystemLog;
use App\Models\Anomaly;
use App\Models\Incident;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin MonitorIA',
            'email' => 'admin@monitoria.local',
            'password' => bcrypt('password123'),
            'role' => 'administrateur',
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'Analyste SOC',
            'email' => 'analyste@monitoria.local',
            'password' => bcrypt('password123'),
            'role' => 'analyste',
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'Utilisateur Standard',
            'email' => 'user@monitoria.local',
            'password' => bcrypt('password123'),
            'role' => 'utilisateur',
            'is_active' => true,
        ]);

        User::factory(5)->create(['role' => 'utilisateur']);

        Server::factory(10)->create(['user_id' => 1]);

        $serverIds = Server::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        Alert::factory(25)->create([
            'server_id' => fn() => fake()->randomElement($serverIds),
            'user_id' => fn() => fake()->randomElement($userIds),
        ]);

        Anomaly::factory(15)->create([
            'server_id' => fn() => fake()->randomElement($serverIds),
        ]);

        SystemLog::factory(50)->create([
            'server_id' => fn() => fake()->randomElement($serverIds),
        ]);

        Incident::factory(8)->create([
            'assigned_to' => fn() => fake()->randomElement($userIds),
            'created_by' => fn() => fake()->randomElement($userIds),
        ]);

        $this->seedMetrics($serverIds);
    }

    private function seedMetrics(array $serverIds): void
    {
        $types = ['cpu', 'ram', 'disk', 'network'];
        $units = ['cpu' => '%', 'ram' => '%', 'disk' => '%', 'network' => 'Mbps'];

        foreach ($serverIds as $serverId) {
            foreach ($types as $type) {
                $baseValue = match ($type) {
                    'cpu' => fake()->randomFloat(2, 30, 85),
                    'ram' => fake()->randomFloat(2, 25, 90),
                    'disk' => fake()->randomFloat(2, 20, 80),
                    'network' => fake()->randomFloat(2, 5, 95),
                };

                for ($h = 23; $h >= 0; $h--) {
                    $variation = fake()->randomFloat(2, -8, 8);
                    $value = max(1, min(99, $baseValue + $variation));

                    Metric::create([
                        'server_id' => $serverId,
                        'type' => $type,
                        'value' => $value,
                        'unit' => $units[$type],
                        'metadata' => null,
                        'recorded_at' => now()->subHours($h)->startOfHour(),
                    ]);
                }
            }
        }
    }
}