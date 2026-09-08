<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FeeStructure extends Model
{
    use HasFactory;
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     * * LOGIC PRESERVED: Includes individual billing fields.
     */
    protected $fillable = [
        'fee_name',
        'amount',
        'grade',
        'term_id',
        'student_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()          // Logs all attributes listed in $fillable
            ->logOnlyDirty()         // Only records a log if fields actually changed during an update
            ->dontSubmitEmptyLogs()  // Prevents saving empty log entries
            ->setDescriptionForEvent(fn(string $eventName) => "Fee Structure record has been {$eventName}");
    }
    /**
     * Relationship: The term this fee structure belongs to.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Relationship: The specific student this fee applies to (if any).
     * * LOGIC PRESERVED: Supports individual billing.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
