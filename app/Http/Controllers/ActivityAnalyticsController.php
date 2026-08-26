<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * CONTINUOUS ASSESSMENT (CA) ANALYTICS
 * -----------------------------------------------------------------------
 * Read-only, school-wide statistical view over `class_activities` +
 * `activity_marks`. Every figure here is computed with a single grouped
 * SQL aggregate query (no N+1, no loading raw mark rows into PHP), so the
 * dashboard stays fast regardless of how many daily activities pile up.
 *
 * Two layers:
 *   1. STATISTICAL  - averages, rankings, trend, distribution (this file).
 *   2. AI            - the same aggregates are handed to an LLM (reusing
 *      the Gemini integration already used by ChatbotController) which
 *      turns the numbers into a short written analysis and recommendations.
 *      The AI never sees individual student records - only class/subject
 *      level aggregates - so there's no privacy exposure in the prompt.
 */
class ActivityAnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        $terms = Term::orderBy('id', 'desc')->get();
        $activeTerm = Term::where('is_current', 1)->first();
        $selectedTerm = $request->filled('term_id')
            ? Term::find($request->term_id)
            : ($activeTerm ?? $terms->first());

        $stats = $this->computeStats($selectedTerm?->id);

        return view('activities.analytics', array_merge($stats, [
            'terms'        => $terms,
            'selectedTerm' => $selectedTerm,
        ]));
    }

    /**
     * AJAX endpoint: recomputes the same aggregates and asks the AI model
     * for a narrative summary. Called on-demand (button click) rather than
     * on every page load, to avoid burning API calls for a dashboard that
     * might be refreshed/filtered often.
     */
    public function aiInsights(Request $request)
    {
        $request->validate(['term_id' => 'nullable|exists:terms,id']);

        $termId = $request->term_id ?: (Term::where('is_current', 1)->first()->id ?? null);
        $term = $termId ? Term::find($termId) : null;
        $stats = $this->computeStats($termId);

        if ($stats['overall']['marks_count'] == 0) {
            return response()->json([
                'insights' => "There isn't enough Continuous Assessment data recorded yet for "
                    . ($term->term_name ?? 'this term') . " to generate a meaningful analysis. "
                    . "Encourage teachers to log a few more activities first.",
            ]);
        }

        $summary = $this->buildStatsSummary($stats, $term);

        $systemPrompt = "You are an academic data analyst for a school. You will be given aggregated "
            . "Continuous Assessment (CA) statistics — daily classwork/homework/quiz/participation marks, "
            . "separate from final exams. You do NOT have access to individual student records, only "
            . "class- and subject-level aggregates.\n\n"
            . "DATA:\n{$summary}\n\n"
            . "INSTRUCTIONS:\n"
            . "1. Write a concise analysis (250-350 words) in plain English, no markdown headers.\n"
            . "2. Call out the best-performing and lowest-performing classes/subjects explicitly, with numbers.\n"
            . "3. Note any trend (improving, declining, stable) based on the weekly averages provided.\n"
            . "4. Flag anything that looks like a risk (e.g. a class/subject well below the pass mark, or low activity coverage).\n"
            . "5. End with 2-3 short, concrete, actionable recommendations for the administration.\n"
            . "6. Be direct and specific — reference actual class/subject names and percentages from the data, not generic advice.";

        try {
            $apiKey = config('services.gemini.key');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        ['parts' => [['text' => $systemPrompt]]],
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.4, // lower temperature: this is analytical, not conversational
                        'maxOutputTokens' => 700,
                    ],
                ]);

            if ($response->failed()) {
                throw new \Exception('Gemini API Error: ' . $response->body());
            }

            $result = $response->json();
            $insights = $result['candidates'][0]['content']['parts'][0]['text']
                ?? "The AI analysis service didn't return a response. Please try again.";

            return response()->json(['insights' => $insights]);
        } catch (\Exception $e) {
            \Log::error('CA AI Insights Error: ' . $e->getMessage());
            return response()->json([
                'insights' => "The AI analysis service is temporarily unavailable, but the charts and "
                    . "figures above reflect the current statistics. Please try again shortly.",
            ], 500);
        }
    }

    /**
     * Runs every aggregate query for the dashboard. Kept as one method so
     * both the page load and the AI endpoint use exactly the same numbers.
     */
    private function computeStats(?int $termId): array
    {
        $base = function () use ($termId) {
            return DB::table('activity_marks as am')
                ->join('class_activities as ca', 'ca.id', '=', 'am.class_activity_id')
                ->join('subject_assignments as sa', 'sa.id', '=', 'ca.subject_assignment_id')
                ->when($termId, fn($q) => $q->where('ca.term_id', $termId));
        };

        // --- Overall school-wide KPI numbers ---
        $overall = (clone $base())
            ->selectRaw('COUNT(am.id) as marks_count')
            ->selectRaw('COUNT(DISTINCT ca.id) as activity_count')
            ->selectRaw('COUNT(DISTINCT am.student_id) as student_count')
            ->selectRaw('AVG((am.score / ca.max_score) * 100) as avg_pct')
            ->selectRaw('SUM(CASE WHEN (am.score / ca.max_score) * 100 < 40 THEN 1 ELSE 0 END) as at_risk_count')
            ->first();

        $overall = [
            'marks_count'    => (int) ($overall->marks_count ?? 0),
            'activity_count' => (int) ($overall->activity_count ?? 0),
            'student_count'  => (int) ($overall->student_count ?? 0),
            'avg_pct'        => round((float) ($overall->avg_pct ?? 0), 2),
            'at_risk_count'  => (int) ($overall->at_risk_count ?? 0),
        ];

        // --- Per-class ranking (this is what surfaces best/worst performing class) ---
        $byClass = (clone $base())
            ->join('school_classes as sc', 'sc.id', '=', 'sa.class_id')
            ->groupBy('sc.id', 'sc.class_name')
            ->selectRaw('sc.id as class_id, sc.class_name')
            ->selectRaw('AVG((am.score / ca.max_score) * 100) as avg_pct')
            ->selectRaw('COUNT(am.id) as marks_count')
            ->havingRaw('COUNT(am.id) >= 1')
            ->orderByDesc('avg_pct')
            ->get()
            ->map(fn($row) => [
                'class_id'    => $row->class_id,
                'class_name'  => $row->class_name,
                'avg_pct'     => round((float) $row->avg_pct, 2),
                'marks_count' => (int) $row->marks_count,
            ]);

        $bestClass = $byClass->first();
        $worstClass = $byClass->count() > 1 ? $byClass->last() : null;

        // --- Per-subject breakdown ---
        $bySubject = (clone $base())
            ->join('subjects as sub', 'sub.id', '=', 'sa.subject_id')
            ->groupBy('sub.id', 'sub.subject_name')
            ->selectRaw('sub.id as subject_id, sub.subject_name')
            ->selectRaw('AVG((am.score / ca.max_score) * 100) as avg_pct')
            ->selectRaw('COUNT(am.id) as marks_count')
            ->orderByDesc('avg_pct')
            ->get()
            ->map(fn($row) => [
                'subject_id'  => $row->subject_id,
                'subject_name'=> $row->subject_name,
                'avg_pct'     => round((float) $row->avg_pct, 2),
                'marks_count' => (int) $row->marks_count,
            ]);

        // --- Weekly trend (ISO year-week bucket) ---
        $trend = (clone $base())
            ->selectRaw("YEARWEEK(ca.activity_date, 1) as yw")
            ->selectRaw('MIN(ca.activity_date) as week_start')
            ->selectRaw('AVG((am.score / ca.max_score) * 100) as avg_pct')
            ->groupBy('yw')
            ->orderBy('yw')
            ->get()
            ->map(fn($row) => [
                'week_start' => \Carbon\Carbon::parse($row->week_start)->format('d M'),
                'avg_pct'    => round((float) $row->avg_pct, 2),
            ]);

        // --- Breakdown by activity type ---
        $byType = (clone $base())
            ->groupBy('ca.type')
            ->selectRaw('ca.type')
            ->selectRaw('AVG((am.score / ca.max_score) * 100) as avg_pct')
            ->selectRaw('COUNT(am.id) as marks_count')
            ->orderByDesc('avg_pct')
            ->get()
            ->map(fn($row) => [
                'type'        => ucfirst($row->type),
                'avg_pct'     => round((float) $row->avg_pct, 2),
                'marks_count' => (int) $row->marks_count,
            ]);

        // --- Grade-bucket distribution across all individual activity scores ---
        $distributionRaw = (clone $base())
            ->selectRaw('(am.score / ca.max_score) * 100 as pct')
            ->get();

        $buckets = ['A (75-100)' => 0, 'B (65-74)' => 0, 'C (50-64)' => 0, 'D (45-49)' => 0, 'E (40-44)' => 0, 'U (0-39)' => 0];
        foreach ($distributionRaw as $row) {
            $pct = (float) $row->pct;
            if ($pct >= 75) $buckets['A (75-100)']++;
            elseif ($pct >= 65) $buckets['B (65-74)']++;
            elseif ($pct >= 50) $buckets['C (50-64)']++;
            elseif ($pct >= 45) $buckets['D (45-49)']++;
            elseif ($pct >= 40) $buckets['E (40-44)']++;
            else $buckets['U (0-39)']++;
        }

        // --- Top 5 / bottom 5 students overall for the term (min. 3 recorded marks to be statistically meaningful) ---
        $studentAverages = (clone $base())
            ->join('students as st', 'st.id', '=', 'am.student_id')
            ->join('school_classes as sc2', 'sc2.id', '=', 'sa.class_id')
            ->groupBy('st.id', 'st.name', 'st.surname', 'sc2.class_name')
            ->selectRaw("st.id, CONCAT(st.surname, ', ', st.name) as student_name, sc2.class_name")
            ->selectRaw('AVG((am.score / ca.max_score) * 100) as avg_pct')
            ->selectRaw('COUNT(am.id) as marks_count')
            ->havingRaw('COUNT(am.id) >= 3')
            ->get();

        $topStudents = $studentAverages->sortByDesc('avg_pct')->take(5)->values()->map(fn($r) => [
            'student_name' => $r->student_name, 'class_name' => $r->class_name,
            'avg_pct' => round((float) $r->avg_pct, 2), 'marks_count' => (int) $r->marks_count,
        ]);
        $bottomStudents = $studentAverages->sortBy('avg_pct')->take(5)->values()->map(fn($r) => [
            'student_name' => $r->student_name, 'class_name' => $r->class_name,
            'avg_pct' => round((float) $r->avg_pct, 2), 'marks_count' => (int) $r->marks_count,
        ]);

        return [
            'overall'         => $overall,
            'byClass'         => $byClass,
            'bestClass'       => $bestClass,
            'worstClass'      => $worstClass,
            'bySubject'       => $bySubject,
            'trend'           => $trend,
            'byType'          => $byType,
            'distribution'    => $buckets,
            'topStudents'     => $topStudents,
            'bottomStudents'  => $bottomStudents,
        ];
    }

    /** Turns the computed stats array into a compact plain-text block for the AI prompt. */
    private function buildStatsSummary(array $stats, ?Term $term): string
    {
        $lines = [];
        $lines[] = 'Term: ' . ($term->term_name ?? 'All terms') . ' (' . ($term->academic_year ?? 'N/A') . ')';
        $lines[] = "School-wide CA average: {$stats['overall']['avg_pct']}% across {$stats['overall']['marks_count']} recorded scores from {$stats['overall']['activity_count']} activities and {$stats['overall']['student_count']} students.";
        $lines[] = "Scores below 40% (at-risk): {$stats['overall']['at_risk_count']}.";

        $lines[] = "Class averages (best to worst):";
        foreach ($stats['byClass'] as $c) {
            $lines[] = "- {$c['class_name']}: {$c['avg_pct']}% ({$c['marks_count']} scores)";
        }

        $lines[] = "Subject averages (best to worst):";
        foreach ($stats['bySubject'] as $s) {
            $lines[] = "- {$s['subject_name']}: {$s['avg_pct']}% ({$s['marks_count']} scores)";
        }

        $lines[] = "Weekly average trend:";
        foreach ($stats['trend'] as $t) {
            $lines[] = "- Week of {$t['week_start']}: {$t['avg_pct']}%";
        }

        $lines[] = "Average by activity type:";
        foreach ($stats['byType'] as $t) {
            $lines[] = "- {$t['type']}: {$t['avg_pct']}% ({$t['marks_count']} scores)";
        }

        $lines[] = "Grade distribution across all recorded scores: " . collect($stats['distribution'])
            ->map(fn($count, $bucket) => "{$bucket}: {$count}")->implode(', ');

        return implode("\n", $lines);
    }
}
