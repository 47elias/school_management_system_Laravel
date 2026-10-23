<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SubjectAssignment;
use App\Models\Timetable;
use App\Models\TimetableSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    public function index()
    {
        $timetables = Timetable::with(['schoolClass', 'subject', 'teacher'])
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')")
            ->orderBy('start_time')
            ->get();
        $classes = SchoolClass::where('status', 'active')->orderBy('class_name')->get();
        $settings = TimetableSetting::query()->find(1)?->settings ?? [
            'periods_per_day' => 7,
            'day_start' => '08:00',
            'period_duration' => 50,
            'period_gap' => 5,
            'events' => [
                ['label' => 'Assembly', 'after_period' => 0, 'duration' => 20],
                ['label' => 'Tea Break', 'after_period' => 2, 'duration' => 20],
                ['label' => 'Lunch', 'after_period' => 4, 'duration' => 45],
            ],
        ];
        return view('timetable.index', compact('timetables', 'classes', 'settings'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'periods_per_day' => ['required', 'integer', 'min:1', 'max:12'],
            'day_start' => ['required', 'date_format:H:i'],
            'period_duration' => ['required', 'integer', 'min:20', 'max:180'],
            'period_gap' => ['required', 'integer', 'min:0', 'max:60'],
            'events' => ['nullable', 'array', 'max:8'],
            'events.*.label' => ['required', 'string', 'max:40'],
            'events.*.after_period' => ['required', 'integer', 'min:0'],
            'events.*.duration' => ['required', 'integer', 'min:5', 'max:180'],
        ]);
        foreach ($data['events'] ?? [] as $event) {
            if ($event['after_period'] > $data['periods_per_day']) {
                return back()->withInput()->withErrors(['events' => 'Each break or activity must be placed after a period that exists.']);
            }
        }

        DB::beginTransaction();
        try {
            // Keep this inside the transaction; TRUNCATE implicitly commits on MySQL.
            Timetable::query()->delete();

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            
            $periodCount = (int) $data['periods_per_day'];
            $periodMinutes = (int) $data['period_duration'];
            $gapMinutes = (int) $data['period_gap'];
            $eventsAfter = collect($data['events'] ?? [])->groupBy(fn ($event) => (int) $event['after_period']);
            $clockMinutes = ((int) substr($data['day_start'], 0, 2) * 60) + (int) substr($data['day_start'], 3, 2);
            $dailySlots = [];
            foreach ($eventsAfter->get(0, collect()) as $event) {
                $start = $clockMinutes;
                $clockMinutes += (int) $event['duration'];
                $dailySlots[] = [
                    'kind' => 'EVENT', 'label' => trim($event['label']),
                    'start' => sprintf('%02d:%02d:00', intdiv($start, 60), $start % 60),
                    'end' => sprintf('%02d:%02d:00', intdiv($clockMinutes, 60), $clockMinutes % 60),
                ];
            }
            for ($periodNumber = 1; $periodNumber <= $periodCount; $periodNumber++) {
                $start = $clockMinutes;
                $clockMinutes += $periodMinutes;
                $dailySlots[] = [
                    'kind' => 'SUBJECT', 'period' => $periodNumber,
                    'start' => sprintf('%02d:%02d:00', intdiv($start, 60), $start % 60),
                    'end' => sprintf('%02d:%02d:00', intdiv($clockMinutes, 60), $clockMinutes % 60),
                ];
                foreach ($eventsAfter->get($periodNumber, collect()) as $event) {
                    $start = $clockMinutes;
                    $clockMinutes += (int) $event['duration'];
                    $dailySlots[] = [
                        'kind' => 'EVENT', 'label' => trim($event['label']),
                        'start' => sprintf('%02d:%02d:00', intdiv($start, 60), $start % 60),
                        'end' => sprintf('%02d:%02d:00', intdiv($clockMinutes, 60), $clockMinutes % 60),
                    ];
                }
                if ($periodNumber < $periodCount) {
                    $clockMinutes += $gapMinutes;
                }
            }
            if ($clockMinutes > (24 * 60)) {
                DB::rollBack();
                return back()->withInput()->withErrors(['day_start' => 'The configured periods and activities extend beyond midnight.']);
            }

            $classes = SchoolClass::with('gradeLevel')->where('status', 'active')->orderBy('class_name')->get();

            if ($classes->isEmpty()) {
                throw new \Exception('No active school classes found to generate a timetable for.');
            }

            // Resolve both stream-specific and grade-wide allocations for each stream.
            $classAssignments = [];
            foreach ($classes as $schoolClass) {
                $assignmentsQuery = SubjectAssignment::where('class_id', $schoolClass->id);
                if ($schoolClass->gradeLevel?->name) {
                    $assignmentsQuery->orWhere(function ($query) use ($schoolClass) {
                        $query->whereNull('class_id')->where('grade', $schoolClass->gradeLevel->name);
                    });
                }
                $assignments = $assignmentsQuery->get()
                    ->unique(fn ($assignment) => $assignment->subject_id . ':' . $assignment->teacher_id)
                    ->values();
                if ($assignments->isNotEmpty()) {
                    // Shuffle daily pool per class so layout starts fresh and mixed up
                    $classAssignments[$schoolClass->id] = [
                        'pool' => $assignments->shuffle(),
                        'index' => 0
                    ];
                }
            }

            $scheduled = 0;
            // Schedule each active stream in every period, avoiding teacher, room,
            // and stream overlaps even if period definitions are later changed.
            foreach ($days as $day) {
                foreach ($dailySlots as $period) {
                    foreach ($classes as $schoolClass) {
                        if ($period['kind'] === 'EVENT') {
                            Timetable::create([
                                'class_id' => $schoolClass->id,
                                'day' => $day,
                                'start_time' => $period['start'],
                                'end_time' => $period['end'],
                                'type' => 'SPECIAL',
                                'special_type' => $period['label'],
                                'room_number' => $schoolClass->room_number,
                            ]);
                            continue;
                        }
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

                            $overlap = function ($query) use ($day, $period) {
                                $query->where('day', $day)
                                    ->where('start_time', '<', $period['end'])
                                    ->where('end_time', '>', $period['start']);
                            };
                            $teacherBusy = $candidate->teacher_id && Timetable::where('teacher_id', $candidate->teacher_id)
                                ->where($overlap)->exists();
                            $roomBusy = $schoolClass->room_number && Timetable::where('room_number', $schoolClass->room_number)
                                ->where($overlap)->exists();

                            if (!$teacherBusy && !$roomBusy) {
                                // Teacher is free! Book this slot safely.
                                Timetable::create([
                                    'class_id'    => $schoolClass->id,
                                    'subject_id'  => $candidate->subject_id,
                                    'teacher_id'  => $candidate->teacher_id,
                                    'day'         => $day,
                                    'start_time'  => $period['start'],
                                    'end_time'    => $period['end'],
                                    'type'        => 'SUBJECT',
                                    'room_number' => $schoolClass->room_number,
                                ]);
                                $assigned = true;
                                $scheduled++;
                            }
                        }

                    }
                }
            }

            $unfilled = ($classes->count() * count($days) * $periodCount) - $scheduled;
            $savedSettings = TimetableSetting::query()->firstOrNew(['id' => 1]);
            $savedSettings->settings = [
                'periods_per_day' => $periodCount,
                'day_start' => $data['day_start'],
                'period_duration' => $periodMinutes,
                'period_gap' => $gapMinutes,
                'events' => array_values($data['events'] ?? []),
            ];
            $savedSettings->save();
            DB::commit();
            return redirect()->route('timetable.index')->with('success', "Timetable generated for {$classes->count()} active streams: {$scheduled} periods scheduled, {$unfilled} left open because no clash-free allocation was available.");
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
