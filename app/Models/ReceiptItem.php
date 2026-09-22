<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptItem extends Model
{
    protected $fillable = ['receipt_id', 'warehouse_item_id', 'quantity'];

    protected $casts = [
        'receipt_id' => 'integer',
        'warehouse_item_id' => 'integer',
        'quantity' => 'decimal:3',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }

    public function warehouseItem(): BelongsTo
    {
        return $this->belongsTo(WarehouseItem::class);
    }
}
