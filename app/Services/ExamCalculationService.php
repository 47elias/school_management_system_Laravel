<?php

namespace App\Services;

use App\Models\Mark;
use App\Models\GradeScale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ExamCalculationService
{
    /**
     * Groups raw marks collection by subject and calculates dynamic average percentages and grades.
     */
    public function aggregateMarksBySubject(Collection $marksCollection): Collection
    {
        return $marksCollection->groupBy(function ($mark) {
            return $mark->exam->subject_id ?? $mark->subject_id;
        })->map(function ($subjectMarks) {
            $firstMark = $subjectMarks->first();
            $subject = $firstMark->exam->subject ?? null;
            $passMark = $subject->pass_mark ?? 50;

            $totalPercentage = 0;
            $papers = [];

            foreach ($subjectMarks as $mark) {
                $maxScore = ($mark->max_score && $mark->max_score > 0) ? $mark->max_score : 100;
                $pct = ($mark->score / $maxScore) * 100;
                $totalPercentage += $pct;

                $papers[] = [
                    'exam_id'    => $mark->exam_id,
                    'exam_name'  => $mark->exam->exam_name ?? 'Paper',
                    'score'      => $mark->score,
                    'max_score'  => $maxScore,
                    'percentage' => round($pct, 2),
                    'comment'    => $mark->teacher_comment
                ];
            }

            $papersCount = $subjectMarks->count();
            $averagePercentage = $papersCount > 0 ? round($totalPercentage / $papersCount, 2) : 0;
            $gradeDetails = $this->calculateGrade($averagePercentage);

            return (object) [
                'subject_id'    => $subject->id ?? $firstMark->exam->subject_id,
                'subject_name'  => $subject->subject_name ?? $firstMark->subject,
                'subject_code'  => $subject->subject_code ?? 'N/A',
                'pass_mark'     => $passMark,
                'papers_taken'  => $papersCount,
                'average_score' => $averagePercentage,
                'percentage'    => $averagePercentage,
                'grade'         => $gradeDetails['grade'],
                'remark'        => $gradeDetails['remark'],
                'is_passed'     => $averagePercentage >= $passMark,
                'papers'        => $papers,
                'individual'    => $subjectMarks
            ];
        })->values();
    }

    /**
     * Dynamically resolves the letter grade and remark from database configuration.
     */
    public function calculateGrade(float $score): array
    {
        $scales = Cache::remember('active_grade_scales', 3600, function () {
            return GradeScale::orderBy('min_score', 'desc')->get();
        });

        foreach ($scales as $scale) {
            if ($score >= $scale->min_score && $score <= $scale->max_score) {
                return [
                    'grade'  => $scale->grade,
                    'remark' => $scale->remark ?? '',
                ];
            }
        }

        return [
            'grade'  => 'N/A',
            'remark' => 'Unrated',
        ];
    }
}