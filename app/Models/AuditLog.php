<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model
{
    protected $table = 'audit_logs';
    protected $fillable = [
        'client_id', 'user_id', 'action', 'entity_type',
        'entity_id', 'old_values', 'new_values', 'ip_address'
    ];
    protected $casts = ['old_values' => 'array', 'new_values' => 'array'];
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
}
