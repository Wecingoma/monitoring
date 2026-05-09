<?php

namespace Database\Factories;

use App\Models\Anomaly;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnomalyFactory extends Factory
{
    protected $model = Anomaly::class;

    public function definition(): array
    {
        $severity = fake()->randomElement(['critical', 'warning', 'low']);

        return [
            'server_id' => null,
            'type' => fake()->randomElement(['cpu', 'ram', 'network', 'disk', 'behavior', 'security']),
            'severity' => $severity,
            'score' => match ($severity) {
                'critical' => fake()->randomFloat(2, 0.7, 0.99),
                'warning' => fake()->randomFloat(2, 0.4, 0.7),
                default => fake()->randomFloat(2, 0.1, 0.4),
            },
            'description' => fake()->randomElement([
                'Pic d\'utilisation CPU détecté',
                'Consommation mémoire anormale',
                'Trafic réseau inhabituel',
                'Comportement suspect détecté',
                'Tentative d\'accès non autorisée',
                'Saturation disque imminente',
            ]),
            'recommendation' => fake()->randomElement([
                'Vérifier les processus actifs',
                'Augmenter la capacité mémoire',
                'Analyser le trafic réseau',
                'Bloquer les IPs suspectes',
                'Libérer de l\'espace disque',
                'Investiguer les processus consommateurs',
            ]),
            'data_points' => null,
            'model_info' => ['model' => fake()->randomElement(['IsolationForest', 'RandomForest']), 'version' => '1.0'],
            'is_false_positive' => fake()->boolean(10),
            'detected_at' => fake()->dateTimeBetween('-48 hours', 'now'),
        ];
    }
}