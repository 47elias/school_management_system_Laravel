<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Exam extends Model
{
    /**
     * The attributes that are mass assignable.
     * Updated to match your SQL dump columns exactly.
     */
    protected $fillable = [
        'exam_name',
        'term_id',
        'subject_id',
        'exam_date',
        'status' // Added status as per your SQL dump
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'exam_date' => 'date',
    ];

    /**
     * Relationship: The term (Academic Session) this exam belongs to.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    /**
     * Relationship: The specific subject being examined.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Relationship: All marks recorded for this exam.
     */
    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class, 'exam_id');
    }

    /**
     * BRIDGE RELATIONSHIP
     * Since 'exams' table is missing 'class_id', we find the class
     * through the SubjectAssignment table using the subject_id.
     */
    public function schoolClass()
    {
        // This assumes a subject is assigned to a class in subject_assignments
        return $this->hasOneThrough(
            SchoolClass::class,
            SubjectAssignment::class,
            'subject_id', // Foreign key on subject_assignments...
            'id',         // Foreign key on school_classes...
            'subject_id', // Local key on exams...
            'class_id'    // Local key on subject_assignments...
        );
    }

    /**
     * Relationship: Access all students who have marks for this exam.
     */
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            Mark::class,
            'exam_id',    // Foreign key on marks table...
            'id',         // Foreign key on students table...
            'id',         // Local key on exams table...
            'student_id'  // Local key on marks table...
        );
    }

    /**
     * Accessor: Format the exam date for the UI.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->exam_date ? $this->exam_date->format('d M, Y') : 'N/A';
    }

    /**
     * Helper: Calculate the average score for this specific exam.
     */
    public function getAverageScoreAttribute(): float
    {
        return round((float) ($this->marks()->avg('score') ?? 0), 2);
    }

    public function getAssignmentIdAttribute()
    {
        $assignment = \App\Models\SubjectAssignment::where('subject_id', $this->subject_id)
            ->where('class_id', $this->schoolClass->id ?? null)
            ->first();
        return $assignment ? $assignment->id : null;
    }

    /**
     * NEW: DIRECT TEACHER AUTHORIZATION LINK
     * This helps the controller verify if the logged-in teacher owns this exam
     * via the SubjectAssignment bridge.
     */
    public function subjectAssignment()
    {
        return $this->hasOne(SubjectAssignment::class, 'subject_id', 'subject_id');
    }
}
