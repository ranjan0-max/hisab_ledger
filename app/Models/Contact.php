<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToClient;

class Contact extends Model
{
    use BelongsToClient;
    protected $table = 'contacts';
    protected $fillable = [
        'client_id', 'type', 'name', 'khata_number', 'address',
        'gst_number', 'notes', 'opening_balance', 'opening_balance_type', 'is_active'
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
    ];
    public function client() { return $this->belongsTo(Client::class, 'client_id'); }
    public function phoneNumbers() { return $this->hasMany(ContactPhoneNumber::class, 'contact_id'); }
    public function transactions() { return $this->hasMany(LedgerTransaction::class, 'contact_id'); }

    public function scopeWithCurrentBalance($query)
    {
        return $query->leftJoin('ledger_transactions', function($join) {
            $join->on('contacts.id', '=', 'ledger_transactions.contact_id')
                 ->where('ledger_transactions.status', '=', 'POSTED');
        })
        ->select('contacts.*')
        ->selectRaw("
            CASE WHEN contacts.opening_balance_type = 'ADVANCE' THEN -contacts.opening_balance ELSE contacts.opening_balance END
            + COALESCE(SUM(
                CASE 
                    WHEN contacts.type = 'REGULAR_CUSTOMER' THEN
                        CASE 
                            WHEN ledger_transactions.transaction_type IN ('SALE', 'CASH_GIVEN', 'ADJUSTMENT') THEN ledger_transactions.amount
                            WHEN ledger_transactions.transaction_type = 'CUSTOMER_PAYMENT' THEN -ledger_transactions.amount
                            ELSE 0
                        END
                    ELSE
                        CASE 
                            WHEN ledger_transactions.transaction_type IN ('PURCHASE', 'ADJUSTMENT') THEN ledger_transactions.amount
                            WHEN ledger_transactions.transaction_type = 'SUPPLIER_PAYMENT' THEN -ledger_transactions.amount
                            ELSE 0
                        END
                END
            ), 0) as current_balance
        ")
        ->groupBy([
            'contacts.id',
            'contacts.client_id',
            'contacts.type',
            'contacts.name',
            'contacts.khata_number',
            'contacts.address',
            'contacts.gst_number',
            'contacts.notes',
            'contacts.opening_balance',
            'contacts.opening_balance_type',
            'contacts.is_active',
            'contacts.created_at',
            'contacts.updated_at',
        ]);
    }

}
