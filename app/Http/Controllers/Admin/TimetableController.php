<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::all();

        if ($request->has('view') && $request->view == 'master') {
            $timetables = Timetable::with(['subject', 'teacher', 'schoolClass'])
                ->orderBy('start_time')
                ->get();

            return view('timetable.show_master', compact('timetables', 'classes'));
        }

        return view('timetable.index', compact('classes'));
    }

    public function create()
    {
        $classes = SchoolClass::all();
        return view('timetable.create', compact('classes'));
    }

    public function getSubjectsByClass($classId)
    {
        $assignments = DB::table('subject_assignments')
            ->join('subjects', 'subject_assignments.subject_id', '=', 'subjects.id')
            ->join('users', 'subject_assignments.teacher_id', '=', 'users.id')
            ->where('subject_assignments.class_id', $classId)
            ->select(
                'subjects.id as subject_id',
                'subjects.subject_name',
                'users.id as teacher_id',
                'users.name as teacher_name'
            )
            ->get();

        return response()->json($assignments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => $request->apply_to_all_classes == '1' ? 'nullable' : 'required',
            'subject_id' => $request->apply_to_all_classes == '1' ? 'nullable' : 'required',
            'teacher_id' => $request->apply_to_all_classes == '1' ? 'nullable' : 'required',
            'day' => $request->apply_all_days == '1' ? 'nullable' : 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'special_type' => 'nullable|string',
        ]);

        $classIds = $request->apply_to_all_classes == '1'
            ? SchoolClass::pluck('id')->toArray()
            : [$request->class_id];

        $days = $request->apply_all_days == '1'
            ? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']
            : [$request->day];

        if ($request->apply_to_all_classes != '1' && $request->teacher_id) {
            $conflict = Timetable::where('teacher_id', $request->teacher_id)
                ->where('day', $request->day)
                ->where(function ($query) use ($request) {
                    $query->where('start_time', '<', $request->end_time)
                          ->where('end_time', '>', $request->start_time);
                })->exists();

            if ($conflict) {
                return back()->with('error', 'Teacher is already assigned to another class at this time.');
            }
        }

        foreach ($classIds as $classId) {
            foreach ($days as $day) {
                Timetable::create([
                    'class_id'   => $classId,
                    'subject_id' => $request->apply_to_all_classes == '1' ? null : $request->subject_id,
                    'teacher_id' => $request->apply_to_all_classes == '1' ? null : $request->teacher_id,
                    'day'        => $day,
                    'start_time' => $request->start_time,
                    'end_time'   => $request->end_time,
                    /**
                     * IMPORTANT: Based on your SQL, the column is 'type'.
                     * We save the label (Break, Lunch, etc.) into 'type'.
                     * If it's a normal lesson, it defaults to 'SUBJECT'.
                     */
                    'type'       => $request->special_type ?? 'SUBJECT',
                ]);
            }
        }

        return back()->with('success', 'Timetable slot(s) added successfully.');
    }

    public function bulkDeleteSpecial(Request $request)
    {
        $request->validate([
            'special_label' => 'required|string'
        ]);

        try {
            // Updated to use the 'type' column to match your database
            $deletedCount = Timetable::where('type', $request->special_label)
                ->whereNull('subject_id')
                ->delete();

            return back()->with('success', "Successfully removed $deletedCount slots for '{$request->special_label}' across all classes.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to perform bulk deletion.');
        }
    }

    public function show($class_id)
    {
        $class = SchoolClass::findOrFail($class_id);
        $timetables = Timetable::with(['subject', 'teacher', 'schoolClass'])
            ->where('class_id', $class_id)
            ->orderBy('start_time')
            ->get();

        return view('timetable.show', compact('class', 'timetables'));
    }

    public function destroy($id)
    {
        try {
            $timetable = Timetable::findOrFail($id);
            $timetable->delete();
            return back()->with('success', 'Schedule slot deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete the slot.');
        }
    }
}
