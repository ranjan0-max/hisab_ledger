<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LedgerTransaction extends Model
{
    protected $table = 'ledger_transactions';
    protected $fillable = [
        'client_id', 'contact_id', 'transaction_type', 'amount',
        'transaction_date', 'description', 'payment_mode',
        'status', 'created_by', 'updated_by', 'voided_at', 'voided_by'
    ];
    protected $casts = ['amount' => 'decimal:2', 'transaction_date' => 'date', 'voided_at' => 'datetime'];
    public function contact() { return $this->belongsTo(Contact::class, 'contact_id'); }
    public function client() { return $this->belongsTo(Client::class, 'client_id'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}
