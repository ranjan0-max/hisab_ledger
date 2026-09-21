<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientRenewalPayment extends Model
{
    protected $fillable = [
        'client_id',
        'renewal_due_date',
        'paid_at',
        'marked_by',
    ];

    protected $casts = [
        'renewal_due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
