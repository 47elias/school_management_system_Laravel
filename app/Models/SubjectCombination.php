<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubjectCombination extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'grade',
    ];

    /**
     * The subjects that belong to the combination package.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_combination_subject');
    }
}