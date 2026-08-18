<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DatabaseBackupLog extends Model
{
    protected $table = 'database_backup_logs';
    protected $fillable = [
        'status', 'file_name', 'file_size', 'drive_file_id', 'drive_web_view_link',
        'local_file_path', 'error_message', 'started_at', 'completed_at',
        'triggered_by', 'trigger_type', 'scheduled_for'
    ];
    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'scheduled_for'=> 'datetime',
    ];
    public function triggeredBy() { return $this->belongsTo(User::class, 'triggered_by'); }
}
