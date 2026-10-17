<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\GradeLevel;
use App\Models\SubjectCombination;
use App\Models\SubjectAssignment;
use App\Models\User;
use App\Models\Teacher;

class SubjectAssignmentController extends Controller
{
    /**
     * Safely retrieve school class streams across database architectures.
     */
    protected function getSchoolClasses()
    {
        if (class_exists(SchoolClass::class)) {
            return SchoolClass::all();
        }
        return collect();
    }

    /**
     * Retrieve grade levels dynamically from the database.
     */
    protected function getGradeLevels()
    {
        if (class_exists(GradeLevel::class) && Schema::hasTable('grade_levels')) {
            $query = GradeLevel::query();
            if (Schema::hasColumn('grade_levels', 'level_order')) {
                $query->orderBy('level_order', 'asc');
            } elseif (Schema::hasColumn('grade_levels', 'id')) {
                $query->orderBy('id', 'asc');
            }
            $levels = $query->get();
            if ($levels->isNotEmpty()) {
                return $levels;
            }
        }

        if (class_exists(SchoolClass::class) && Schema::hasTable('school_classes')) {
            $column = Schema::hasColumn('school_classes', 'grade_level') ? 'grade_level' : (Schema::hasColumn('school_classes', 'grade') ? 'grade' : null);
            if ($column) {
                return SchoolClass::whereNotNull($column)
                    ->where($column, '!=', '')
                    ->pluck($column)
                    ->unique()
                    ->values();
            }
        }

        return collect();
    }

    /**
     * Safely retrieve teachers/educators across database architectures.
     */
    protected function getTeachers()
    {
        if (class_exists(Teacher::class) && Teacher::count() > 0) {
            return Teacher::orderBy('name', 'asc')->get();
        }

        if (class_exists(User::class)) {
            $query = User::query();

            if (Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
                $query->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['teacher', 'Teacher', 'Educator', 'educator', 'Staff', 'staff']);
                });
            } else {
                $query->where(function ($q) {
                    if (Schema::hasColumn('users', 'role')) {
                        $q->orWhereIn('role', ['teacher', 'Teacher', 'Educator', 'educator', 'staff']);
                    }
                    if (Schema::hasColumn('users', 'type')) {
                        $q->orWhereIn('type', ['teacher', 'Teacher', 'Educator', 'educator', 'staff']);
                    }
                });
            }

            $teachers = $query->orderBy('name', 'asc')->get();
            return $teachers->isNotEmpty() ? $teachers : User::orderBy('name', 'asc')->get();
        }

        return collect();
    }

    /**
     * Safely retrieve subject combinations with loaded subjects.
     */
    protected function getCombinations()
    {
        if (class_exists(SubjectCombination::class)) {
            return SubjectCombination::with('subjects')->get();
        }

        return collect();
    }

    /**
     * Safely retrieve teaching assignments with loaded relationships.
     */
    protected function getAssignments()
    {
        if (class_exists(SubjectAssignment::class)) {
            return SubjectAssignment::with(['teacher', 'subject', 'schoolClass'])->get();
        }

        return collect();
    }

    /**
     * 1. CURRICULUM DASHBOARD VIEW
     * Route: GET /subjects
     */
    public function index()
    {
        $subjects = Subject::all();
        $schoolClasses = $this->getSchoolClasses();
        $gradeLevels = $this->getGradeLevels();
        $combinations = $this->getCombinations();
        $assignments = $this->getAssignments();

        return view('subjects.index', compact(
            'subjects',
            'schoolClasses',
            'gradeLevels',
            'combinations',
            'assignments'
        ));
    }

    /**
     * 2. SUBJECT & COMBINATION STUDIO VIEW
     * Route: GET /subjects/manage
     */
    public function manage()
    {
        $subjects = Subject::all();
        $gradeLevels = $this->getGradeLevels();
        $combinations = $this->getCombinations();

        return view('subjects.manage', compact(
            'subjects',
            'gradeLevels',
            'combinations'
        ));
    }

    /**
     * 3. CLASS & STREAM SUBJECT ALLOCATION VIEW
     * Route: GET /subjects/allocate-classes
     */
    public function allocateClasses()
    {
        $subjects = Subject::all();
        $gradeLevels = $this->getGradeLevels();
        $schoolClasses = $this->getSchoolClasses();
        $combinations = $this->getCombinations();
        $assignments = $this->getAssignments();

        return view('subjects.allocate_classes', compact(
            'subjects',
            'gradeLevels',
            'schoolClasses',
            'combinations',
            'assignments'
        ));
    }

    /**
     * 4. FACULTY TEACHING LOAD ALLOCATION VIEW
     * Route: GET /subjects/allocate-teachers
     */
    public function allocateTeachers()
    {
        $subjects = Subject::all();
        $teachers = $this->getTeachers();
        $gradeLevels = $this->getGradeLevels();
        $schoolClasses = $this->getSchoolClasses();
        $assignments = $this->getAssignments();

        return view('subjects.allocate_teachers', compact(
            'subjects',
            'teachers',
            'gradeLevels',
            'schoolClasses',
            'assignments'
        ));
    }

    /**
     * Default Resource Store Action for Subject Assignments.
     * Route: POST /subject-assignments
     */
    public function store(Request $request)
    {
        if ($request->has('subject_name') && !$request->has('teacher_id')) {
            $data = $request->validate([
                'subject_name' => 'required|string|max:255',
                'subject_code' => 'required|string|max:50|unique:subjects,subject_code',
                'type'         => 'required|string',
                'pass_mark'    => 'required|integer|min:0|max:100'
            ]);

            Subject::create($data);
            return back()->with('success', 'Subject created successfully!');
        }

        return $this->storeTeacherAssignment($request);
    }

    /**
     * Update an existing subject.
     * Route: PUT/PATCH /subjects/{id}
     */
    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $data = $request->validate([
            'subject_name' => 'required|string|max:255',
            'subject_code' => 'required|string|max:50|unique:subjects,subject_code,' . $subject->id,
            'type'         => 'required|string',
            'pass_mark'    => 'required|integer|min:0|max:100'
        ]);

        $subject->update($data);

        return back()->with('success', 'Subject updated successfully!');
    }

    /**
     * Delete a subject assignment or unassign teacher.
     * Route: DELETE /subject-assignments/{id}
     */
    public function destroy($id)
    {
        if (class_exists(SubjectAssignment::class)) {
            $assignment = SubjectAssignment::find($id);
            if ($assignment) {
                if ($assignment->teacher_id) {
                    $assignment->update(['teacher_id' => null]);
                    return back()->with('success', 'Teacher unassigned successfully!');
                }
                $assignment->delete();
                return back()->with('success', 'Subject allocation removed successfully.');
            }
        }

        if (class_exists(Subject::class)) {
            $subject = Subject::find($id);
            if ($subject) {
                $subject->delete();
                return back()->with('success', 'Subject deleted successfully from registry.');
            }
        }

        return back()->with('error', 'Record not found.');
    }

    /**
     * Store a new subject combination package.
     * Route: POST /subject-combinations
     */
    public function storeCombination(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:50',
            'grade'         => 'nullable|string|max:100',
            'subject_ids'   => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id'
        ]);

        if (class_exists(SubjectCombination::class)) {
            $combination = SubjectCombination::create([
                'name'  => $data['name'],
                'code'  => $data['code'] ?? null,
                'grade' => $data['grade'] ?? null,
            ]);

            if (method_exists($combination, 'subjects')) {
                $combination->subjects()->sync($data['subject_ids']);
            }
        }

        return back()->with('success', 'Subject combination package created successfully!');
    }

    /**
     * Delete a subject combination package.
     * Route: DELETE /subject-combinations/{id}
     */
    public function destroyCombination($id)
    {
        if (class_exists(SubjectCombination::class)) {
            $combination = SubjectCombination::findOrFail($id);
            if (method_exists($combination, 'subjects')) {
                $combination->subjects()->detach();
            }
            $combination->delete();
        }

        return back()->with('success', 'Subject combination deleted successfully.');
    }

    /**
     * Store class or grade subject / combination package allocation.
     * Route: POST /class-subjects
     */
    public function storeClassAllocation(Request $request)
    {
        $data = $request->validate([
            'scope'           => 'required|string|in:grade,stream',
            'allocation_type' => 'required|string|in:subject,combination',
            'grade'           => 'nullable|required_if:scope,grade|string',
            'class_id'        => 'nullable|required_if:scope,stream',
            'subject_id'      => 'nullable|exists:subjects,id',
            'subject_ids'     => 'nullable|array',
            'subject_ids.*'   => 'exists:subjects,id',
            'combination_id'  => 'nullable|required_if:allocation_type,combination',
            'academic_year'   => 'nullable|string',
        ]);

        if (class_exists(SubjectAssignment::class)) {
            $academicYear = $data['academic_year'] ?? date('Y');

            $subjectIds = [];
            if ($data['allocation_type'] === 'combination' && !empty($data['combination_id']) && class_exists(SubjectCombination::class)) {
                $combination = SubjectCombination::with('subjects')->find($data['combination_id']);
                if ($combination) {
                    $subjectIds = $combination->subjects->pluck('id')->toArray();
                }
            } else {
                if (!empty($data['subject_ids']) && is_array($data['subject_ids'])) {
                    $subjectIds = $data['subject_ids'];
                } elseif (!empty($data['subject_id'])) {
                    $subjectIds = [$data['subject_id']];
                }
            }

            if (empty($subjectIds)) {
                return back()->withErrors(['subject_ids' => 'Please select at least one subject to allocate.']);
            }

            foreach ($subjectIds as $sId) {
                if ($data['scope'] === 'grade') {
                    SubjectAssignment::firstOrCreate([
                        'grade'         => $data['grade'],
                        'class_id'      => null,
                        'subject_id'    => $sId,
                        'academic_year' => $academicYear,
                    ], [
                        'teacher_id'    => null,
                    ]);
                } else {
                    SubjectAssignment::firstOrCreate([
                        'class_id'      => $data['class_id'],
                        'subject_id'    => $sId,
                        'academic_year' => $academicYear,
                    ], [
                        'grade'         => null,
                        'teacher_id'    => null,
                    ]);
                }
            }
        }

        return back()->with('success', 'Class subject allocation saved successfully!');
    }

    /**
     * Delete a class subject allocation.
     * Route: DELETE /class-subjects/{id}
     */
    public function destroyClassAllocation($id)
    {
        if (class_exists(SubjectAssignment::class)) {
            $assignment = SubjectAssignment::findOrFail($id);
            $assignment->delete();
        }

        return back()->with('success', 'Class subject allocation deleted successfully.');
    }

    /**
     * Store or update faculty teaching load assignment.
     * Route: POST /subject-assignments
     */
    public function storeTeacherAssignment(Request $request)
    {
        $data = $request->validate([
            'scope'         => 'nullable|string|in:grade,stream',
            'teacher_id'    => 'required',
            'subject_id'    => 'required|exists:subjects,id',
            'grade'         => 'nullable|required_if:scope,grade|string',
            'class_id'      => 'nullable|required_if:scope,stream',
            'academic_year' => 'nullable|string'
        ]);

        $academicYear = $data['academic_year'] ?? date('Y');
        $scope = $data['scope'] ?? (!empty($data['grade']) ? 'grade' : 'stream');

        if (class_exists(SubjectAssignment::class)) {
            if ($scope === 'grade' && !empty($data['grade'])) {
                SubjectAssignment::updateOrCreate(
                    [
                        'subject_id'    => $data['subject_id'],
                        'grade'         => $data['grade'],
                        'academic_year' => $academicYear,
                    ],
                    [
                        'teacher_id'    => $data['teacher_id'],
                        'class_id'      => null,
                    ]
                );
            } else {
                SubjectAssignment::updateOrCreate(
                    [
                        'subject_id'    => $data['subject_id'],
                        'class_id'      => $data['class_id'],
                        'academic_year' => $academicYear,
                    ],
                    [
                        'teacher_id'    => $data['teacher_id'],
                        'grade'         => null,
                    ]
                );
            }
        }

        return back()->with('success', 'Faculty teaching load assigned successfully!');
    }

    /**
     * Remove faculty teaching load assignment.
     * Route: DELETE /subject-assignments/{id}
     */
    public function destroyTeacherAssignment($id)
    {
        if (class_exists(SubjectAssignment::class)) {
            $assignment = SubjectAssignment::findOrFail($id);
            if ($assignment->teacher_id) {
                $assignment->update(['teacher_id' => null]);
                return back()->with('success', 'Faculty unassigned successfully!');
            }
            $assignment->delete();
        }

        return back()->with('success', 'Faculty assignment removed successfully.');
    }
}