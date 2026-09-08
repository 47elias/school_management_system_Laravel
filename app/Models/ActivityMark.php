<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ActivityMark extends Model
{
    use LogsActivity;
    protected $fillable = [
        'class_activity_id',
        'student_id',
        'score',
        'comment',
    ];

    protected $casts = [
        'score' => 'float',
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()          // Logs all attributes listed in $fillable
            ->logOnlyDirty()         // Only records a log if fields actually changed during an update
            ->dontSubmitEmptyLogs()  // Prevents saving empty log entries
            ->setDescriptionForEvent(fn(string $eventName) => "Activity Mark record has been {$eventName}");
    }

    public function classActivity(): BelongsTo
    {
        return $this->belongsTo(ClassActivity::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getPercentAttribute(): float
    {
        $max = $this->classActivity->max_score ?? 100;
        return $max ? round(($this->score / $max) * 100, 2) : 0.0;
    }
}
