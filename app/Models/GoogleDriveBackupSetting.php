<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GoogleDriveBackupSetting extends Model
{
    protected $table = 'google_drive_backup_settings';
    protected $fillable = [
        'refresh_token_ciphertext', 'refresh_token_iv', 'refresh_token_auth_tag',
        'drive_email', 'folder_id', 'folder_name', 'connected_at', 'updated_by',
        'automatic_backup_enabled', 'backup_time', 'backup_timezone'
    ];
    protected $hidden = ['refresh_token_ciphertext', 'refresh_token_iv', 'refresh_token_auth_tag'];
    protected $casts = ['automatic_backup_enabled' => 'boolean', 'connected_at' => 'datetime'];
    public function updatedBy() { return $this->belongsTo(User::class, 'updated_by'); }
}
