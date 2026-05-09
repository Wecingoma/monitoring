<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anomaly extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'server_id',
        'type',
        'score',
        'severity',
        'description',
        'recommendation',
        'data_points',
        'model_info',
        'is_false_positive',
        'detected_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'data_points' => 'array',
            'model_info' => 'array',
            'is_false_positive' => 'boolean',
            'detected_at' => 'datetime',
        ];
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeWarning($query)
    {
        return $query->where('severity', 'warning');
    }

    public function scopeNotFalsePositive($query)
    {
        return $query->where('is_false_positive', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('detected_at', '>=', now()->subHours($hours));
    }

    public function markAsFalsePositive(): bool
    {
        return $this->update(['is_false_positive' => true]);
    }
}