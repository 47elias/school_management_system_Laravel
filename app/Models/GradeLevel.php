<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'level_order'];

    /**
     * Get all sub-classes / streams under this Form (e.g. Form 1 -> 1A, 1B, 1C).
     */
    public function schoolClasses()
    {
        return $this->hasMany(SchoolClass::class, 'grade_level_id');
    }

    /**
     * Get all students enrolled across all streams of this Form level.
     */
    public function students()
    {
        return $this->hasManyThrough(Student::class, SchoolClass::class, 'grade_level_id', 'class_id');
    }
}