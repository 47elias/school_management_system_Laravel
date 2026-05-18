<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolClass extends Model
{
    // Fix the table name mismatch
    protected $table = 'school_classes';

    protected $fillable = [
        'class_name',
        'class_code',
        'room_number',
        'capacity',
        'status',
        'teacher_id' // <--- CRITICAL: Add this to allow assigning teachers
    ];

    /**
     * Get the teacher that manages this class.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * The subjects that belong to the class.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'school_class_id', 'subject_id');
    }

    /**
     * Relationship: A class has many students.
     */
    public function students(): HasMany
    {
        // Ensure this points to the Student Model, not the User Model
        // 'class_id' is the column in your 'students' table
        return $this->hasMany(Student::class, 'class_id');
    }
}
