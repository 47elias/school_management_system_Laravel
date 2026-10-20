<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectAssignment extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    // Mass assignable attributes
    protected $fillable = [
        'grade',
        'teacher_id',
        'subject_id',
        'class_id',
        'academic_year',
    ];

    /** Human-readable target used by teacher pages. */
    public function getClassLabelAttribute(): string
    {
        if ($this->schoolClass) {
            return $this->schoolClass->class_name;
        }

        if ($this->grade) {
            return $this->grade . ' - All Streams';
        }

        return $this->grade ?: 'Class not assigned';
    }

    /**
     * Get the teacher (User) assigned to this subject.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the subject being assigned.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Get the class assigned to this subject.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /** Streams belonging to a grade-wide allocation. */
    public function gradeClasses()
    {
        return $this->hasManyThrough(
            SchoolClass::class,
            GradeLevel::class,
            'name',
            'grade_level_id',
            'grade',
            'id'
        );
    }

    /**
     * Relationship to Exams
     * Allows finding all exams linked to this specific assignment
     * via subject_id and class_id match.
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'subject_id', 'subject_id')
                    ->whereColumn('class_id', 'subject_assignments.class_id');
    }

    /**
     * Scope for Teacher Filtering
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Relationship to Students
     * Access all students in the class stream this allocation belongs to.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id', 'class_id');
    }

    /**
     * CONTINUOUS ASSESSMENT: All class activities logged against this teaching assignment.
     */
    public function activities()
    {
        return $this->hasMany(ClassActivity::class, 'subject_assignment_id');
    }
}
