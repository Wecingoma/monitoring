<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Server extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'hostname',
        'ip_address',
        'os_type',
        'status',
        'user_id',
        'zabbix_host_id',
        'location',
        'description',
        'cpu_usage',
        'ram_usage',
        'disk_usage',
        'network_usage',
        'uptime_seconds',
        'last_check_at',
    ];

    protected function casts(): array
    {
        return [
            'cpu_usage' => 'decimal:2',
            'ram_usage' => 'decimal:2',
            'disk_usage' => 'decimal:2',
            'network_usage' => 'decimal:2',
            'last_check_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function metrics()
    {
        return $this->hasMany(Metric::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function anomalies()
    {
        return $this->hasMany(Anomaly::class);
    }

    public function logs()
    {
        return $this->hasMany(SystemLog::class);
    }

    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    public function scopeWarning($query)
    {
        return $query->where('status', 'warning');
    }

    public function getUptimeAttribute(): string
    {
        $seconds = $this->uptime_seconds;
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return "{$days}j {$hours}h {$minutes}m";
    }
}