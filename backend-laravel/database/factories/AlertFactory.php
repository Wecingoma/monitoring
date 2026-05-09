<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'CPU critique sur le serveur',
                'Espace disque faible',
                'Service HTTP indisponible',
                'Charge mémoire élevée',
                'Latence réseau anormale',
                'Processus zombie détecté',
                'Connexion base de données perdue',
                'Certificat SSL expirant bientôt',
            ]),
            'description' => fake()->paragraph(),
            'severity' => fake()->randomElement(['critical', 'warning', 'info']),
            'status' => fake()->randomElement(['active', 'active', 'acknowledged', 'resolved']),
            'source' => fake()->randomElement(['zabbix', 'elastic', 'ia', 'manual']),
            'server_id' => null,
            'user_id' => User::factory(),
            'metadata' => null,
            'resolved_at' => fn(array $attr) => $attr['status'] === 'resolved' ? fake()->dateTime() : null,
        ];
    }
}