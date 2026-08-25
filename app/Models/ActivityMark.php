<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityMark extends Model
{
    protected $fillable = [
        'class_activity_id',
        'student_id',
        'score',
        'comment',
    ];

    protected $casts = [
        'score' => 'float',
    ];

    public function classActivity(): BelongsTo
    {
        return $this->belongsTo(ClassActivity::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getPercentAttribute(): float
    {
        $max = $this->classActivity->max_score ?? 100;
        return $max ? round(($this->score / $max) * 100, 2) : 0.0;
    }
}
