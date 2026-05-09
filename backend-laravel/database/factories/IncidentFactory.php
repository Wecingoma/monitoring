<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'Panne serveur production',
                'Indisponibilité service API',
                'Base de données injoignable',
                'Latence excessive réseau',
                'Défaillance disque RAID',
            ]),
            'description' => fake()->paragraph(),
            'severity' => fake()->randomElement(['critical', 'major', 'minor', 'low']),
            'status' => fake()->randomElement(['open', 'investigating', 'identified', 'monitoring', 'resolved']),
            'assigned_to' => null,
            'created_by' => null,
            'started_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'resolved_at' => fn(array $attr) => $attr['status'] === 'resolved' ? fake()->dateTimeBetween($attr['started_at'], 'now') : null,
            'affected_servers' => null,
            'timeline' => null,
            'root_cause' => fn(array $attr) => $attr['status'] === 'resolved' ? fake()->sentence() : null,
            'resolution' => fn(array $attr) => $attr['status'] === 'resolved' ? fake()->sentence() : null,
        ];
    }
}