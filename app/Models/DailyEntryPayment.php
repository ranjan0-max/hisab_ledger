<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyEntryPayment extends Model
{
    protected $table = 'daily_entry_payments';

    protected $fillable = [
        'daily_entry_id',
        'amount',
        'payment_date',
        'payment_mode',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function dailyEntry()
    {
        return $this->belongsTo(DailyEntry::class, 'daily_entry_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
