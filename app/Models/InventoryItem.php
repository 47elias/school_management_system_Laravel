<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Added
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Added for type-hinting

class InventoryItem extends Model
{
    use HasFactory; // Added

    protected $fillable = [
        'item_name',
        'sku',
        'category',
        'quantity',
        'alert_level',
        'unit_price'
    ];

    /**
     * Get the stock movement logs for the item.
     */
    public function logs(): HasMany
    {
        // Explicitly defining the foreign key ensures no issues with Laravel's naming conventions
        return $this->hasMany(InventoryStock::class, 'inventory_item_id');
    }
}
