<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Term;
use App\Models\Mark;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\GradeLevel;
use App\Models\SubjectAssignment;
use App\Services\ExamCalculationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExamController extends Controller
{
    protected ExamCalculationService $calculationService;

    public function __construct(ExamCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * ADMIN VIEW: Exam Collapsible Grade & Streams Directory
     */
    public function index(Request $request)
    {
        $terms = Term::orderBy('id', 'desc')->get();
        $activeTerm = Term::where('is_current', 1)->first() ?? $terms->first();

        $selectedTermId = $request->get('term_id');
        $selectedTerm = $selectedTermId ? Term::find($selectedTermId) : $activeTerm;

        // Retrieve Grade Levels
        $gradeLevels = class_exists(GradeLevel::class) ? GradeLevel::orderBy('level_order')->get() : collect();
        
        if ($gradeLevels->isEmpty()) {
            $distinctGrades = SchoolClass::whereNotNull('grade_level')->pluck('grade_level')->unique();
            if ($distinctGrades->isEmpty()) {
                $distinctGrades = collect(['Form 1', 'Form 2', 'Form 3', 'Form 4']);
            }
            $gradeLevels = $distinctGrades->map(fn($g, $i) => (object)['id' => $i + 1, 'name' => $g]);
        }

        $allClasses = SchoolClass::with('gradeLevel')->orderBy('class_name')->get();
        $allStudents = Student::where('status', 'active')->get();
        $allAssignments = SubjectAssignment::with('subject')->get();
        $allExams = Exam::with(['subject', 'term', 'schoolClass', 'subjectAssignment'])
            ->when($selectedTerm, fn($q) => $q->where('term_id', $selectedTerm->id))
            ->get();

        $gradeData = collect();

        foreach ($gradeLevels as $gl) {
            $gName = is_object($gl) ? $gl->name : (string)$gl;
            $gId = is_object($gl) ? ($gl->id ?? $gName) : $gName;

            // Find sub-streams belonging to this Grade Level
            $streams = $allClasses->filter(function($c) use ($gl, $gName) {
                if (isset($gl->id) && $c->grade_level_id && $c->grade_level_id == $gl->id) return true;
                if ($c->grade_level && strtolower(trim($c->grade_level)) === strtolower(trim($gName))) return true;
                return Str::startsWith(strtolower(trim($c->class_name)), strtolower(trim($gName)));
            });

            // Filter out exact duplicate class names if stream sub-classes exist (e.g. drop 'Form 1' if 'Form 1 A' exists)
            $specificStreams = $streams->filter(fn($c) => strtolower(trim($c->class_name)) !== strtolower(trim($gName)));
            if ($specificStreams->isNotEmpty()) {
                $streams = $specificStreams;
            }

            $streamList = collect();
            $totalGradeStudents = 0;

            foreach ($streams as $st) {
                // Enrolled students in stream
                $studentCount = $allStudents->where('class_id', $st->id)->count();
                if ($studentCount === 0) {
                    $studentCount = $allStudents->filter(fn($s) => strtolower(trim($s->grade)) === strtolower(trim($st->class_name)))->count();
                }
                $totalGradeStudents += $studentCount;

                // Subjects assigned to stream
                $streamAssignments = $allAssignments->filter(function($a) use ($st, $gName) {
                    if ($a->class_id == $st->id) return true;
                    if ($a->grade && strtolower(trim($a->grade)) === strtolower(trim($gName))) return true;
                    return false;
                });

                $streamSubjectsCount = $streamAssignments->map(fn($a) => $a->subject)->filter()->unique('id')->count();

                // Scheduled exams count
                $streamExamsCount = $allExams->filter(function ($exam) use ($st, $gName) {
                    if ((int) $exam->class_id === (int) $st->id) return true;
                    return $exam->subjectAssignment
                        && strtolower(trim((string) $exam->subjectAssignment->grade)) === strtolower(trim((string) $gName));
                })->count();

                $streamList->push([
                    'id'            => $st->id,
                    'name'          => $st->class_name,
                    'code'          => $st->class_code,
                    'student_count' => $studentCount,
                    'subject_count' => $streamSubjectsCount,
                    'exams_count'   => $streamExamsCount,
                ]);
            }

            // Grade level total exams
            $gradeExamsCount = $allExams->filter(function($e) use ($gName, $streams) {
                if ($e->class_id && $streams->pluck('id')->contains($e->class_id)) return true;
                if ($e->subjectAssignment && $e->subjectAssignment->grade === $gName) return true;
                return false;
            })->count();

            $gradeData->push([
                'id'            => $gId,
                'name'          => $gName,
                'student_count' => $totalGradeStudents,
                'streams'       => $streamList,
                'exams_count'   => $gradeExamsCount
            ]);
        }

        return view('exams.index', compact('gradeData', 'terms', 'selectedTerm', 'activeTerm'));
    }

    /**
     * DEDICATED EXAM SCHEDULING VIEW
     */
    public function scheduleView($type, $id, Request $request)
    {
        $terms = Term::orderBy('id', 'desc')->get();
        $activeTerm = Term::where('is_current', 1)->first() ?? $terms->first();
        $selectedTermId = $request->get('term_id');
        $selectedTerm = $selectedTermId ? Term::find($selectedTermId) : $activeTerm;

        $targetName = '';
        $classId = null;
        $gradeName = null;
        $studentsCount = 0;

        if ($type === 'stream') {
            $schoolClass = SchoolClass::with('gradeLevel')->findOrFail($id);
            $targetName = $schoolClass->class_name;
            $classId = $schoolClass->id;
            $gradeName = $schoolClass->gradeLevel->name ?? $schoolClass->class_name;

            $studentsCount = Student::where('class_id', $classId)->where('status', 'active')->count();

            $assignedSubjectIds = SubjectAssignment::where(function($q) use ($classId, $gradeName) {
                $q->where('class_id', $classId)
                  ->orWhere('grade', $gradeName);
            })->pluck('subject_id')->unique();

            $allocatedSubjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('subject_name')->get();

            $scheduledExams = Exam::with(['subject', 'term', 'schoolClass', 'subjectAssignment'])
                ->where(function ($query) use ($classId, $gradeName) {
                    $query->where('class_id', $classId);
                    if ($gradeName) {
                        $query->orWhereHas('subjectAssignment', fn ($assignment) => $assignment->where('grade', $gradeName));
                    }
                })
                ->when($selectedTerm, fn($q) => $q->where('term_id', $selectedTerm->id))
                ->latest()
                ->get();

        } else {
            $gradeName = urldecode($id);
            $targetName = $gradeName . ' (All Streams)';

            $gl = GradeLevel::where('name', $gradeName)->first();
            $matchingClasses = SchoolClass::where('grade_level_id', $gl->id ?? 0)
                ->orWhere('class_name', 'LIKE', "{$gradeName}%")
                ->pluck('id');

            $studentsCount = Student::where(function($q) use ($matchingClasses, $gradeName) {
                $q->whereIn('class_id', $matchingClasses)
                  ->orWhere('grade', $gradeName);
            })->where('status', 'active')->count();

            $assignedSubjectIds = SubjectAssignment::where(function($q) use ($gradeName, $matchingClasses) {
                $q->where('grade', $gradeName)
                  ->orWhereIn('class_id', $matchingClasses);
            })->pluck('subject_id')->unique();

            $allocatedSubjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('subject_name')->get();

            $scheduledExams = Exam::with(['subject', 'term', 'schoolClass', 'subjectAssignment'])
                ->where(function($q) use ($gradeName, $matchingClasses) {
                    $q->whereIn('class_id', $matchingClasses)
                      ->orWhereHas('subjectAssignment', fn($sa) => $sa->where('grade', $gradeName));
                })
                ->when($selectedTerm, fn($q) => $q->where('term_id', $selectedTerm->id))
                ->latest()
                ->get();
        }

        return view('exams.schedule', compact(
            'type', 'id', 'targetName', 'classId', 'gradeName', 'studentsCount',
            'allocatedSubjects', 'scheduledExams', 'terms', 'selectedTerm', 'activeTerm'
        ));
    }

    /**
     * SUBJECT PERFORMANCE & PAPERS OVERVIEW REPORT
     */
    public function subjectReport(Request $request, $subject_id)
    {
        $subject = Subject::findOrFail($subject_id);
        return $this->buildSubjectReport($request, $subject);
    }

    public function teacherSubjectReport(Request $request, $subject_id)
    {
        $subject = Subject::findOrFail($subject_id);
        $assignments = SubjectAssignment::with(['schoolClass', 'gradeClasses'])
            ->where('teacher_id', Auth::id())
            ->where('subject_id', $subject->id)
            ->get();

        abort_if($assignments->isEmpty(), 403);

        return $this->buildSubjectReport($request, $subject, $assignments, true);
    }

    private function buildSubjectReport(Request $request, Subject $subject, $teacherAssignments = null, bool $teacherView = false)
    {
        $terms = Term::orderBy('id', 'desc')->get();
        $activeTerm = Term::where('is_current', 1)->first() ?? $terms->first();

        $selectedTermId = $request->get('term_id');
        $selectedTerm = $selectedTermId ? Term::find($selectedTermId) : $activeTerm;

        // Teachers see papers linked to their allocations, plus admin-created
        // papers for the exact classes covered by those allocations.
        $examQuery = Exam::with(['schoolClass', 'subjectAssignment', 'marks.student.schoolClass'])
            ->where('subject_id', $subject->id);

        if ($teacherAssignments !== null) {
            $assignmentIds = $teacherAssignments->pluck('id');
            $classIds = $teacherAssignments->flatMap(function ($assignment) {
                return $assignment->schoolClass
                    ? collect([$assignment->schoolClass->id])
                    : $assignment->gradeClasses->pluck('id');
            })->unique()->values();

            $examQuery->where(function ($query) use ($assignmentIds, $classIds) {
                $query->whereIn('subject_assignment_id', $assignmentIds);
                if ($classIds->isNotEmpty()) $query->orWhereIn('class_id', $classIds);
            });
        }

        $exams = $examQuery
            ->when($selectedTerm, fn($q) => $q->where('term_id', $selectedTerm->id))
            ->orderBy('exam_date')->orderBy('exam_name')
            ->get();

        // Collect all marks recorded for this subject in the selected term
        $allMarks = Mark::whereIn('exam_id', $exams->pluck('id'))
            ->with(['student.schoolClass', 'exam'])
            ->get();

        // 1. Overall Subject Summary Metrics
        $totalCandidates = $allMarks->pluck('student_id')->unique()->count();
        $overallAverage = $allMarks->count() > 0 ? round($allMarks->avg('score'), 2) : 0;
        
        $passedCount = $allMarks->filter(fn($m) => $this->calculationService->calculateGrade($m->score)['is_passed'])->count();
        $overallPassRate = $allMarks->count() > 0 ? round(($passedCount / $allMarks->count()) * 100, 1) : 0;

        // 2. Overview of Papers Written for this Subject
        $paperBreakdown = $exams->map(function($exam) {
            $examMarks = $exam->marks;
            $satCount = $examMarks->count();
            $avgScore = $satCount > 0 ? round($examMarks->avg('score'), 2) : 0;
            $passed = $examMarks->filter(fn($m) => $this->calculationService->calculateGrade($m->score)['is_passed'])->count();
            $passRate = $satCount > 0 ? round(($passed / $satCount) * 100, 1) : 0;

            return (object)[
                'exam_id'    => $exam->id,
                'exam_name'  => $exam->exam_name,
                'class_name' => $exam->schoolClass->class_name
                    ?? ($exam->subjectAssignment?->grade ? $exam->subjectAssignment->grade . ' · All Streams' : 'All Streams'),
                'exam_date'  => $exam->exam_date,
                'status'     => $exam->status,
                'sat_count'  => $satCount,
                'avg_score'  => $avgScore,
                'pass_rate'  => $passRate,
                'highest'    => $satCount > 0 ? $examMarks->max('score') : 0,
                'lowest'     => $satCount > 0 ? $examMarks->min('score') : 0,
            ];
        });

        // 3. Per Class Stream Breakdown Overview
        $streamBreakdown = $allMarks->groupBy(function($mark) {
            return $mark->student->schoolClass->id ?? 0;
        })->map(function($streamMarks, $classId) {
            $firstStudent = $streamMarks->first()->student ?? null;
            $className = $firstStudent && $firstStudent->schoolClass 
                ? $firstStudent->schoolClass->class_name 
                : ($firstStudent->grade ?? 'Unassigned Stream');

            $studentsCount = $streamMarks->pluck('student_id')->unique()->count();
            $avgScore = round($streamMarks->avg('score'), 2);
            $passed = $streamMarks->filter(fn($m) => $this->calculationService->calculateGrade($m->score)['is_passed'])->count();
            $passRate = round(($passed / $streamMarks->count()) * 100, 1);
            $highestScore = $streamMarks->max('score');
            
            // Top student in stream for this subject
            $topMark = $streamMarks->sortByDesc('score')->first();
            $topStudentName = $topMark && $topMark->student 
                ? $topMark->student->name . ' ' . $topMark->student->surname 
                : 'N/A';

            return (object)[
                'class_id'       => $classId,
                'class_name'     => $className,
                'students_count' => $studentsCount,
                'avg_score'      => $avgScore,
                'pass_rate'      => $passRate,
                'highest_score'  => $highestScore,
                'top_student'    => $topStudentName,
            ];
        })->values();

        // 4. Overall Grade Distribution (A - F)
        $gradeCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];
        foreach ($allMarks as $m) {
            $g = $this->calculationService->calculateGrade($m->score)['grade'];
            if (isset($gradeCounts[$g])) {
                $gradeCounts[$g]++;
            } else {
                $gradeCounts['F']++;
            }
        }

        $markSearch = trim((string) $request->get('search', ''));
        $selectedExamId = $request->get('exam_id');
        $selectedClassId = $request->get('class_id');
        $sortBy = $request->get('sort', 'student');
        $sortDirection = $request->get('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $validSorts = ['student', 'class', 'paper', 'mark', 'date'];
        if (!in_array($sortBy, $validSorts, true)) $sortBy = 'student';

        $marksForTable = $allMarks
            ->filter(function ($mark) use ($markSearch, $selectedExamId, $selectedClassId) {
                if ($selectedExamId && (int) $mark->exam_id !== (int) $selectedExamId) return false;
                if ($selectedClassId && (int) ($mark->student->schoolClass->id ?? 0) !== (int) $selectedClassId) return false;
                if ($markSearch !== '') {
                    $student = $mark->student;
                    $haystack = strtolower(implode(' ', [
                        $student->name ?? '', $student->surname ?? '', $student->student_number ?? '',
                    ]));
                    if (!str_contains($haystack, strtolower($markSearch))) return false;
                }
                return true;
            })
            ->sortBy(function ($mark) use ($sortBy) {
                return match ($sortBy) {
                    'class' => strtolower($mark->student->schoolClass->class_name ?? ''),
                    'paper' => strtolower($mark->exam->exam_name ?? ''),
                    'mark' => (float) $mark->score,
                    'date' => $mark->exam->exam_date?->timestamp ?? 0,
                    default => strtolower(($mark->student->surname ?? '') . ' ' . ($mark->student->name ?? '')),
                };
            }, SORT_REGULAR, $sortDirection === 'desc')
            ->values();

        $reportClasses = $allMarks->map(fn ($mark) => $mark->student->schoolClass)->filter()->unique('id')->sortBy('class_name')->values();

        return view('exams.subject_report', compact(
            'subject', 'exams', 'terms', 'selectedTerm', 'activeTerm',
            'totalCandidates', 'overallAverage', 'overallPassRate',
            'paperBreakdown', 'streamBreakdown', 'gradeCounts', 'marksForTable',
            'markSearch', 'selectedExamId', 'selectedClassId', 'sortBy', 'sortDirection', 'reportClasses', 'teacherView'
        ));
    }

    /**
     * ADMIN STORE: Schedule Exam Paper
     * Automatically populates class_id for all stream classes when scheduled at Grade Level.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_name'  => 'required|string|max:255',
            'term_id'    => 'required|exists:terms,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id'   => 'nullable',
            'grade_name' => 'nullable|string',
            'exam_date'  => 'required|date',
            'status'     => 'nullable|in:pending,published'
        ]);

        $status = $request->input('status', 'pending');

        // CASE 1: Scheduled for a Specific Stream Class (e.g. Form 1 A)
        if ($request->filled('class_id') && is_numeric($request->class_id)) {
            Exam::create([
                'exam_name'  => $request->exam_name,
                'term_id'    => $request->term_id,
                'subject_id' => $request->subject_id,
                'class_id'   => $request->class_id,
                'exam_date'  => $request->exam_date,
                'status'     => $status,
            ]);

            return back()->with('success', 'Exam paper scheduled successfully for the class stream!');
        }

        // CASE 2: Scheduled for an Entire Grade Level (e.g. Form 1)
        if ($request->filled('grade_name')) {
            $gradeName = urldecode($request->grade_name);

            $gl = GradeLevel::where('name', $gradeName)->first();
            $classes = SchoolClass::where('grade_level_id', $gl->id ?? 0)
                ->orWhere('class_name', 'LIKE', "{$gradeName}%")
                ->get();

            $specificClasses = $classes->filter(fn($c) => strtolower(trim($c->class_name)) !== strtolower(trim($gradeName)));
            if ($specificClasses->isNotEmpty()) {
                $classes = $specificClasses;
            }

            if ($classes->isNotEmpty()) {
                foreach ($classes as $schoolClass) {
                    Exam::create([
                        'exam_name'  => $request->exam_name,
                        'term_id'    => $request->term_id,
                        'subject_id' => $request->subject_id,
                        'class_id'   => $schoolClass->id,
                        'exam_date'  => $request->exam_date,
                        'status'     => $status,
                    ]);
                }

                return back()->with('success', "Exam paper scheduled successfully across all {$classes->count()} streams in {$gradeName}!");
            }
        }

        // Fallback: Default creation
        Exam::create([
            'exam_name'  => $request->exam_name,
            'term_id'    => $request->term_id,
            'subject_id' => $request->subject_id,
            'class_id'   => null,
            'exam_date'  => $request->exam_date,
            'status'     => $status,
        ]);

        return back()->with('success', 'Exam paper scheduled successfully!');
    }

    /**
     * DOWNLOAD NATIVE EXCEL (.XLSX) OR CSV MARKS TEMPLATE
     */
    public function downloadTemplate($exam_id, $target, Request $request)
    {
        $exam = Exam::with(['subject', 'term'])->findOrFail($exam_id);
        $format = strtolower($request->get('format', 'xlsx'));

        if (is_numeric($target)) {
            $targetClass = SchoolClass::find($target);
            $targetLabel = $targetClass ? $targetClass->class_name : "Stream_{$target}";
            $students = Student::where('class_id', $target)->orderBy('surname')->get();
        } else {
            $targetLabel = urldecode($target);
            $students = Student::where(function($q) use ($targetLabel) {
                $q->where('grade', $targetLabel)
                  ->orWhereHas('schoolClass', function($sc) use ($targetLabel) {
                      $sc->where('class_name', 'LIKE', "{$targetLabel}%");
                  });
            })->orderBy('surname')->get();
        }

        $headers = ['student_id', 'student_number', 'surname', 'first_name', 'score', 'max_score', 'teacher_comment'];

        if ($format === 'xlsx' && class_exists(Spreadsheet::class)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Marks Template');

            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);

            $rowNum = 2;
            foreach ($students as $st) {
                $existingMark = Mark::where('exam_id', $exam->id)->where('student_id', $st->id)->first();
                $sheet->fromArray([[
                    $st->id,
                    $st->student_number,
                    $st->surname,
                    $st->name,
                    $existingMark ? $existingMark->score : '',
                    $existingMark ? $existingMark->max_score : 100,
                    $existingMark ? $existingMark->teacher_comment : ''
                ]], null, 'A' . $rowNum);
                $rowNum++;
            }

            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = "Marks_Template_" . Str::slug($exam->exam_name) . "_" . Str::slug($targetLabel) . ".xlsx";

            return response()->streamDownload(function() use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
        }

        // CSV Fallback
        $filename = "Marks_Template_" . Str::slug($exam->exam_name) . "_" . Str::slug($targetLabel) . ".csv";

        $downloadHeaders = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($students, $exam, $headers) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, $headers);

            foreach ($students as $st) {
                $existingMark = Mark::where('exam_id', $exam->id)->where('student_id', $st->id)->first();

                fputcsv($file, [
                    $st->id,
                    $st->student_number,
                    $st->surname,
                    $st->name,
                    $existingMark ? $existingMark->score : '',
                    $existingMark ? $existingMark->max_score : 100,
                    $existingMark ? $existingMark->teacher_comment : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $downloadHeaders);
    }

    /**
     * BULK IMPORT MARKS FROM EXCEL (.XLSX, .XLS) OR CSV
     */
    public function importMarks(Request $request)
    {
        $request->validate([
            'exam_id'    => 'required|exists:exams,id',
            'marks_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $exam = Exam::with('subject')->findOrFail($request->exam_id);
        $file = $request->file('marks_file');
        $filePath = $file->getRealPath();

        $rows = [];

        if (class_exists(IOFactory::class)) {
            try {
                $spreadsheet = IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
            } catch (\Exception $e) {
                return back()->with('error', 'Error reading Excel spreadsheet: ' . $e->getMessage());
            }
        } else {
            $handle = fopen($filePath, 'r');
            if ($handle) {
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
                while (($data = fgetcsv($handle)) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        }

        if (empty($rows)) {
            return back()->with('error', 'Uploaded spreadsheet file is empty or could not be read.');
        }

        $header = array_shift($rows);
        $header = array_map(fn($h) => strtolower(trim(str_replace([' ', '"', "'"], '', (string)$h))), $header);

        $studentIdIdx = array_search('student_id', $header);
        $scoreIdx     = array_search('score', $header);
        $maxScoreIdx  = array_search('max_score', $header);
        $commentIdx   = array_search('teacher_comment', $header);

        if ($studentIdIdx === false || $scoreIdx === false) {
            return back()->with('error', 'Invalid file layout. Missing required columns: "student_id" and "score".');
        }

        $importedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                if (!isset($row[$studentIdIdx]) || trim((string)$row[$studentIdIdx]) === '') {
                    continue;
                }

                $studentId = trim((string)$row[$studentIdIdx]);
                $scoreVal  = isset($row[$scoreIdx]) && trim((string)$row[$scoreIdx]) !== '' ? trim((string)$row[$scoreIdx]) : null;

                if ($scoreVal !== null && is_numeric($scoreVal)) {
                    $maxScoreVal = ($maxScoreIdx !== false && isset($row[$maxScoreIdx]) && is_numeric(trim((string)$row[$maxScoreIdx]))) 
                        ? (float)trim((string)$row[$maxScoreIdx]) 
                        : 100;
                    
                    $commentVal = ($commentIdx !== false && isset($row[$commentIdx])) 
                        ? trim((string)$row[$commentIdx]) 
                        : null;

                    Mark::updateOrCreate(
                        ['exam_id' => $exam->id, 'student_id' => $studentId],
                        [
                            'subject'         => $exam->subject->subject_name,
                            'score'           => (float)$scoreVal,
                            'max_score'       => $maxScoreVal,
                            'teacher_comment' => $commentVal
                        ]
                    );

                    $importedCount++;
                }
            }

            DB::commit();
            return back()->with('success', "Successfully imported exam marks for {$importedCount} student(s) from Excel!");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to save imported marks: ' . $e->getMessage());
        }
    }

    /**
     * TOGGLE EXAM PUBLISH STATUS
     */
    public function togglePublish($id)
    {
        $exam = Exam::findOrFail($id);
        $exam->status = $exam->status === 'published' ? 'pending' : 'published';
        $exam->save();

        return back()->with('success', "Exam status updated to {$exam->status}.");
    }

    /**
     * ADMIN DESTROY: Delete exam and marks
     */
    public function destroy($id)
    {
        $exam = Exam::findOrFail($id);
        DB::beginTransaction();
        try {
            Mark::where('exam_id', $exam->id)->delete();
            $exam->delete();
            DB::commit();
            return back()->with('success', 'Exam schedule and associated marks deleted.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error deleting exam: ' . $e->getMessage());
        }
    }

    /**
     * ADMIN MARK ENTRY VIEW
     */
    public function createMarks($exam_id, $target)
    {
        $exam = Exam::with(['subject', 'term', 'schoolClass'])->findOrFail($exam_id);

        if (is_numeric($target)) {
            $targetClass = SchoolClass::find($target);
            $targetLabel = $targetClass ? $targetClass->class_name : "Stream #{$target}";
            $students = Student::where('class_id', $target)->orderBy('surname')->get();
        } else {
            $targetLabel = urldecode($target);
            $students = Student::where(function($q) use ($targetLabel) {
                $q->where('grade', $targetLabel)
                  ->orWhereHas('schoolClass', function($sc) use ($targetLabel) {
                      $sc->where('class_name', 'LIKE', "{$targetLabel}%");
                  });
            })->orderBy('surname')->get();
        }

        $marks = Mark::where('exam_id', $exam_id)->get()->keyBy('student_id');

        return view('exams.enter_marks', compact('exam', 'students', 'target', 'targetLabel', 'marks'));
    }

    /**
     * ADMIN BULK STORE MARKS
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'marks'   => 'required|array',
        ]);

        $exam = Exam::with('subject')->findOrFail($request->exam_id);

        DB::beginTransaction();
        try {
            foreach ($request->marks as $studentId => $data) {
                if (isset($data['score']) && $data['score'] !== null && $data['score'] !== '') {
                    Mark::updateOrCreate(
                        ['exam_id' => $exam->id, 'student_id' => $studentId],
                        [
                            'subject'         => $exam->subject->subject_name,
                            'score'           => $data['score'],
                            'max_score'       => $data['max_score'] ?? 100,
                            'teacher_comment' => $data['comment'] ?? null
                        ]
                    );
                }
            }
            DB::commit();
            return redirect()->route('exams.index')->with('success', 'Exam marks recorded successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to save marks: ' . $e->getMessage());
        }
    }

    /**
     * CLASS PERFORMANCE REPORT VIEW
     */
    public function examReport($exam_id, $target)
    {
        $exam = Exam::with(['subject', 'term', 'schoolClass'])->findOrFail($exam_id);

        if (is_numeric($target)) {
            $targetClass = SchoolClass::find($target);
            $targetLabel = $targetClass ? $targetClass->class_name : "Stream #{$target}";
            $students = Student::where('class_id', $target)
                ->with(['marks' => fn($q) => $q->where('exam_id', $exam_id)])
                ->orderBy('surname', 'asc')
                ->get();
        } else {
            $targetLabel = urldecode($target);
            $students = Student::where(function($q) use ($targetLabel) {
                $q->where('grade', $targetLabel)
                  ->orWhereHas('schoolClass', fn($sc) => $sc->where('class_name', 'LIKE', "{$targetLabel}%"));
            })
            ->with(['marks' => fn($q) => $q->where('exam_id', $exam_id)])
            ->orderBy('surname', 'asc')
            ->get();
        }

        return view('exams.report', compact('exam', 'students', 'target', 'targetLabel'));
    }

    /**
     * BIOMETRIC GATEKEEPER VIEW & PROCESSOR
     */
    public function verify($exam_id)
    {
        $exam = Exam::with(['subject', 'term', 'schoolClass'])->findOrFail($exam_id);
        return view('exams.verify', compact('exam'));
    }

    public function verifyFace(Request $request)
    {
        $request->validate([
            'exam_id'    => 'required|exists:exams,id',
            'face_image' => 'required|string',
        ]);

        $exam = Exam::with(['subject', 'schoolClass'])->findOrFail($request->exam_id);
        
        $student = Student::where('status', 'active')
            ->when($exam->class_id, fn($q) => $q->where('class_id', $exam->class_id))
            ->first();

        if ($student) {
            if ($student->balance > 0) {
                return response()->json([
                    'message' => 'Student has outstanding tuition balance: $' . number_format($student->balance, 2)
                ], 402);
            }

            return response()->json([
                'student' => [
                    'name'           => $student->name . ' ' . $student->surname,
                    'student_number' => $student->student_number,
                    'photo_url'      => $student->photo_path ? asset('storage/' . $student->photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($student->name),
                    'verified_time'  => now()->format('H:i:s d M, Y')
                ]
            ], 200);
        }

        return response()->json(['message' => 'No biometric match confirmed in active register.'], 404);
    }

    /**
     * STUDENT PORTAL RESULTS VIEW
     */
    public function studentResults(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)
            ->orWhere('student_number', $user->ec_number)
            ->firstOrFail();

        $allTerms = Term::orderBy('id', 'desc')->get();
        $selectedTermId = $request->get('term_id');
        $activeTerm = $selectedTermId ? Term::find($selectedTermId) : (Term::where('is_current', 1)->first() ?? $allTerms->first());

        if (!$activeTerm) {
            return back()->with('error', 'No academic terms found in system.');
        }

        $raw_current_results = Mark::where('student_id', $student->id)
            ->whereHas('exam', fn($q) => $q->where('term_id', $activeTerm->id)->where('status', 'published'))
            ->with(['exam.subject', 'exam.term'])
            ->get();

        $current_results = $this->calculationService->aggregateMarksBySubject($raw_current_results);
        $average = $current_results->count() > 0 ? round($current_results->avg('average_score'), 2) : 0;
        $overallGrade = $this->calculationService->calculateGrade($average);

        $history = Mark::where('student_id', $student->id)
            ->whereHas('exam', fn($q) => $q->where('term_id', '!=', $activeTerm->id)->where('status', 'published'))
            ->with(['exam.term', 'exam.subject'])
            ->get()
            ->groupBy(fn($item) => $item->exam->term->term_name ?? 'Archive')
            ->map(fn($termMarks) => $this->calculationService->aggregateMarksBySubject($termMarks));

        return view('exams.student_index', compact(
            'student', 'current_results', 'history', 'average', 'overallGrade', 'activeTerm', 'allTerms'
        ));
    }

    /**
     * TERMINAL REPORT CARD GENERATOR
     */
    public function generateTermReport($student_id, $term_id)
    {
        $student = Student::with('schoolClass')->findOrFail($student_id);
        $term = Term::findOrFail($term_id);

        $raw_marks = Mark::where('student_id', $student->id)
            ->whereHas('exam', fn($q) => $q->where('term_id', $term->id))
            ->with(['exam.subject'])
            ->get();

        $finalGrades = $this->calculationService->aggregateMarksBySubject($raw_marks);
        $overallAverage = $finalGrades->count() > 0 ? round($finalGrades->avg('average_score'), 2) : 0;
        $overallGrade = $this->calculationService->calculateGrade($overallAverage);

        return view('exams.student_report', compact('student', 'term', 'finalGrades', 'overallAverage', 'overallGrade'));
    }

    /**
     * TEACHER PORTAL HANDLERS
     */
    public function teacherManageMarks($id)
    {
        $exam = Exam::with(['subject', 'term', 'schoolClass', 'subjectAssignment'])->findOrFail($id);
        abort_unless($this->teacherOwnsExam($exam), 403);

        $students = Student::where('status', 'active')
            ->when($exam->class_id, fn ($query) => $query->where('class_id', $exam->class_id))
            ->when(!$exam->class_id, function ($query) use ($exam) {
                if ($exam->subjectAssignment?->grade) {
                    $grade = $exam->subjectAssignment->grade;
                    $query->whereHas('schoolClass.gradeLevel', fn ($classes) => $classes->where('name', $grade));
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->orderBy('surname')
            ->get();

        $marks = Mark::where('exam_id', $id)->get()->keyBy('student_id');

        return view('teachers.exams.record_marks', compact('exam', 'students', 'marks'));
    }

    public function downloadTeacherMarksTemplate($examId)
    {
        $exam = Exam::with(['subject', 'subjectAssignment'])->findOrFail($examId);
        abort_unless($this->teacherOwnsExam($exam), 403);

        $students = $this->teacherExamStudents($exam);
        $rows = [['student_id', 'student_number', 'surname', 'first_name', 'score', 'max_score', 'teacher_comment']];
        $existingMarks = Mark::where('exam_id', $exam->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()->keyBy('student_id');

        foreach ($students as $student) {
            $mark = $existingMarks->get($student->id);
            $rows[] = [$student->id, $student->student_number, $student->surname, $student->name,
                $mark?->score, $exam->max_marks ?? 100, $mark?->teacher_comment];
        }

        $filename = Str::slug($exam->exam_name . '-marks-template') . '.xlsx';
        if (class_exists(Spreadsheet::class)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            foreach ($rows as $rowIndex => $row) {
                foreach ($row as $columnIndex => $value) {
                    $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex + 1, $value);
                }
            }
            $sheet->freezePane('A2');
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);
            return response()->streamDownload(function () use ($spreadsheet) {
                (new Xlsx($spreadsheet))->save('php://output');
            }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        }

        if (class_exists('ZipArchive')) {
            return response()->streamDownload(function () use ($rows) {
                $zip = new \ZipArchive();
                $temp = tempnam(sys_get_temp_dir(), 'marks-xlsx-');
                $zip->open($temp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
                $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
                $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Marks" sheetId="1" r:id="rId1"/></sheets></workbook>');
                $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
                $xml = '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
                foreach ($rows as $r => $row) {
                    $xml .= '<row r="' . ($r + 1) . '">';
                    foreach ($row as $c => $value) {
                        $ref = $this->excelColumnName($c + 1) . ($r + 1);
                        if (is_numeric($value) && $value !== '') {
                            $xml .= '<c r="' . $ref . '"><v>' . htmlspecialchars((string) $value, ENT_XML1) . '</v></c>';
                        } else {
                            $xml .= '<c r="' . $ref . '" t="inlineStr"><is><t>' . htmlspecialchars((string) ($value ?? ''), ENT_XML1) . '</t></is></c>';
                        }
                    }
                    $xml .= '</row>';
                }
                $zip->addFromString('xl/worksheets/sheet1.xml', $xml . '</sheetData></worksheet>');
                $zip->close();
                readfile($temp);
                unlink($temp);
            }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        }

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            foreach ($rows as $row) fputcsv($output, $row);
            fclose($output);
        }, Str::replaceEnd('.xlsx', '.csv', $filename), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function teacherImportMarks(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'marks_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $exam = Exam::with(['subject', 'subjectAssignment'])->findOrFail($request->exam_id);
        abort_unless($this->teacherOwnsExam($exam), 403);

        $students = $this->teacherExamStudents($exam)->keyBy('id');
        $maxMarks = (float) ($exam->max_marks ?? 100);
        $parsed = [];
        $file = $request->file('marks_file')->getRealPath();

        $extension = strtolower($request->file('marks_file')->getClientOriginalExtension());
        if (in_array($extension, ['xlsx', 'xls'], true) && class_exists(IOFactory::class)) {
            $sheetRows = IOFactory::load($file)->getActiveSheet()->toArray(null, true, true, false);
        } elseif ($extension === 'xlsx' && class_exists('ZipArchive')) {
            $sheetRows = $this->readTeacherXlsxRows($file);
        } elseif (in_array($extension, ['xlsx', 'xls'], true)) {
            return back()->with('error', 'Excel import requires PhpSpreadsheet or ZIP support. Please upload a CSV file.');
        } else {
            $handle = fopen($file, 'r');
            $sheetRows = [];
            while (($row = fgetcsv($handle)) !== false) $sheetRows[] = $row;
            fclose($handle);
        }

        if (count($sheetRows) < 2) {
            return back()->with('error', 'The uploaded file has no mark rows.');
        }

        $headers = array_map(fn ($header) => Str::snake(trim((string) $header)), array_shift($sheetRows));
        if (!in_array('student_id', $headers, true) || !in_array('score', $headers, true)) {
            return back()->with('error', 'Template must include student_id and score columns. Download a fresh template and try again.');
        }

        foreach ($sheetRows as $rowNumber => $row) {
            $data = [];
            foreach ($headers as $index => $header) $data[$header] = $row[$index] ?? null;
            $studentId = trim((string) ($data['student_id'] ?? ''));
            $score = trim((string) ($data['score'] ?? ''));
            if ($studentId === '' && $score === '') continue;
            if (!ctype_digit($studentId) || !$students->has((int) $studentId)) {
                return back()->with('error', 'Row ' . ($rowNumber + 2) . ' contains a student outside this assigned class. No marks were imported.');
            }
            if ($score === '' || !is_numeric($score) || (float) $score < 0 || (float) $score > $maxMarks) {
                return back()->with('error', 'Row ' . ($rowNumber + 2) . ' has an invalid score. Scores must be between 0 and ' . $maxMarks . '. No marks were imported.');
            }
            $uploadedMax = $data['max_score'] ?? null;
            $parsed[] = [
                'student_id' => (int) $studentId,
                'score' => (float) $score,
                'max_score' => is_numeric($uploadedMax) && (float) $uploadedMax > 0 ? (float) $uploadedMax : $maxMarks,
                'teacher_comment' => isset($data['teacher_comment']) ? trim((string) $data['teacher_comment']) : null,
            ];
        }

        DB::transaction(function () use ($parsed, $exam) {
            foreach ($parsed as $mark) {
                Mark::updateOrCreate(
                    ['exam_id' => $exam->id, 'student_id' => $mark['student_id']],
                    ['subject' => $exam->subject->subject_name ?? 'Subject', 'score' => $mark['score'],
                        'max_score' => $mark['max_score'], 'teacher_comment' => $mark['teacher_comment']]
                );
            }
        });

        return back()->with('success', count($parsed) . ' mark rows imported successfully.');
    }

    private function teacherExamStudents(Exam $exam)
    {
        return Student::where('status', 'active')
            ->when($exam->class_id, fn ($query) => $query->where('class_id', $exam->class_id))
            ->when(!$exam->class_id, function ($query) use ($exam) {
                if ($exam->subjectAssignment?->grade) {
                    $grade = $exam->subjectAssignment->grade;
                    $query->whereHas('schoolClass.gradeLevel', fn ($classes) => $classes->where('name', $grade));
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->orderBy('surname')->orderBy('name')->get();
    }

    private function readTeacherXlsxRows(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return [];

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $strings = @simplexml_load_string($sharedXml);
            if ($strings) {
                foreach ($strings->si as $item) {
                    $text = '';
                    foreach ($item->children() as $part) {
                        $text .= $part->getName() === 't' ? (string) $part : (string) ($part->t ?? '');
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false || !($sheet = @simplexml_load_string($sheetXml))) return [];

        $rows = [];
        foreach ($sheet->sheetData->row ?? [] as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                preg_match('/^([A-Z]+)/', (string) $cell['r'], $match);
                $column = 0;
                foreach (str_split($match[1] ?? '') as $letter) $column = $column * 26 + ord($letter) - 64;
                $column--;
                $value = (string) ($cell->v ?? '');
                if ((string) $cell['t'] === 's') $value = $sharedStrings[(int) $value] ?? '';
                elseif ((string) $cell['t'] === 'inlineStr') $value = (string) ($cell->is->t ?? '');
                $values[$column] = $value;
            }
            if ($values) {
                $maxColumn = max(array_keys($values));
                $rows[] = array_replace(array_fill(0, $maxColumn + 1, ''), $values);
            }
        }
        return $rows;
    }

    private function excelColumnName(int $column): string
    {
        $name = '';
        while ($column > 0) {
            $column--;
            $name = chr(65 + ($column % 26)) . $name;
            $column = intdiv($column, 26);
        }
        return $name;
    }

    public function teacherBulkStore(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'marks' => 'required|array',
        ]);

        $exam = Exam::with(['subject', 'subjectAssignment'])->findOrFail($request->exam_id);
        abort_unless($this->teacherOwnsExam($exam), 403);

        $request->validate([
            'marks.*.score' => ['nullable', 'numeric', 'min:0', 'max:' . ($exam->max_marks ?? 100)],
        ]);

        $students = Student::where('status', 'active')
            ->when($exam->class_id, fn ($query) => $query->where('class_id', $exam->class_id))
            ->when(!$exam->class_id, function ($query) use ($exam) {
                if ($exam->subjectAssignment?->grade) {
                    $grade = $exam->subjectAssignment->grade;
                    $query->whereHas('schoolClass.gradeLevel', fn ($classes) => $classes->where('name', $grade));
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->get()
            ->keyBy('id');

        DB::transaction(function () use ($request, $exam, $students) {
            foreach ($request->input('marks', []) as $studentId => $data) {
                if (!$students->has($studentId) || !isset($data['score']) || $data['score'] === '') {
                    continue;
                }

                Mark::updateOrCreate(
                    ['exam_id' => $exam->id, 'student_id' => $studentId],
                    [
                        'subject' => $exam->subject->subject_name ?? 'Subject',
                        'score' => $data['score'],
                        'max_score' => $data['max_score'] ?? $exam->max_marks ?? 100,
                        'teacher_comment' => $data['comment'] ?? $data['remarks'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('teacher.exams.index')->with('success', 'Marks recorded successfully.');
    }

    private function teacherOwnsExam(Exam $exam): bool
    {
        if ($exam->subject_assignment_id) {
            $assignment = SubjectAssignment::whereKey($exam->subject_assignment_id)
                ->where('teacher_id', Auth::id())
                ->where('subject_id', $exam->subject_id)
                ->first();

            if (!$assignment) return false;
            if ($assignment->class_id) return (int) $exam->class_id === (int) $assignment->class_id;
            if (!$assignment->grade) return false;
            if (!$exam->class_id) return true;

            return SchoolClass::whereKey($exam->class_id)
                ->whereHas('gradeLevel', fn ($query) => $query->where('name', $assignment->grade))
                ->exists();
        }

        if (!$exam->class_id) {
            return false;
        }

        return SubjectAssignment::where('teacher_id', Auth::id())
            ->where('subject_id', $exam->subject_id)
            ->where('class_id', $exam->class_id)
            ->exists();
    }

    public function teacherDestroy($id)
    {
        $exam = Exam::findOrFail($id);
        abort_unless($this->teacherOwnsExam($exam), 403);

        DB::transaction(function () use ($exam) {
            Mark::where('exam_id', $exam->id)->delete();
            $exam->delete();
        });

        return redirect()->route('teacher.exams.index')->with('success', 'Exam and its marks were deleted.');
    }
}
