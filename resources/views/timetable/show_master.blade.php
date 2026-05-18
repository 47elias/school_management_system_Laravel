<!DOCTYPE html>
<html>
<head>
    <title>Master Timetable | {{ env('SCHOOL_ACRONYM') }}</title>
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

        /* Master Table Styling */
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
            height: 85px;
        }

        /* The Class Column (Vertical Axis) */
        .class-col {
            background: #f1f5f9 !important;
            font-weight: 800;
            color: #1e293b;
            width: 120px !important;
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
            font-size: 10px;
            letter-spacing: 1px;
        }

        .delete-slot {
            position: absolute;
            top: 2px;
            right: 2px;
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
            padding: 0;
            transition: opacity 0.2s;
        }

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
            margin-top: 3px;
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
            .class-col { background: #f5f5f5 !important; border-right: 2px solid #000 !important; }
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
                <h1>{{ env('SCHOOL_NAME') }}</h1>
                <h2>Master Timetable - School Wide</h2>
            </div>

            <section class="content-header no-print">
                <div class="row">
                    <div class="col-xs-8">
                        <h1 style="font-weight: 800; color: #1e293b; margin: 0;">
                            <i class="fa fa-globe text-blue"></i> Master Timetable
                            <small style="display:block; margin-top:5px;">All Classes | Monday - Friday</small>
                        </h1>
                    </div>
                    <div class="col-xs-4 text-right">
                        <a href="{{ route('timetable.index') }}" class="btn btn-default btn-flat border"><i class="fa fa-arrow-left"></i> BACK</a>
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
                                    <th class="text-center class-col">CLASS / TIME</th>
                                    @php
                                        // Get unique time slots from the collection
                                        $uniqueSlots = $timetables->map(function($t) {
                                            return ['start' => $t->start_time, 'end' => $t->end_time];
                                        })->unique(function($item) {
                                            return $item['start'].$item['end'];
                                        })->sortBy('start');
                                    @endphp

                                    @foreach($uniqueSlots as $index => $slot)
                                        <th class="text-center">
                                            PERIOD {{ $index + 1 }}<br>
                                            <small style="font-weight: 400; font-size: 10px;">
                                                {{ \Carbon\Carbon::parse($slot['start'])->format('H:i') }} - {{ \Carbon\Carbon::parse($slot['end'])->format('H:i') }}
                                            </small>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classes as $class)
                                <tr>
                                    <td class="text-center class-col">
                                        {{ $class->class_name }}
                                        <br><small style="font-weight: normal; color: #64748b;">{{ $class->class_code }}</small>
                                    </td>
                                    @foreach($uniqueSlots as $slot)
                                        <td class="text-center">
                                            @php
                                                // Find if this class has a lesson in this specific time slot
                                                // Note: This logic assumes you are viewing a specific day or all days.
                                                // Usually, Master View is shown per day. You can add a day filter if needed.
                                                $lesson = $timetables->where('class_id', $class->id)
                                                                    ->where('start_time', $slot['start'])
                                                                    ->where('end_time', $slot['end'])
                                                                    ->first();
                                            @endphp

                                            @if($lesson)
                                                <div class="slot-card {{ is_null($lesson->subject_id) ? 'special-slot' : '' }}">
                                                    {{-- Delete Button --}}
                                                    <form action="{{ route('timetable.destroy', $lesson->id) }}" method="POST" class="no-print">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="delete-slot" onclick="return confirm('Delete this slot?')">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </form>

                                                    @if(is_null($lesson->subject_id))
                                                        <span class="subject-name">BREAK / LUNCH</span>
                                                    @else
                                                        <span class="subject-name">{{ $lesson->subject->subject_name }}</span>
                                                        <span class="teacher-name">{{ $lesson->teacher->name }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span style="color: #cbd5e1; font-size: 14px;">&bull;</span>
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
