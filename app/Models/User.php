<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Staff account (V2: one shared admin login; the table already supports more).
 * Students live in `students` — V1 used the auth table for the school roster.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_ACCOUNTANT = 'accountant';

    public const ROLE_VIEWER = 'viewer';

    protected $fillable = [
        'username',
        'name',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /** Anything except a viewer may change data. */
    public function canManage(): bool
    {
        return $this->role !== self::ROLE_VIEWER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}
