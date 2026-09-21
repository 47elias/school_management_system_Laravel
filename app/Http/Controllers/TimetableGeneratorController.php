<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\SubjectAssignment;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableGeneratorController extends Controller
{
    public function generate(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Clear existing timetable slots before generating a fresh layout
            Timetable::truncate();

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            
            // Standard school period blocks
            $periods = [
                ['start' => '08:00:00', 'end' => '09:00:00'],
                ['start' => '09:00:00', 'end' => '10:00:00'],
                ['start' => '10:20:00', 'end' => '11:20:00'], // After morning break
                ['start' => '11:20:00', 'end' => '12:20:00'],
                ['start' => '13:20:00', 'end' => '14:20:00'], // After lunch
                ['start' => '14:20:00', 'end' => '15:20:00'],
            ];

            $classes = SchoolClass::where('status', 'active')->get();

            foreach ($classes as $schoolClass) {
                // Fetch assigned teachers and subjects for this class from your subject_assignments table
                $assignments = SubjectAssignment::where('class_id', $schoolClass->id)->get();

                if ($assignments->isEmpty()) {
                    continue; // Skip classes that have no subjects assigned yet
                }

                $assignmentIndex = 0;

                foreach ($days as $day) {
                    foreach ($periods as $period) {
                        // Cycle through the available subject assignments for this class
                        $assignment = $assignments[$assignmentIndex % $assignments->count()];

                        // Constraint Check: Ensure the teacher isn't double-booked at this day and time slot
                        $teacherConflict = Timetable::where('teacher_id', $assignment->teacher_id)
                            ->where('day', $day)
                            ->where('start_time', $period['start'])
                            ->exists();

                        if (!$teacherConflict) {
                            Timetable::create([
                                'class_id'    => $schoolClass->id,
                                'subject_id'  => $assignment->subject_id,
                                'teacher_id'  => $assignment->teacher_id,
                                'day'         => $day,
                                'start_time'  => $period['start'],
                                'end_time'    => $period['end'],
                                'type'        => 'SUBJECT',
                                'room_number' => $schoolClass->room_number ?? 'Main Hall',
                            ]);
                        }

                        $assignmentIndex++;
                    }
                }
            }

            DB::commit();
            return redirect()->route('timetable.index')->with('success', 'Timetable generated successfully with conflict checks resolved!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Timetable generation failed: ' . $e->getMessage());
        }
    }
}
