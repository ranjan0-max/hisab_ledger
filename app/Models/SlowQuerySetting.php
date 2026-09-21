<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SlowQuerySetting extends Model
{
    protected $table = 'slow_query_settings';
    public $timestamps = false;
    protected $fillable = ['threshold_ms', 'updated_by'];
    public function updatedBy() { return $this->belongsTo(User::class, 'updated_by'); }
}
