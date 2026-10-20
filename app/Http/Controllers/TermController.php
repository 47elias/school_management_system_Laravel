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

        // Handle case-sensitivity across Windows/Linux view directories
        $viewName = view()->exists('terms.manage') ? 'terms.manage' : 'Terms.manage';

        return view($viewName, compact('terms'));
    }

    /**
     * Store a newly created term in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'term_name'     => 'required|string|max:255',
            'academic_year' => 'required|integer|min:2000|max:2100',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'status'        => 'nullable|string|in:active,open,closed',
        ]);

        // Auto-activate if this is the first term registered
        $data['is_current'] = Term::count() === 0;
        $data['status'] = $data['status'] ?? 'active';

        Term::create($data);

        return back()->with('success', 'Academic Term created successfully!');
    }

    /**
     * Update an existing term's details.
     */
    public function update(Request $request, $id)
    {
        $term = Term::findOrFail($id);

        $data = $request->validate([
            'term_name'     => 'required|string|max:255',
            'academic_year' => 'required|integer|min:2000|max:2100',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'status'        => 'nullable|string|in:active,open,closed',
        ]);

        $term->update($data);

        return back()->with('success', 'Term details updated successfully!');
    }

    /**
     * Set a term as the active/current system term.
     */
    public function activate($id)
    {
        DB::transaction(function () use ($id) {
            Term::where('is_current', true)->update(['is_current' => false]);
            
            $term = Term::findOrFail($id);
            $term->update([
                'is_current' => true,
                'status'     => 'active',
            ]);
        });

        return back()->with('success', 'Academic Term activated successfully.');
    }

    /**
     * Remove the specified term from storage.
     */
    public function destroy($id)
    {
        $term = Term::findOrFail($id);

        $hasPayments = DB::table('payments')->where('term_id', $id)->exists();
        $hasExams    = DB::table('exams')->where('term_id', $id)->exists();

        if ($hasPayments || $hasExams) {
            return back()->with('error', 'Cannot delete this term as it has recorded payments or scheduled exams.');
        }

        $term->delete();

        return back()->with('success', 'Term deleted successfully.');
    }
}