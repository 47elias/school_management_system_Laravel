<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'type',
        'quantity',
        'remarks',
        'person_involved'
    ];

    /**
     * Get the item associated with this stock movement.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
