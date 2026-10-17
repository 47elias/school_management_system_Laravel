<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_level_id',
        'class_name',
        'stream',
        'class_code',
        'room_number',
        'capacity',
        'teacher_id',
    ];

    /**
     * Get the grade level / form this class stream belongs to.
     */
    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    /**
     * Get the form teacher assigned to this class.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Alias for teacher relationship for backward compatibility across views.
     */
    public function classTeacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get all students enrolled in this class stream.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * Get all subjects assigned to this class stream.
     * Pivot column: school_class_id
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'school_class_id', 'subject_id');
    }
}