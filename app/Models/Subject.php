<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_name',
        'subject_code',
        'type',
        'pass_mark',
    ];

    /**
     * Get all school classes directly assigned to this subject.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject', 'subject_id', 'school_class_id');
    }

    /**
     * Get all subject combinations that include this subject.
     */
    public function combinations(): BelongsToMany
    {
        return $this->belongsToMany(SubjectCombination::class, 'subject_combination_subject');
    }

    /**
     * Get all teaching load allocations and class assignments for this subject.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(SubjectAssignment::class, 'subject_id');
    }
}