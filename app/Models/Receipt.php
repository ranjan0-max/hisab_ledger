<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receipt extends Model
{
    protected $fillable = ['client_id', 'customer_name', 'created_by'];

    protected $casts = [
        'client_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceiptItem::class);
    }

    public function getNumberAttribute(): string
    {
        return 'RCT-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
