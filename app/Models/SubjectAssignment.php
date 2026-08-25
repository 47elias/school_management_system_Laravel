<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectAssignment extends Model
{
    use HasFactory;

    // Mass assignable attributes
    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_id',
        'academic_year'
    ];

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
        // Points to class_id as defined in your $fillable
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * NEW: Inverse relationship to Exams
     * Allows you to find all exams linked to this specific assignment
     * via the subject_id and class_id match.
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'subject_id', 'subject_id')
                    ->whereColumn('class_id', 'subject_assignments.class_id');
    }

    /**
     * NEW: Scope for Teacher Filtering
     * Makes it easy to fetch assignments for the logged-in teacher:
     * SubjectAssignment::forTeacher(auth()->id())->get();
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * NEW: Relationship to Students
     * Access all students in the class this teacher is assigned to.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id', 'class_id');
    }

    /**
     * CONTINUOUS ASSESSMENT: All class activities (classwork, homework,
     * quizzes, etc.) logged against this teaching assignment. Independent
     * of the `exams` table entirely.
     */
    public function activities()
    {
        return $this->hasMany(ClassActivity::class, 'subject_assignment_id');
    }
}
