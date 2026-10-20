<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Mark;
use App\Models\Term;
use App\Models\FeeStructure;
use App\Models\ActivityMark;
use App\Models\ClassActivity;
use App\Services\StudentFinanceService;

class PortalController extends Controller
{
    /**
     * DASHBOARD: Summarized overview of current term status
     */
    public function dashboard()
    {
        $student = Auth::guard('student')->user();
        $avatar = ($student->gender == 'Female')
            ? asset('adminlte/dist/img/avatar3.png')
            : asset('adminlte/dist/img/avatar5.png');

        $finance = app(StudentFinanceService::class);
        $eligibleTerms = $finance->availableTerms($student);
        $currentTerm = $eligibleTerms->firstWhere('is_current', true) ?? $eligibleTerms->first();

        // If no term exists at all, prevent crash
        if (!$currentTerm) {
            return view('student.dashboard', ['student' => $student, 'avatar' => $avatar, 'currentAverage' => 0, 'calculatedBalance' => 0, 'totalOwed' => 0, 'totalPaid' => 0, 'carriedForward' => 0, 'paymentPercentage' => 0, 'currentTerm' => null]);
        }

        $statement = $finance->statement($student, $currentTerm);
        $totalOwed = $statement['term_charges'];
        $totalPaid = $statement['term_paid'];
        $calculatedBalance = $statement['balance'];
        $carriedForward = $statement['carried_forward'];
        $paymentBase = $totalOwed + abs($carriedForward);
        $paymentPercentage = $paymentBase > 0
            ? min(100, max(0, (($totalPaid + max(0, -$carriedForward)) / $paymentBase) * 100))
            : 0;

        // Academic Quick Stats for current term
        $recentResults = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($q) use ($currentTerm) {
                $q->where('term_id', $currentTerm->id);
            })->with('exam.subject')->get();

        // Use the new aggregator so the dashboard average matches the results page average
        $aggregatedResults = $this->aggregateMarksBySubject($recentResults);
        $currentAverage = $aggregatedResults->count() > 0 ? $aggregatedResults->avg('average_score') : 0;

        return view('student.dashboard', compact(
            'student', 'avatar', 'calculatedBalance',
            'totalOwed', 'totalPaid', 'carriedForward', 'paymentPercentage', 'currentTerm', 'currentAverage'
        ));
    }

    public function updates()
    {
        // Fetch active announcements, ordering high priority first, then by newest
        $announcements = \App\Models\Announcement::where('is_active', true)
            ->orderByRaw("FIELD(priority, 'high', 'normal', 'low')")
            ->latest()
            ->get();

        // Fetch newsletters meant for 'all' or specifically 'students'
        $newsletters = \App\Models\Newsletter::whereIn('target_audience', ['all', 'students'])
            ->latest()
            ->get();

        return view('student.updates', compact('announcements', 'newsletters'));
    }

    /**
     * FEES: Detailed statement with Term Switcher functionality
     */
    public function fees(Request $request)
    {
        $student = Auth::guard('student')->user();
        $finance = app(StudentFinanceService::class);
        $allTerms = $finance->availableTerms($student);

        // Term selection logic
        $selectedTermId = $request->get('term_id');
        $displayTerm = $selectedTermId ? $allTerms->firstWhere('id', (int) $selectedTermId) : null;
        $displayTerm = $displayTerm ?? $allTerms->firstWhere('is_current', true) ?? $allTerms->first();

        if (!$displayTerm) {
            return view('student.fees', [
                'student' => $student, 'allTerms' => $allTerms, 'displayTerm' => null,
                'payments' => collect(), 'balance' => 0, 'totalOwed' => 0, 'feeItems' => collect(),
                'carriedForward' => 0, 'termCharges' => 0, 'scheduledFees' => 0, 'termPayments' => 0,
                'termTransactions' => collect(), 'invoices' => collect(), 'receipts' => collect(),
                'feeStructures' => collect(), 'termInvoiceExists' => false,
            ]);
        }

        $statement = $finance->statement($student, $displayTerm);
        $individualFees = FeeStructure::where('term_id', $displayTerm->id)
            ->where('student_id', $student->id);
        if ($individualFees->exists()) {
            $feeItems = $individualFees->with('term')->get();
        } else {
            $class = $student->schoolClass()->with('gradeLevel')->first();
            $gradeNames = collect([$student->grade, $class?->gradeLevel?->name])->filter()->unique();
            $feeItems = FeeStructure::where('term_id', $displayTerm->id)
                ->whereNull('student_id')
                ->whereIn('grade', $gradeNames)
                ->where(fn ($query) => $query->whereNull('class_id')->orWhere('class_id', $student->class_id))
                ->with('schoolClass')
                ->get();
        }
        $payments = $statement['transactions']->sortByDesc('payment_date')->values();
        $carriedForward = $statement['carried_forward'];
        $totalOwed = $statement['term_charges'];
        $totalPaid = $statement['term_paid'];
        $balance = $statement['balance'];
        $termCharges = $statement['base_term_fees'];
        $scheduledFees = $statement['scheduled_fees'];
        $termPayments = $statement['term_paid'];
        $termTransactions = $statement['transactions'];
        $invoices = $termTransactions->where('amount_paid', '<', 0);
        $receipts = $termTransactions->where('amount_paid', '>', 0);
        $termInvoiceExists = $termTransactions->contains(fn ($payment) => $payment->payment_method === 'Term Invoice'
            || str_contains((string) $payment->remarks, 'Term Fees Charged'));
        $feeStructures = $feeItems;

        return view('student.fees', compact(
            'student', 'payments', 'balance', 'totalOwed', 'feeItems', 'allTerms', 'displayTerm',
            'carriedForward', 'termCharges', 'scheduledFees', 'termPayments', 'termTransactions', 'invoices', 'receipts', 'feeStructures', 'termInvoiceExists'
        ));
    }

    /**
     * RESULTS: Academic performance with detailed term filtering
     */
    public function results(Request $request)
    {
        $student = Auth::guard('student')->user();
        $finance = app(StudentFinanceService::class);
        $allTerms = $finance->availableTerms($student);
        $enrollmentDate = $finance->enrollmentDate($student)->toDateString();

        // 1. Identify which term to display
        $selectedTermId = $request->get('term_id');
        if ($selectedTermId) {
            $activeTerm = $allTerms->firstWhere('id', (int) $selectedTermId);
        } else {
            $activeTerm = $allTerms->firstWhere('is_current', true) ?? $allTerms->first();
        }

        if (!$activeTerm) {
            return view('student.results', [
                'student' => $student, 'current_results' => collect(), 'history' => collect(),
                'average' => 0, 'activeTerm' => null, 'allTerms' => $allTerms,
            ]);
        }

        // 2. Fetch raw marks for the current active/selected term
        $raw_current_results = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($query) use ($activeTerm) {
                $query->where('term_id', $activeTerm->id);
            })
            ->whereHas('exam', fn ($query) => $query->whereDate('exam_date', '>=', $enrollmentDate))
            ->with(['exam.subject', 'exam.term'])
            ->get();

        // 3. Aggregate current term results (Averages Paper 1 + Paper 2 automatically)
        $current_results = $this->aggregateMarksBySubject($raw_current_results);

        // 4. Calculate overall average based on the final aggregated subject scores
        $average = $current_results->count() > 0 ? (float) $current_results->avg('average_score') : 0;

        // 5. Historical Data: Grouped results for all other terms
        $history = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($query) use ($activeTerm, $enrollmentDate, $allTerms) {
                $query->whereIn('term_id', $allTerms->pluck('id'))
                    ->where('term_id', '!=', $activeTerm->id)
                    ->whereDate('exam_date', '>=', $enrollmentDate);
            })
            ->with(['exam.term', 'exam.subject'])
            ->get()
            ->groupBy(fn($item) => $item->exam->term
                ? $item->exam->term->term_name . ' (' . $item->exam->term->academic_year . ')'
                : 'Archive')
            ->map(function ($termMarks) {
                return $this->aggregateMarksBySubject($termMarks);
            });

        // Pass exact variable names the Blade view expects
        return view('student.results', compact(
            'student', 'current_results', 'history', 'average', 'activeTerm', 'allTerms'
        ));
    }

    /** Read only continuous assessment record for the signed in student. */
    public function continuousAssessment(Request $request)
    {
        $student = Auth::guard('student')->user();
        $finance = app(StudentFinanceService::class);
        $allTerms = $finance->availableTerms($student);
        $enrollmentDate = $finance->enrollmentDate($student)->toDateString();
        $requested = $request->integer('term_id');
        $activeTerm = $requested ? $allTerms->firstWhere('id', $requested) : null;
        $activeTerm ??= $allTerms->firstWhere('is_current', true) ?? $allTerms->first();

        $activityMarks = collect();
        $subjectSummary = collect();
        if ($activeTerm) {
            $activityMarks = ActivityMark::query()
                ->where('student_id', $student->id)
                ->whereHas('classActivity', fn ($query) => $query
                    ->where('term_id', $activeTerm->id)
                    ->whereDate('activity_date', '>=', $enrollmentDate))
                ->with(['classActivity.term', 'classActivity.subjectAssignment.subject', 'classActivity.subjectAssignment.schoolClass'])
                ->orderByDesc(ClassActivity::select('activity_date')->whereColumn('class_activities.id', 'activity_marks.class_activity_id'))
                ->get();

            $subjectSummary = $activityMarks->groupBy(fn ($mark) => $mark->classActivity?->subjectAssignment?->subject_id)
                ->map(function ($marks) {
                    $valid = $marks->filter(fn ($mark) => (float) ($mark->classActivity?->max_score ?? 0) > 0);
                    $average = $valid->isEmpty() ? 0 : $valid->avg(fn ($mark) => ($mark->score / $mark->classActivity->max_score) * 100);
                    return (object) [
                        'subject' => $marks->first()->classActivity?->subjectAssignment?->subject?->subject_name ?? 'Subject',
                        'count' => $valid->count(),
                        'average' => round($average, 1),
                    ];
                })->values();
        }

        return view('student.continuous_assessment', compact('student', 'allTerms', 'activeTerm', 'activityMarks', 'subjectSummary'));
    }

    /**
     * SECURITY: Password Management
     */
    public function changePassword()
    {
        $student = Auth::guard('student')->user();
        return view('student.change_password', compact('student'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $student = Auth::guard('student')->user();

        if (!Hash::check($request->current_password, $student->password)) {
            return back()->withErrors(['current_password' => 'The old password you entered is incorrect.']);
        }

        $student->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    /* 
    |--------------------------------------------------------------------------
    | HELPER METHODS FOR AGGREGATING MULTIPLE PAPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Groups raw marks by subject and calculates the final average.
     * Automatically handles Paper 1, Paper 2, etc.
     */
    private function aggregateMarksBySubject($marksCollection)
    {
        return $marksCollection->groupBy('exam.subject_id')->map(function ($subjectMarks) {
            // Calculate the average score across all papers for this subject
            $validMarks = $subjectMarks->filter(fn ($mark) => (float) ($mark->max_score ?: $mark->exam?->max_marks ?: 0) > 0);
            $totalMaxMarks = $validMarks->sum(fn ($mark) => (float) ($mark->max_score ?: $mark->exam->max_marks));
            $totalObtained = $validMarks->sum('score');
            $percentage = $totalMaxMarks > 0 ? ($totalObtained / $totalMaxMarks) * 100 : 0;
            $averageScore = $percentage;

            return (object) [
                'subject_id'    => $subjectMarks->first()->exam->subject_id,
                'subject_name'  => $subjectMarks->first()->exam->subject->subject_name,
                'papers_taken'  => $validMarks->count(),
                'total_score'   => $totalObtained,
                'average_score' => round($averageScore, 2),
                'percentage'    => round($percentage, 2),
                'grade'         => $this->calculateGrade(round($percentage)),
                'individual'    => $subjectMarks // Raw paper data included so the view can list them
            ];
        })->values(); // Reset keys for easy iteration in blade
    }

    /**
     * Resolves the letter grade based on the average score.
     */
    private function calculateGrade($score)
    {
        if ($score >= 75) return 'A';
        if ($score >= 65) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 45) return 'D';
        if ($score >= 40) return 'E';
        return 'U';
    }
}
