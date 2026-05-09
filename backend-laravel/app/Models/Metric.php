<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metric extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'type',
        'value',
        'unit',
        'metadata',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'metadata' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function scopeCpu($query)
    {
        return $query->where('type', 'cpu');
    }

    public function scopeRam($query)
    {
        return $query->where('type', 'ram');
    }

    public function scopeDisk($query)
    {
        return $query->where('type', 'disk');
    }

    public function scopeNetwork($query)
    {
        return $query->where('type', 'network');
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('recorded_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('recorded_at', '>=', now()->subHours($hours));
    }
}