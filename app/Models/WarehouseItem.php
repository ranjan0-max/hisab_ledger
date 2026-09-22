<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseItem extends Model
{
    protected $table = 'items_in_warehouses';

    protected $fillable = ['warehouse_id', 'item_name', 'quantity'];

    protected $casts = [
        'warehouse_id' => 'integer',
        'quantity' => 'decimal:3',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
