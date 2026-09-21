<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = ['name', 'description', 'is_system_role', 'is_active', 'permission_keys'];
    protected $casts = ['is_system_role' => 'boolean', 'is_active' => 'boolean', 'permission_keys' => 'array'];
    public function permissions() { return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id'); }
    public function users() { return $this->hasMany(User::class, 'role_id'); }
}
