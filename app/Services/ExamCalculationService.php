<?php

namespace App\Services;

use App\Models\GradeScale;
use Illuminate\Support\Collection;

class ExamCalculationService
{
    /**
     * Dynamically determine grade, remark, and pass status using the grade_scales database table.
     */
    public function calculateGrade(float $score): array
    {
        $score = round($score, 2);

        $scale = GradeScale::where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();

        if ($scale) {
            $isPassed = !in_array(strtoupper($scale->grade), ['F', 'U', 'E']);
            return [
                'grade'     => $scale->grade,
                'remark'    => $scale->remark ?? 'Evaluated',
                'is_passed' => $isPassed
            ];
        }

        // Fallback standard scale if table is empty
        if ($score >= 80) return ['grade' => 'A', 'remark' => 'Distinction', 'is_passed' => true];
        if ($score >= 70) return ['grade' => 'B', 'remark' => 'Merit', 'is_passed' => true];
        if ($score >= 60) return ['grade' => 'C', 'remark' => 'Credit', 'is_passed' => true];
        if ($score >= 50) return ['grade' => 'D', 'remark' => 'Pass', 'is_passed' => true];
        if ($score >= 40) return ['grade' => 'E', 'remark' => 'Subordinate Pass', 'is_passed' => false];
        return ['grade' => 'F', 'remark' => 'Fail', 'is_passed' => false];
    }

    /**
     * Aggregate raw paper marks by subject and compute overall scores.
     */
    public function aggregateMarksBySubject(Collection $rawMarks): Collection
    {
        return $rawMarks->groupBy(function ($mark) {
            return $mark->exam->subject_id ?? $mark->subject;
        })->map(function ($subjectMarks) {
            $firstMark = $subjectMarks->first();
            $subjectName = $firstMark->exam->subject->subject_name ?? $firstMark->subject ?? 'N/A';
            $subjectCode = $firstMark->exam->subject->subject_code ?? 'SUBJ';

            $totalPercentageSum = 0;
            $papers = [];

            foreach ($subjectMarks as $m) {
                $maxScore = $m->max_score > 0 ? $m->max_score : 100;
                $percentage = round(($m->score / $maxScore) * 100, 2);
                $totalPercentageSum += $percentage;

                $papers[] = [
                    'exam_id'    => $m->exam_id,
                    'exam_name'  => $m->exam->exam_name ?? 'Paper',
                    'score'      => $m->score,
                    'max_score'  => $maxScore,
                    'percentage' => $percentage,
                    'comment'    => $m->teacher_comment
                ];
            }

            $papersCount = count($papers);
            $averageScore = $papersCount > 0 ? round($totalPercentageSum / $papersCount, 2) : 0;
            $gradeInfo = $this->calculateGrade($averageScore);

            return (object) [
                'subject_id'   => $firstMark->exam->subject_id ?? null,
                'subject_name' => $subjectName,
                'subject_code' => $subjectCode,
                'papers'       => $papers,
                'papers_taken' => $papersCount,
                'average_score'=> $averageScore,
                'grade'        => $gradeInfo['grade'],
                'remark'       => $gradeInfo['remark'],
                'is_passed'    => $gradeInfo['is_passed']
            ];
        })->values();
    }
}