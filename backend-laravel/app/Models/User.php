<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'email' => $this->email,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'administrateur';
    }

    public function isAnalyste(): bool
    {
        return $this->role === 'analyste';
    }

    public function isUtilisateur(): bool
    {
        return $this->role === 'utilisateur';
    }

    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function assignedIncidents()
    {
        return $this->hasMany(Incident::class, 'assigned_to');
    }

    public function createdIncidents()
    {
        return $this->hasMany(Incident::class, 'created_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function scopeAdministrateurs($query)
    {
        return $query->where('role', 'administrateur');
    }

    public function scopeAnalystes($query)
    {
        return $query->where('role', 'analyste');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}