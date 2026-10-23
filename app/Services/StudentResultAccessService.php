<?php

namespace App\Services;

use App\Models\ResultFeeExemption;
use App\Models\Student;
use App\Models\Term;
use App\Models\TermResultPublication;

class StudentResultAccessService
{
    public function isPublished(Term $term): bool
    {
        return (bool) TermResultPublication::where('term_id', $term->id)
            ->where('is_published', true)
            ->exists();
    }

    public function hasActiveFeeExemption(Student $student, Term $term): bool
    {
        return ResultFeeExemption::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->whereNull('revoked_at')
            ->exists();
    }

    public function feeBalance(Student $student, Term $term): float
    {
        return (float) app(StudentFinanceService::class)->statement($student, $term)['balance'];
    }

    public function canView(Student $student, Term $term): bool
    {
        if (!$this->isPublished($term)) {
            return false;
        }

        return $this->feeBalance($student, $term) <= 0.004
            || $this->hasActiveFeeExemption($student, $term);
    }
}
