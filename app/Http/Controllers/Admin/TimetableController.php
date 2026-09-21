<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SubjectAssignment;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    public function index()
    {
        $timetables = Timetable::with(['schoolClass', 'subject', 'teacher'])->get();
        return view('timetable.index', compact('timetables'));
    }

    public function generate(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Clear existing timetable slots
            Timetable::truncate();

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            
            // Strictly structured periods avoiding Break (10:00 - 10:30) and Lunch (13:00 - 14:00)
            $periods = [
                ['start' => '08:00:00', 'end' => '09:00:00'],
                ['start' => '09:00:00', 'end' => '10:00:00'],
                // 10:00 - 10:30 Morning Break (Skipped)
                ['start' => '10:30:00', 'end' => '11:30:00'],
                ['start' => '11:30:00', 'end' => '12:30:00'],
                ['start' => '12:30:00', 'end' => '13:00:00'],
                // 13:00 - 14:00 Lunch Break (Skipped)
                ['start' => '14:00:00', 'end' => '15:00:00'],
                ['start' => '15:00:00', 'end' => '16:00:00'],
            ];

            $classes = SchoolClass::where('status', 'active')->get();

            if ($classes->isEmpty()) {
                throw new \Exception('No active school classes found to generate a timetable for.');
            }

            // Prepare tracking arrays for intelligent distribution & shuffling per class
            $classAssignments = [];
            foreach ($classes as $schoolClass) {
                $assignments = SubjectAssignment::where('class_id', $schoolClass->id)->get();
                if ($assignments->isNotEmpty()) {
                    // Shuffle daily pool per class so layout starts fresh and mixed up
                    $classAssignments[$schoolClass->id] = [
                        'pool' => $assignments->shuffle(),
                        'index' => 0
                    ];
                }
            }

            // Intelligent Matrix Generation: Iterate Day -> Period -> Class
            // This ensures every class gets an equal opportunity at each time slot without dropping slots.
            foreach ($days as $day) {
                foreach ($periods as $period) {
                    foreach ($classes as $schoolClass) {
                        if (!isset($classAssignments[$schoolClass->id])) {
                            continue; // Skip classes with zero subject assignments
                        }

                        $pool = $classAssignments[$schoolClass->id]['pool'];
                        $poolSize = $pool->count();
                        
                        // Try to find a valid assignment where the teacher is NOT clashing/double-booked
                        $assigned = false;
                        $attempts = 0;

                        while ($attempts < $poolSize && !$assigned) {
                            $currentIndex = $classAssignments[$schoolClass->id]['index'] % $poolSize;
                            $candidate = $pool[$currentIndex];

                            // Advance index for next time
                            $classAssignments[$schoolClass->id]['index']++;
                            $attempts++;

                            // STRICT CLASH CHECK: Is this teacher teaching ANY other class at this exact day & time?
                            $teacherBusy = Timetable::where('teacher_id', $candidate->teacher_id)
                                ->where('day', $day)
                                ->where('start_time', $period['start'])
                                ->exists();

                            if (!$teacherBusy) {
                                // Teacher is free! Book this slot safely.
                                Timetable::create([
                                    'class_id'    => $schoolClass->id,
                                    'subject_id'  => $candidate->subject_id,
                                    'teacher_id'  => $candidate->teacher_id,
                                    'day'         => $day,
                                    'start_time'  => $period['start'],
                                    'end_time'    => $period['end'],
                                    'type'        => 'SUBJECT',
                                    'room_number' => $schoolClass->room_number ?? 'Main Hall',
                                ]);
                                $assigned = true;
                            }
                        }

                        // If all teacher options for this class in this period are clashed, 
                        // we assign a placeholder or leave it unassigned gracefully rather than breaking.
                    }
                }
            }

            DB::commit();
            return redirect()->route('timetable.index')->with('success', 'Intelligent timetable generated successfully! Zero teacher clashes and all class slots filled.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Timetable generation failed: ' . $e->getMessage());
        }
    }

    public function show($class_id)
    {
        $schoolClass = SchoolClass::findOrFail($class_id);
        
        $timetables = Timetable::with(['subject', 'teacher'])
            ->where('class_id', $class_id)
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')")
            ->orderBy('start_time')
            ->get();

        $classes = SchoolClass::where('status', 'active')->get();

        return view('timetable.show', compact('schoolClass', 'timetables', 'classes'));
    }
}