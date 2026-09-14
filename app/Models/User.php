<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = ['admin', 'admin_cabang', 'anggota', 'fasilitator', 'executive'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'no_wa',
        'verified_at',
        'pcm',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'admin_cabang']);
    }

    public function isAdminDaerah(): bool
    {
        return $this->role === 'admin';
    }

    public function isAdminCabang(): bool
    {
        return $this->role === 'admin_cabang';
    }

    public function isAnggota(): bool
    {
        return $this->role === 'anggota';
    }

    public function isFasilitator(): bool
    {
        return $this->role === 'fasilitator';
    }

    public function isExecutive(): bool
    {
        return $this->role === 'executive';
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function anggota()
    {
        return $this->hasOne(Anggota::class);
    }

    public function eventFasilitator()
    {
        return $this->belongsToMany(Event::class, 'event_fasilitator')->withTimestamps();
    }
}
