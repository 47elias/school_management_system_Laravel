<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TermController extends Controller
{
    /**
     * Display a listing of the academic terms.
     */
    public function index()
    {
        $terms = Term::orderBy('academic_year', 'desc')
                    ->orderBy('start_date', 'desc')
                    ->get();
        return view('terms.manage', compact('terms'));
    }

    /**
     * Store a newly created term.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'term_name' => 'required|string|max:255',
            'academic_year' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // LOGIC: Even if the user selects 4 months, we force the duration
        // logic in our fee calculations. We don't need to change the dates here,
        // but we ensure the "is_current" logic remains.

        $data['is_current'] = Term::count() === 0;

        Term::create($data);
        return back()->with('success', 'Academic Term created successfully! Note: Fee installments are automatically calculated based on a fixed 3-month cycle.');
    }

    /**
     * Update an existing term.
     */
    public function update(Request $request, $id)
    {
        $term = Term::findOrFail($id);

        $data = $request->validate([
            'term_name' => 'required|string|max:255',
            'academic_year' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $term->update($data);
        return back()->with('success', 'Term details updated successfully!');
    }

    /**
     * Set a term as the active/current term.
     */
    public function activate($id)
    {
        DB::transaction(function () use ($id) {
            Term::where('is_current', true)->update(['is_current' => false]);
            $term = Term::findOrFail($id);
            $term->update(['is_current' => true]);
        });

        return back()->with('success', 'Term activated successfully. Fee calculations are now locked to a 3-month installment plan for this term.');
    }

    public function destroy($id)
    {
        $term = Term::findOrFail($id);
        $hasPayments = DB::table('payments')->where('term_id', $id)->exists();

        if ($hasPayments) {
            return back()->with('error', 'Cannot delete this term because it has recorded payments.');
        }

        $term->delete();
        return back()->with('success', 'Term deleted successfully.');
    }
}
