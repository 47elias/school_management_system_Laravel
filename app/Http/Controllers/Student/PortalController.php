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
            })->get();

        $currentAverage = $recentResults->avg('score') ?? 0;

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
            $displayTerm = Term::find($selectedTermId);
        } else {
            $displayTerm = Term::where('is_current', true)->first() ?? $allTerms->first();
        }

        if (!$displayTerm) {
            return back()->with('error', 'No academic terms found.');
        }

        // 2. Fetch results for the selected term
        // Added deeper Eager Loading (exam.subject, exam.term) to ensure Blade has everything
        $termResults = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($q) use ($displayTerm) {
                $q->where('term_id', $displayTerm->id);
            })
            ->with(['exam.subject', 'exam.term'])
            ->get();

        // 3. Historical Data: Grouped results for all other terms
        $history = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($q) use ($displayTerm) {
                $q->where('term_id', '!=', $displayTerm->id);
            })
            ->with(['exam.subject', 'exam.term'])
            ->get()
            ->groupBy(function($mark) {
                $term = $mark->exam?->term;
                return $term ? $term->term_name . ' (' . $term->academic_year . ')' : 'Archived Data';
            });

        // 4. Calculate Average Score (ensure it handles decimals correctly)
        $average = $termResults->count() > 0 ? (float) $termResults->avg('score') : 0;

        return view('student.results', compact(
            'student', 'termResults', 'history', 'average', 'allTerms', 'displayTerm'
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
}
