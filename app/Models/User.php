<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    protected $table = 'users';
    protected $authPasswordName = 'password_hash';

    protected $fillable = [
        'username', 'address', 'password_hash',
        'role_id', 'client_id', 'is_active', 'last_login_at',
    ];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'is_active'     => 'boolean',
        'last_login_at' => 'datetime',
        'client_id'     => 'integer',
        'role_id'       => 'integer',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function isSuperAdmin(): bool
    {
        return is_null($this->client_id);
    }

    public function hasPermission(string $permissionKey): bool
    {
        if ($this->isSuperAdmin()) return true;
        return $this->role->permissions->contains('key', $permissionKey);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
