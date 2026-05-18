<!DOCTYPE html>
<html>
<head>
    <title>View Timetable | {{ $class->class_name }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('components.adminlte')
    <style>
        :root {
            --primary-soft: #eef2ff;
            --primary-dark: #3730a3;
            --accent: #3c8dbc;
            --special-bg: #fff7ed;
            --special-text: #9a3412;
            --special-border: #f97316;
        }
        .content-wrapper { background-color: #f8fafc !important; }

        /* Modern Table Styling */
        .timetable-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .timetable-table { margin-bottom: 0; border: none !important; table-layout: fixed; width: 100%; }
        .timetable-table th {
            background: #1e293b !important;
            color: white !important;
            text-transform: uppercase;
            font-size: 10px;
            padding: 8px 4px !important;
            border: 1px solid #334155 !important;
            vertical-align: middle !important;
        }
        .timetable-table td {
            border: 1px solid #f1f5f9 !important;
            vertical-align: middle !important;
            padding: 4px !important;
            height: 80px;
        }

        .day-col {
            background: #f1f5f9 !important;
            font-weight: 800;
            color: #1e293b;
            width: 100px !important;
            font-size: 12px;
            border-right: 2px solid #cbd5e1 !important;
        }

        /* Slot Cards */
        .slot-card {
            background: var(--primary-soft);
            border-radius: 4px;
            padding: 5px;
            border-left: 3px solid var(--accent);
            text-align: center;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }

        .slot-card.special-slot {
            background: var(--special-bg);
            border-left-color: var(--special-border);
        }

        .slot-card.special-slot .subject-name {
            color: var(--special-text);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
        }

        .delete-slot {
            position: absolute;
            top: 1px;
            right: 1px;
            color: #ef4444;
            opacity: 0;
            cursor: pointer;
            background: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #fee2e2;
            font-size: 10px;
            z-index: 10;
        }

        .slot-card.special-slot .delete-slot { opacity: 1 !important; background: #fee2e2; }
        .slot-card:hover .delete-slot { opacity: 1; }

        .subject-name {
            font-size: 11px;
            font-weight: 700;
            color: var(--primary-dark);
            line-height: 1.1;
            display: block;
        }
        .teacher-name {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
            display: block;
        }

        #print-header { display: none; }

        @media print {
            @page { size: landscape; margin: 0.5cm; }
            .no-print, .main-sidebar, .main-header, .delete-slot, .btn { display: none !important; }
            .content-wrapper { margin-left: 0 !important; padding: 0 !important; background: white !important; }
            .timetable-container { box-shadow: none; border: 1px solid #000; }
            .timetable-table th { background: #eee !important; color: #000 !important; border: 1px solid #000 !important; }
            .timetable-table td { border: 1px solid #000 !important; }
            .day-col { background: #f5f5f5 !important; border-right: 1px solid #000 !important; }
            #print-header { display: block !important; text-align: center; margin-bottom: 10px; }
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <div id="print-header">
                <h1>{{ env('SCHOOL_NAME', 'KNOWLEDGE PLANET COLLEGE') }}</h1>
                <h2>Timetable for {{ $class->class_name }}</h2>
            </div>

            <section class="content-header no-print">
                <div class="row">
                    <div class="col-xs-8">
                        <h1 style="font-weight: 800; color: #1e293b; margin: 0;">
                            <i class="fa fa-calendar text-blue"></i> {{ $class->class_name }} Timetable
                            <small style="display:block; margin-top:5px;">Time (Horizontal) | Days (Vertical)</small>
                        </h1>
                    </div>
                    <div class="col-xs-4 text-right">
                        <button onclick="window.print()" class="btn btn-primary btn-flat"><i class="fa fa-print"></i> PRINT</button>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="timetable-container">
                    <div class="table-responsive">
                        <table class="table timetable-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 100px;">DAY</th>
                                    @php
                                        $uniqueSlots = $timetables->map(function($t) {
                                            return ['start' => $t->start_time, 'end' => $t->end_time];
                                        })->unique(function($item) {
                                            return $item['start'].$item['end'];
                                        })->sortBy('start');

                                        $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                                    @endphp
                                    @foreach($uniqueSlots as $index => $slot)
                                        <th class="text-center">
                                            P{{ $index + 1 }}<br>
                                            <small style="font-weight: 400; font-size: 9px;">
                                                {{ \Carbon\Carbon::parse($slot['start'])->format('H:i') }}-{{ \Carbon\Carbon::parse($slot['end'])->format('H:i') }}
                                            </small>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($weekDays as $dayName)
                                <tr>
                                    <td class="text-center day-col">
                                        {{ strtoupper($dayName) }}
                                    </td>
                                    @foreach($uniqueSlots as $slot)
                                        <td class="text-center">
                                            @php
                                                $lesson = $timetables->where('day', $dayName)
                                                                    ->where('start_time', $slot['start'])
                                                                    ->where('end_time', $slot['end'])
                                                                    ->first();
                                            @endphp
                                            @if($lesson)
                                                <div class="slot-card {{ is_null($lesson->subject_id) ? 'special-slot' : '' }}">
                                                    <form action="{{ route('timetable.destroy', $lesson->id) }}" method="POST" class="no-print">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="delete-slot" onclick="return confirm('Delete this slot?')"><i class="fa fa-times"></i></button>
                                                    </form>

                                                    @if(is_null($lesson->subject_id))
                                                        {{-- Updated to show specific label from special_type column --}}
                                                        <span class="subject-name">{{ strtoupper($lesson->special_type ?? 'BREAK') }}</span>
                                                    @else
                                                        <span class="subject-name">{{ $lesson->subject->subject_name }}</span>
                                                        <span class="teacher-name">{{ $lesson->teacher->name }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span style="color: #cbd5e1;">&bull;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @include('components.scripts')
</body>
</html>
