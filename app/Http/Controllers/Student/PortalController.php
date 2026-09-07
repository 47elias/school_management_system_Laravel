<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Mark;
use App\Models\Term;
use App\Models\Payment;
use App\Models\FeeStructure;

class PortalController extends Controller
{
    /**
     * DASHBOARD: Summarized overview of current term status
     */
    public function dashboard()
    {
        $student = Auth::guard('student')->user();

        // Use the explicitly marked 'current' term, or the most recent one
        $currentTerm = Term::where('is_current', true)->first() ?? Term::latest()->first();

        // If no term exists at all, prevent crash
        if (!$currentTerm) {
            return view('student.dashboard', ['student' => $student, 'currentAverage' => 0, 'calculatedBalance' => 0]);
        }

        // Financial Summary for the current term
        $totalOwed = FeeStructure::where('grade', $student->grade)
                    ->where('term_id', $currentTerm->id)
                    ->sum('amount');

        $totalPaid = Payment::where('student_id', $student->id)
                    ->where('term_id', $currentTerm->id)
                    ->sum('amount_paid');

        $calculatedBalance = $totalOwed - $totalPaid;
        $paymentPercentage = ($totalOwed > 0) ? ($totalPaid / $totalOwed) * 100 : 0;

        // Academic Quick Stats for current term
        $recentResults = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($q) use ($currentTerm) {
                $q->where('term_id', $currentTerm->id);
            })->with('exam.subject')->get();

        // Use the new aggregator so the dashboard average matches the results page average
        $aggregatedResults = $this->aggregateMarksBySubject($recentResults);
        $currentAverage = $aggregatedResults->count() > 0 ? $aggregatedResults->avg('average_score') : 0;

        $avatar = ($student->gender == 'Female')
                ? asset('adminlte/dist/img/avatar3.png')
                : asset('adminlte/dist/img/avatar5.png');

        return view('student.dashboard', compact(
            'student', 'avatar', 'calculatedBalance',
            'totalOwed', 'totalPaid', 'paymentPercentage', 'currentTerm', 'currentAverage'
        ));
    }

    /**
     * FEES: Detailed statement with Term Switcher functionality
     */
    public function fees(Request $request)
    {
        $student = Auth::guard('student')->user();
        $allTerms = Term::orderBy('id', 'desc')->get();

        // Term selection logic
        $selectedTermId = $request->get('term_id');
        $displayTerm = $selectedTermId ? Term::find($selectedTermId) : Term::where('is_current', true)->first();
        $displayTerm = $displayTerm ?? $allTerms->first();

        if (!$displayTerm) return back()->with('error', 'No term data available.');

        // Fetch term-specific financial data
        $feeItems = FeeStructure::where('grade', $student->grade)
                    ->where('term_id', $displayTerm->id)
                    ->get();

        $payments = Payment::where('student_id', $student->id)
                    ->where('term_id', $displayTerm->id)
                    ->orderBy('payment_date', 'desc')
                    ->get();

        $totalOwed = $feeItems->sum('amount');
        $totalPaid = $payments->sum('amount_paid');
        $balance = $totalOwed - $totalPaid;

        return view('student.fees', compact(
            'student', 'payments', 'balance',
            'totalOwed', 'feeItems', 'allTerms', 'displayTerm'
        ));
    }

    /**
     * RESULTS: Academic performance with detailed term filtering
     */
    public function results(Request $request)
    {
        $student = Auth::guard('student')->user();
        $allTerms = Term::orderBy('id', 'desc')->get();

        // 1. Identify which term to display
        $selectedTermId = $request->get('term_id');
        if ($selectedTermId) {
            $activeTerm = Term::find($selectedTermId);
        } else {
            $activeTerm = Term::where('is_current', 1)->first() ?? $allTerms->first();
        }

        if (!$activeTerm) {
            return back()->with('error', 'No academic terms found in the system.');
        }

        // 2. Fetch raw marks for the current active/selected term
        $raw_current_results = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($query) use ($activeTerm) {
                $query->where('term_id', $activeTerm->id);
            })
            ->with(['exam.subject', 'exam.term'])
            ->get();

        // 3. Aggregate current term results (Averages Paper 1 + Paper 2 automatically)
        $current_results = $this->aggregateMarksBySubject($raw_current_results);

        // 4. Calculate overall average based on the final aggregated subject scores
        $average = $current_results->count() > 0 ? (float) $current_results->avg('average_score') : 0;

        // 5. Historical Data: Grouped results for all other terms
        $history = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($query) use ($activeTerm) {
                $query->where('term_id', '!=', $activeTerm->id);
            })
            ->with(['exam.term', 'exam.subject'])
            ->get()
            ->groupBy(fn($item) => $item->exam->term->term_name ?? 'Archive')
            ->map(function ($termMarks) {
                return $this->aggregateMarksBySubject($termMarks);
            });

        // Pass exact variable names the Blade view expects
        return view('student.results', compact(
            'student', 'current_results', 'history', 'average', 'activeTerm', 'allTerms'
        ));
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
            $averageScore = $subjectMarks->avg('score');
            
            // Calculate percentage based on max marks 
            $totalMaxMarks = $subjectMarks->sum('exam.max_marks');
            $totalObtained = $subjectMarks->sum('score');
            $percentage = ($totalMaxMarks > 0) ? (($totalObtained / $totalMaxMarks) * 100) : 0;

            return (object) [
                'subject_id'    => $subjectMarks->first()->exam->subject_id,
                'subject_name'  => $subjectMarks->first()->exam->subject->subject_name,
                'papers_taken'  => $subjectMarks->count(),
                'total_score'   => $totalObtained,
                'average_score' => round($averageScore, 2),
                'percentage'    => round($percentage, 2),
                'grade'         => $this->calculateGrade(round($averageScore)),
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
        if ($score >= 40) return 'E';
        if ($score >= 39) return 'U';
        return 'F';
    }
}