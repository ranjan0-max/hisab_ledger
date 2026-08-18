<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToClient;

class DailyEntry extends Model
{
    use BelongsToClient;
    protected $table = 'daily_entries';
    protected $fillable = [
        'client_id', 'customer_name', 'mobile_number', 'description', 'total_amount',
        'paid_amount', 'remaining_amount', 'payment_mode', 'entry_date',
        'payment_status', 'status', 'created_by', 'updated_by'
    ];
    protected $casts = [
        'total_amount'     => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'entry_date'       => 'date',
    ];
    public function client() { return $this->belongsTo(Client::class, 'client_id'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function payments() { return $this->hasMany(DailyEntryPayment::class, 'daily_entry_id')->latest('payment_date')->latest('id'); }

    // Payment status calculate karna
    public static function calcPaymentStatus(float $total, float $paid): string
    {
        if ($paid <= 0) return 'UNPAID';
        if ($paid >= $total) return $paid > $total ? 'ADVANCE' : 'PAID';
        return 'PARTIAL';
    }
}
