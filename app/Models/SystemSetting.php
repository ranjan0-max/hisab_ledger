<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SystemSetting extends Model
{
    protected $table = 'system_settings';
    protected $fillable = ['client_id', 'currency', 'timezone'];
    public function client() { return $this->belongsTo(Client::class, 'client_id'); }
}
