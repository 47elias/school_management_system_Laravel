<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Mark extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'exam_id',
        'student_id',
        'subject',
        'score',
        'max_score',
        'teacher_comment'
    ];

    /**
     * Relationship: The student this mark belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship: The exam this mark was recorded for.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    /**
     * Relationship: Access the Term directly via the Exam.
     * Useful for: $mark->term->term_name
     */
    public function term(): HasOneThrough
    {
        return $this->hasOneThrough(
            Term::class,
            Exam::class,
            'id',      // Foreign key on exams table...
            'id',      // Foreign key on terms table...
            'exam_id', // Local key on marks table...
            'term_id'  // Local key on exams table...
        );
    }

    /**
     * Accessor: Get the letter grade for this specific mark instance.
     * Usage in Blade: {{ $mark->grade_letter }}
     */
    public function getGradeLetterAttribute(): string
    {
        return self::getGrade($this->score);
    }

    /**
     * Updated Grade Logic
     * Explicitly cast to (int) to ensure 50% correctly maps to 'C'.
     * This logic aligns with standard academic thresholds.
     */
    public static function getGrade($score): string
    {
        $score = (int)$score;

        if ($score >= 75) return 'A';
        if ($score >= 65) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 45) return 'D';
        if ($score >= 40) return 'E';
        return 'U';
    }

    /**
     * Helper: Get CSS classes for styling based on grade.
     * Perfect for Tailwind/AdminLTE integration.
     */
    public function getGradeColorAttribute(): string
    {
        $score = (int)$this->score;

        if ($score >= 75) return 'text-green-600 font-bold'; // A
        if ($score >= 50) return 'text-blue-600';           // B, C
        if ($score >= 40) return 'text-orange-500';         // D, E
        return 'text-red-600 font-bold';                    // U
    }
}
