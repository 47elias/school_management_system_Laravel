<?php

namespace App\Http\Controllers;

use App\Models\ActivityMark;
use App\Models\ClassActivity;
use App\Models\Student;
use App\Models\SubjectAssignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use App\Models\GradeLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * CONTINUOUS ASSESSMENT (CA) CONTROLLER
 * -----------------------------------------------------------------------
 * Handles day-to-day, in-class activities (classwork, homework, quizzes,
 * participation, practicals, projects, etc.).
 */
class ActivityController extends Controller
{
    /**
     * TEACHER: Landing page listing assigned classes and subject teaching load.
     */
    public function index()
    {
        $assignments = SubjectAssignment::with(['subject', 'schoolClass', 'gradeClasses'])
            ->where('teacher_id', Auth::id())->get();

        $counts = \App\Models\ClassActivity::whereIn('subject_assignment_id', $assignments->pluck('id')->unique())
            ->selectRaw('subject_assignment_id, COUNT(*) as total')
            ->groupBy('subject_assignment_id')
            ->pluck('total', 'subject_assignment_id');

        $assignments->each(fn($a) => $a->activities_count = $counts[$a->id] ?? 0);

        return view('teachers.activities.index', compact('assignments'));
    }

    /**
     * TEACHER: Store a newly logged in-class activity.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_assignment_id' => 'required|exists:subject_assignments,id',
            'title'                 => 'required|string|max:255',
            'type'                  => 'required|in:classwork,homework,quiz,participation,practical,project,other',
            'activity_date'         => 'required|date',
            'max_score'             => 'required|integer|min:1|max:1000',
            'weight'                => 'nullable|numeric|min:0.1|max:10',
            'notes'                 => 'nullable|string|max:1000',
        ]);

        $assignment = SubjectAssignment::findOrFail($validated['subject_assignment_id']);
        $this->authorizeAssignment($assignment);

        $term = Term::where('is_current', 1)->first() ?? Term::latest('id')->first();

        $activity = ClassActivity::create([
            'subject_assignment_id' => $assignment->id,
            'term_id'               => $term?->id,
            'title'                 => $validated['title'],
            'type'                  => $validated['type'],
            'activity_date'         => $validated['activity_date'],
            'max_score'             => $validated['max_score'],
            'weight'                => $validated['weight'] ?? 1.00,
            'notes'                 => $validated['notes'] ?? null,
            'created_by'            => Auth::id(),
        ]);

        return redirect()
            ->route('teacher.activities.record', $activity->id)
            ->with('success', 'Activity logged successfully. Record scores below.');
    }

    /**
     * TEACHER: Mark-entry grid for a specific activity.
     */
    public function recordMarks($id)
    {
        $activity = ClassActivity::with(['subjectAssignment.subject', 'subjectAssignment.schoolClass', 'subjectAssignment.gradeClasses', 'term'])
            ->findOrFail($id);

        $this->authorizeAssignment($activity->subjectAssignment);

        $students = $this->rosterFor($activity->subjectAssignment);

        $marks = ActivityMark::where('class_activity_id', $activity->id)
            ->get()
            ->keyBy('student_id');

        return view('teachers.activities.record_marks', compact('activity', 'students', 'marks'));
    }

    /**
     * TEACHER: Save/update student scores for an activity.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'activity_id'     => 'required|exists:class_activities,id',
            'marks'           => 'required|array',
            'marks.*.score'   => ['nullable', 'numeric', 'min:0'],
            'marks.*.comment' => 'nullable|string|max:255',
        ]);

        $activity = ClassActivity::with('subjectAssignment')->findOrFail($validated['activity_id']);
        $this->authorizeAssignment($activity->subjectAssignment);

        $allowedIds = $this->rosterFor($activity->subjectAssignment)->pluck('id')->map(fn ($id) => (string) $id)->all();
        foreach ($validated['marks'] as $studentId => $data) {
            if (!in_array((string) $studentId, $allowedIds, true)) {
                abort(422, 'A selected student is not in this assigned class.');
            }
            if (isset($data['score']) && $data['score'] !== '' && $data['score'] !== null && $data['score'] > $activity->max_score) {
                throw \Illuminate\Validation\ValidationException::withMessages(['marks' => "Scores cannot be more than {$activity->max_score}."]);
            }
        }

        DB::beginTransaction();
        try {
            foreach ($validated['marks'] as $studentId => $data) {
                if (isset($data['score']) && $data['score'] !== '' && $data['score'] !== null) {
                    ActivityMark::updateOrCreate(
                        ['class_activity_id' => $activity->id, 'student_id' => $studentId],
                        [
                            'score'   => (float) $data['score'],
                            'comment' => $data['comment'] ?? null,
                        ]
                    );
                }
            }
            DB::commit();

            return back()->with('success', 'Activity scores saved successfully!');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Failed to save scores: ' . $e->getMessage());
        }
    }

    /**
     * TEACHER: Activity history and running CA averages for an assignment.
     */
    public function history($assignmentId)
    {
        $assignment = SubjectAssignment::with(['subject', 'schoolClass', 'gradeClasses'])->findOrFail($assignmentId);
        $this->authorizeAssignment($assignment);

        $term = Term::where('is_current', 1)->first() ?? Term::latest('id')->first();

        $activities = ClassActivity::where('subject_assignment_id', $assignment->id)
            ->when($term, fn($q) => $q->where('term_id', $term->id))
            ->withAvg('marks', 'score')
            ->withCount('marks')
            ->orderByDesc('activity_date')
            ->get();

        $students = $this->rosterFor($assignment)->load('schoolClass');
        $markRows = ActivityMark::with('classActivity')
            ->whereIn('class_activity_id', $activities->pluck('id'))
            ->get()->groupBy('student_id');
        $students = $students->map(function ($student) use ($markRows) {
            $marks = $markRows->get($student->id, collect());
            $weight = $marks->sum(fn ($mark) => (float) $mark->classActivity->weight);
            $student->ca_mark_count = $marks->count();
            $student->ca_average = $weight > 0
                ? round($marks->sum(fn ($mark) => ($mark->score / $mark->classActivity->max_score) * 100 * $mark->classActivity->weight) / $weight, 1)
                : 0.0;
            return $student;
        });

        $activityChart = $activities->sortBy('activity_date')->values()->map(fn ($activity) => [
            'label' => $activity->activity_date->format('d M'),
            'average' => $activity->average_percent,
        ]);
        $studentChart = $students->filter(fn ($student) => $student->ca_mark_count > 0)->sortByDesc('ca_average')->take(20)->values()->map(fn ($student) => [
            'label' => $student->surname . ', ' . $student->name,
            'average' => $student->ca_average,
        ]);

        return view('teachers.activities.history', compact('assignment', 'activities', 'students', 'term', 'activityChart', 'studentChart'));
    }

    /**
     * TEACHER/ADMIN: Delete an activity and its associated marks.
     */
    public function destroy($id)
    {
        $activity = ClassActivity::with('subjectAssignment')->findOrFail($id);
        $this->authorizeAssignment($activity->subjectAssignment);

        DB::beginTransaction();
        try {
            ActivityMark::where('class_activity_id', $activity->id)->delete();
            $activity->delete();
            DB::commit();

            return back()->with('success', 'Activity deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Error deleting activity: ' . $e->getMessage());
        }
    }

    /**
     * ADMIN: School-wide overview of all logged continuous assessment activities.
     */
    public function adminIndex(Request $request)
    {
        $terms = Term::orderBy('id', 'desc')->get();
        $activeTerm = Term::where('is_current', 1)->first() ?? $terms->first();
        $selectedTerm = $request->filled('term_id') ? Term::find($request->term_id) : $activeTerm;
        $selectedClass = $request->filled('class_id') ? SchoolClass::with('gradeLevel')->find($request->class_id) : null;
        $selectedGrade = $request->filled('grade_level_id') ? GradeLevel::find($request->grade_level_id) : null;
        $visibleMarks = function ($query) use ($selectedClass, $selectedGrade) {
            $query->when($selectedClass, fn($marks) => $marks->whereHas('student', fn($student) => $student->where('class_id', $selectedClass->id)))
                ->when($selectedGrade, fn($marks) => $marks->whereHas('student.schoolClass', fn($class) => $class->where('grade_level_id', $selectedGrade->id)));
        };

        $activities = ClassActivity::with(['subjectAssignment.subject', 'subjectAssignment.schoolClass', 'subjectAssignment.gradeClasses', 'subjectAssignment.teacher', 'creator'])
            ->withAvg(['marks as visible_marks_avg_score' => $visibleMarks], 'score')
            ->withCount(['marks as visible_marks_count' => $visibleMarks])
            ->when($selectedTerm, fn($q) => $q->where('term_id', $selectedTerm->id))
            ->when($selectedClass, fn($q) => $q->where(function ($scope) use ($selectedClass) {
                $scope->whereHas('subjectAssignment', fn($assignment) => $assignment->where('class_id', $selectedClass->id)
                    ->orWhere(fn($grade) => $grade->whereNull('class_id')->where('grade', $selectedClass->gradeLevel?->name)))
                    ->orWhereHas('marks.student', fn($student) => $student->where('class_id', $selectedClass->id));
            }))
            ->when($selectedGrade, fn($q) => $q->where(function ($scope) use ($selectedGrade) {
                $scope->whereHas('subjectAssignment', fn($assignment) => $assignment->where('grade', $selectedGrade->name))
                    ->orWhereHas('subjectAssignment.schoolClass', fn($class) => $class->where('grade_level_id', $selectedGrade->id))
                    ->orWhereHas('marks.student.schoolClass', fn($class) => $class->where('grade_level_id', $selectedGrade->id));
            }))
            ->when($request->filled('subject_id'), fn($q) => $q->whereHas('subjectAssignment', fn($qq) => $qq->where('subject_id', $request->subject_id)))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->orderByDesc('activity_date')
            ->paginate(25)
            ->withQueryString();

        $activities->getCollection()->each(function ($activity) {
            $averageScore = (float) ($activity->visible_marks_avg_score ?? 0);
            $activity->visible_average_percent = $activity->max_score
                ? round(($averageScore / $activity->max_score) * 100, 1)
                : 0.0;
        });

        $classes = SchoolClass::with('gradeLevel')->orderBy('grade_level_id')->orderBy('class_name')->get();
        $gradeLevels = GradeLevel::orderBy('level_order')->get();
        $subjects = Subject::orderBy('subject_name')->get();

        return view('activities.index', compact('activities', 'terms', 'selectedTerm', 'selectedClass', 'selectedGrade', 'classes', 'gradeLevels', 'subjects'));
    }

    public function adminShow($id, Request $request)
    {
        $request->validate(['grade_level_id' => 'nullable|exists:grade_levels,id', 'class_id' => 'nullable|exists:school_classes,id']);
        $activity = ClassActivity::with(['subjectAssignment.subject', 'subjectAssignment.schoolClass', 'subjectAssignment.gradeClasses', 'term'])
            ->withAvg('marks', 'score')->findOrFail($id);
        $marks = ActivityMark::with('student.schoolClass.gradeLevel')->where('class_activity_id', $activity->id)
            ->when($request->filled('class_id'), fn($q) => $q->whereHas('student', fn($student) => $student->where('class_id', $request->class_id)))
            ->when($request->filled('grade_level_id'), fn($q) => $q->whereHas('student.schoolClass', fn($class) => $class->where('grade_level_id', $request->grade_level_id)))
            ->get()->sortBy(fn ($mark) => ($mark->student->schoolClass->class_name ?? '') . ($mark->student->surname ?? ''))->values();
        $visibleAverage = round((float) ($marks->avg(fn ($mark) => $activity->max_score ? ($mark->score / $activity->max_score) * 100 : 0) ?? 0), 1);
        $gradeLevels = GradeLevel::orderBy('level_order')->get();
        $classes = SchoolClass::with('gradeLevel')->orderBy('grade_level_id')->orderBy('class_name')->get();
        return view('activities.show', compact('activity', 'marks', 'gradeLevels', 'classes', 'visibleAverage'));
    }

    public function downloadTemplate($id)
    {
        $activity = ClassActivity::with('subjectAssignment')->findOrFail($id);
        $this->authorizeAssignment($activity->subjectAssignment);
        $students = $this->rosterFor($activity->subjectAssignment);
        $rows = [['student_id', 'student_number', 'surname', 'first_name', 'score', 'comment']];
        foreach ($students as $student) $rows[] = [$student->id, $student->student_number, $student->surname, $student->name, '', ''];
        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            $sheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet->getActiveSheet()->fromArray($rows, null, 'A1');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sheet);
            $path = tempnam(sys_get_temp_dir(), 'ca-template-');
            $writer->save($path);
            return response()->download($path, 'activity-' . $activity->id . '-marks-template.xlsx')->deleteFileAfterSend(true);
        }
        if (class_exists('ZipArchive')) {
            return response()->streamDownload(function () use ($rows) {
                $zip = new \ZipArchive(); $temp = tempnam(sys_get_temp_dir(), 'ca-xlsx-');
                $zip->open($temp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
                $zip->addFromString('_rels/.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
                $zip->addFromString('xl/workbook.xml', '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Marks" sheetId="1" r:id="rId1"/></sheets></workbook>');
                $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
                $xml = '<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
                foreach ($rows as $r => $row) { $xml .= '<row r="' . ($r + 1) . '">'; foreach ($row as $c => $value) { $ref = $this->excelColumnName($c + 1) . ($r + 1); $xml .= '<c r="' . $ref . '" t="inlineStr"><is><t>' . htmlspecialchars((string) ($value ?? ''), ENT_XML1) . '</t></is></c>'; } $xml .= '</row>'; }
                $zip->addFromString('xl/worksheets/sheet1.xml', $xml . '</sheetData></worksheet>'); $zip->close(); readfile($temp); unlink($temp);
            }, 'activity-' . $activity->id . '-marks-template.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        }
        $content = implode("\r\n", array_map(fn ($row) => implode(',', array_map(fn ($cell) => '"' . str_replace('"', '""', (string) $cell) . '"', $row)), $rows));
        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="activity-' . $activity->id . '-marks-template.csv"',
        ]);
    }

    public function importMarks(Request $request)
    {
        $data = $request->validate(['activity_id' => 'required|exists:class_activities,id', 'file' => 'required|file|mimes:csv,txt,xlsx|max:5120']);
        $activity = ClassActivity::with('subjectAssignment')->findOrFail($data['activity_id']);
        $this->authorizeAssignment($activity->subjectAssignment);
        $path = $request->file('file')->getRealPath();
        if (strtolower($request->file('file')->getClientOriginalExtension()) === 'xlsx') {
            if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                $rows = \PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet()->toArray(null, true, true, false);
            } elseif (class_exists('ZipArchive') && function_exists('simplexml_load_string')) {
                $rows = $this->readSimpleXlsxRows($path);
            } else {
                return back()->withErrors(['file' => 'This server cannot read .xlsx files. Save the workbook as CSV and import it instead.']);
            }
        } else {
            $handle = fopen($path, 'r'); $rows = [];
            while (($row = fgetcsv($handle)) !== false) $rows[] = $row;
            fclose($handle);
        }
        $header = array_map(fn ($v) => strtolower(trim(ltrim((string) $v, "\xEF\xBB\xBF"))), array_shift($rows) ?? []);
        $idColumn = array_search('student_id', $header, true);
        $numberColumn = array_search('student_number', $header, true);
        $scoreColumn = array_search('score', $header, true);
        $commentColumn = array_search('comment', $header, true);
        if ($scoreColumn === false || ($idColumn === false && $numberColumn === false)) return back()->withErrors(['file' => 'Include student_id or student_number and score columns.']);

        $roster = $this->rosterFor($activity->subjectAssignment);
        $byId = $roster->keyBy(fn ($student) => (string) $student->id);
        $byNumber = $roster->keyBy(fn ($student) => (string) $student->student_number);
        $imports = []; $errors = [];
        foreach ($rows as $offset => $row) {
            $id = $idColumn !== false ? trim((string) ($row[$idColumn] ?? '')) : '';
            $number = $numberColumn !== false ? trim((string) ($row[$numberColumn] ?? '')) : '';
            $student = ($id !== '' ? $byId->get($id) : null) ?? ($number !== '' ? $byNumber->get($number) : null);
            $score = trim((string) ($row[$scoreColumn] ?? ''));
            if ($score === '') continue;
            $line = $offset + 2;
            if (!$student) { $errors[] = "Row {$line}: student is not active in the assigned class."; continue; }
            if (!is_numeric($score) || (float) $score < 0 || (float) $score > $activity->max_score) { $errors[] = "Row {$line}: score must be between 0 and {$activity->max_score}."; continue; }
            $imports[] = ['student_id' => $student->id, 'score' => (float) $score, 'comment' => $commentColumn !== false ? trim((string) ($row[$commentColumn] ?? '')) : null];
        }
        if ($errors) return back()->withErrors(['file' => implode(' ', $errors)]);
        DB::transaction(function () use ($imports, $activity) {
            foreach ($imports as $mark) ActivityMark::updateOrCreate(['class_activity_id' => $activity->id, 'student_id' => $mark['student_id']], ['score' => $mark['score'], 'comment' => $mark['comment']]);
        });
        return back()->with('success', count($imports) . ' marks imported successfully.');
    }

    private function rosterFor(SubjectAssignment $assignment)
    {
        $query = Student::where('status', 'active')->with('schoolClass');
        if ($assignment->class_id) return $query->where('class_id', $assignment->class_id)->orderBy('surname')->get();
        $classIds = $assignment->gradeClasses->pluck('id');
        return $query->whereIn('class_id', $classIds)->with('schoolClass')->orderBy('class_id')->orderBy('surname')->get();
    }

    private function readSimpleXlsxRows(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return [];
        $shared = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false && ($strings = @simplexml_load_string($xml))) {
            foreach ($strings->si as $item) {
                $text = (string) ($item->t ?? '');
                if ($text === '') foreach ($item->r ?? [] as $run) $text .= (string) ($run->t ?? '');
                $shared[] = $text;
            }
        }
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml'); $zip->close();
        if ($sheetXml === false || !($sheet = @simplexml_load_string($sheetXml))) return [];
        $rows = [];
        foreach ($sheet->sheetData->row ?? [] as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                preg_match('/^([A-Z]+)/', (string) $cell['r'], $match); $column = 0;
                foreach (str_split($match[1] ?? '') as $letter) $column = $column * 26 + ord($letter) - 64;
                $column--; $value = (string) ($cell->v ?? '');
                if ((string) $cell['t'] === 's') $value = $shared[(int) $value] ?? '';
                elseif ((string) $cell['t'] === 'inlineStr') $value = (string) ($cell->is->t ?? '');
                $values[$column] = $value;
            }
            if ($values) $rows[] = array_replace(array_fill(0, max(array_keys($values)) + 1, ''), $values);
        }
        return $rows;
    }

    private function excelColumnName(int $column): string
    {
        $name = '';
        while ($column > 0) { $column--; $name = chr(65 + ($column % 26)) . $name; $column = intdiv($column, 26); }
        return $name;
    }

    /**
     * Authorization check: verifies if the logged-in user owns the assignment or is an admin.
     */
    private function authorizeAssignment(SubjectAssignment $assignment): void
    {
        $user = Auth::user();

        $isAdmin = ($user->role === 'admin') || (method_exists($user, 'hasRole') && $user->hasRole('admin'));

        if ($assignment->teacher_id !== $user->id && !$isAdmin) {
            abort(403, 'Unauthorized access to this class activity.');
        }
    }
}
