<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    protected $table = 'system_logs';

    protected $fillable = [
        'server_id',
        'level',
        'source',
        'facility',
        'message',
        'metadata',
        'log_index',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'logged_at' => 'datetime',
        ];
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function scopeCritical($query)
    {
        return $query->whereIn('level', ['emergency', 'alert', 'critical']);
    }

    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('logged_at', '>=', now()->subHours($hours));
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }
}