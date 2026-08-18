<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SlowQueryLog extends Model
{
    protected $table = 'slow_query_logs';
    public $timestamps = false;
    protected $fillable = [
        'query_text', 'query_type', 'duration_ms', 'threshold_ms',
        'endpoint', 'http_method', 'source_file', 'database_name',
        'user_id', 'client_id', 'executed_at'
    ];
    protected $casts = ['executed_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function client() { return $this->belongsTo(Client::class, 'client_id'); }
}
