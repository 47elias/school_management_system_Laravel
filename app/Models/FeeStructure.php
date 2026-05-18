<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeStructure extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * * LOGIC PRESERVED: Includes individual billing fields.
     */
    protected $fillable = [
        'fee_name',
        'amount',
        'grade',
        'term_id',
        'student_id',
    ];

    /**
     * Relationship: The term this fee structure belongs to.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Relationship: The specific student this fee applies to (if any).
     * * LOGIC PRESERVED: Supports individual billing.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
