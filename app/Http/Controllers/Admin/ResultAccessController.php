<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResultFeeExemption;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Term;
use App\Models\TermResultPublication;
use App\Services\StudentFinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultAccessController extends Controller
{
    public function index(Request $request)
    {
        $terms = Term::orderByDesc('start_date')->orderByDesc('id')->get();
        $term = $request->integer('term_id')
            ? $terms->firstWhere('id', $request->integer('term_id'))
            : ($terms->firstWhere('is_current', true) ?? $terms->first());

        $publication = $term
            ? TermResultPublication::with('publishedBy')->where('term_id', $term->id)->first()
            : null;

        $query = Student::with('schoolClass')->orderBy('surname')->orderBy('name');
        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(fn ($students) => $students
                ->where('name', 'like', "%{$search}%")
                ->orWhere('surname', 'like', "%{$search}%")
                ->orWhere('student_number', 'like', "%{$search}%"));
        }
        $students = $term ? $query->paginate(30)->withQueryString() : collect();
        $finance = app(StudentFinanceService::class);
        $exemptions = $term
            ? ResultFeeExemption::with(['student', 'grantedBy', 'revokedBy'])
                ->where('term_id', $term->id)->latest()->paginate(15, ['*'], 'history_page')->withQueryString()
            : collect();

        $studentRows = $students->getCollection()->map(function (Student $student) use ($term, $finance) {
            $exemption = $term
                ? ResultFeeExemption::where('student_id', $student->id)
                    ->where('term_id', $term->id)->whereNull('revoked_at')->latest()->first()
                : null;
            return [
                'student' => $student,
                'balance' => $term ? $finance->statement($student, $term)['balance'] : 0,
                'exemption' => $exemption,
            ];
        });

        return view('admin.results.access', compact('terms', 'term', 'publication', 'students', 'studentRows', 'exemptions'));
    }

    public function publish(Request $request)
    {
        $data = $request->validate([
            'term_id' => ['required', 'exists:terms,id'],
            'is_published' => ['required', 'boolean'],
        ]);
        $published = (bool) $data['is_published'];
        $publication = TermResultPublication::firstOrNew(['term_id' => $data['term_id']]);
        $publication->is_published = $published;
        $publication->published_by = $published ? Auth::id() : null;
        $publication->published_at = $published ? now() : null;
        $publication->save();
        if ($published) {
            // The term publication action is the administrator's release of all
            // marks already entered for that term.
            Exam::where('term_id', $data['term_id'])->update(['status' => 'published', 'updated_at' => now()]);
        }

        return redirect()->route('results.access', ['term_id' => $data['term_id']])
            ->with('success', $published ? 'Results have been published for this term.' : 'Results have been unpublished for this term.');
    }

    public function grantExemption(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $alreadyActive = ResultFeeExemption::where('student_id', $data['student_id'])
            ->where('term_id', $data['term_id'])->whereNull('revoked_at')->exists();
        if ($alreadyActive) {
            return back()->withInput()->withErrors(['student_id' => 'This student already has an active fee exemption for this term.']);
        }

        ResultFeeExemption::create([
            ...$data,
            'granted_by' => Auth::id(),
        ]);

        return redirect()->route('results.access', ['term_id' => $data['term_id']])
            ->with('success', 'Fee exemption recorded with its reason.');
    }

    public function revokeExemption(ResultFeeExemption $exemption)
    {
        if ($exemption->revoked_at) {
            return back()->with('error', 'This fee exemption has already been revoked.');
        }

        $exemption->revoked_by = Auth::id();
        $exemption->revoked_at = now();
        $exemption->save();

        return redirect()->route('results.access', ['term_id' => $exemption->term_id])
            ->with('success', 'Fee exemption revoked. The reason and audit history were retained.');
    }
}
