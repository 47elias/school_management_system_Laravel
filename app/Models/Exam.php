<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Exam extends Model
{
    use LogsActivity;

    /**
     * Mass assignable attributes matching the database schema.
     */
    protected $fillable = [
        'exam_name',
        'term_id',
        'subject_id',
        'exam_date',
        'status',
        'subject_assignment_id',
        'class_id'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Exam record has been {$eventName}");
    }

    protected $casts = [
        'exam_date' => 'date',
    ];

    /**
     * Relationship: The academic term/session.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    /**
     * Relationship: The specific subject examined.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Relationship: Specific school class stream (if allocated to a stream).
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relationship: Link to teaching load assignment.
     */
    public function subjectAssignment(): BelongsTo
    {
        return $this->belongsTo(SubjectAssignment::class, 'subject_assignment_id');
    }

    /**
     * Relationship: All marks recorded for this exam paper.
     */
    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class, 'exam_id');
    }

    /**
     * Relationship: Access all students taking this exam through marks.
     */
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            Mark::class,
            'exam_id',
            'id',
            'id',
            'student_id'
        );
    }

    /**
     * Helper: Format exam date for UI.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->exam_date ? $this->exam_date->format('d M, Y') : 'N/A';
    }

    /**
     * Helper: Dynamic average score for this paper.
     */
    public function getAverageScoreAttribute(): float
    {
        return round((float) ($this->marks()->avg('score') ?? 0), 2);
    }

    /**
     * Helper: Format readable target scope string.
     */
    public function getTargetNameAttribute(): string
    {
        if ($this->schoolClass) {
            return $this->schoolClass->class_name;
        }
        if ($this->subjectAssignment && $this->subjectAssignment->grade) {
            return $this->subjectAssignment->grade;
        }
        return 'All Grade Streams';
    }
}