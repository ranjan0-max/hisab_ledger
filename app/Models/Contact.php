<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Contact extends Model
{
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
}
