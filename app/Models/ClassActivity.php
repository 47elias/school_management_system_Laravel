<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * Represents a single in-class activity (classwork, homework, quiz,
 * participation, practical, project, etc.) that a teacher records marks
 * for. Unlike Exam, this has no fixed schedule and is expected to be
 * created and marked on the same day, potentially many times a term.
 */
class ClassActivity extends Model
{
    use LogsActivity;
    protected $fillable = [
        'subject_assignment_id',
        'term_id',
        'title',
        'type',
        'activity_date',
        'max_score',
        'weight',
        'notes',
        'created_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()          // Logs all attributes listed in $fillable
            ->logOnlyDirty()         // Only records a log if fields actually changed during an update
            ->dontSubmitEmptyLogs()  // Prevents saving empty log entries
            ->setDescriptionForEvent(fn(string $eventName) => "Class activity record has been {$eventName}");
    }
    protected $casts = [
        'activity_date' => 'date',
        'max_score'     => 'integer',
        'weight'        => 'float',
    ];

    public function subjectAssignment(): BelongsTo
    {
        return $this->belongsTo(SubjectAssignment::class, 'subject_assignment_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(ActivityMark::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Convenience passthroughs so views don't need to dig through subjectAssignment. */
    public function getSubjectAttribute()
    {
        return $this->subjectAssignment?->subject;
    }

    public function getSchoolClassAttribute()
    {
        return $this->subjectAssignment?->schoolClass;
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->activity_date ? $this->activity_date->format('d M, Y') : 'N/A';
    }

    public function getAverageScoreAttribute(): float
    {
        return round((float) ($this->marks()->avg('score') ?? 0), 2);
    }

    public function getAveragePercentAttribute(): float
    {
        if (!$this->max_score) {
            return 0.0;
        }
        return round(($this->average_score / $this->max_score) * 100, 2);
    }

    public function getRecordedCountAttribute(): int
    {
        return $this->marks()->count();
    }

    public function getTypeLabelAttribute(): string
    {
        return ucfirst($this->type);
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'classwork'     => 'bg-blue',
            'homework'      => 'bg-purple',
            'quiz'          => 'bg-orange',
            'participation' => 'bg-teal',
            'practical'     => 'bg-maroon',
            'project'       => 'bg-aqua',
            default         => 'bg-gray',
        };
    }
}
