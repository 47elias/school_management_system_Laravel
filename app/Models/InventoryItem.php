<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Added
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Added for type-hinting
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class InventoryItem extends Model
{
    use HasFactory; // Added
    use LogsActivity;

    protected $fillable = [
        'item_name',
        'sku',
        'category',
        'quantity',
        'alert_level',
        'unit_price'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()          // Logs all attributes listed in $fillable
            ->logOnlyDirty()         // Only records a log if fields actually changed during an update
            ->dontSubmitEmptyLogs()  // Prevents saving empty log entries
            ->setDescriptionForEvent(fn(string $eventName) => "InventoryItem record has been {$eventName}");
    }

    /**
     * Get the stock movement logs for the item.
     */
    public function logs(): HasMany
    {
        // Explicitly defining the foreign key ensures no issues with Laravel's naming conventions
        return $this->hasMany(InventoryStock::class, 'inventory_item_id');
    }
}
