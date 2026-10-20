<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityMark extends Model implements AuditableContract
{
    use Auditable;
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
