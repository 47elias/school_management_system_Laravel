<!DOCTYPE html>
<html lang="en">
<head>
    <title>Class Timetable | {{ $schoolClass->class_name }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    @include('components.adminlte')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --brand-primary: #3c8dbc;
            --bg-light: #f4f6f9;
            --border-color: #d2d6de;
        }

        html, body {
            font-family: 'Inter', sans-serif !important;
            background-color: var(--bg-light) !important;
            height: 100vh !important;
            overflow: hidden !important;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            height: 100vh !important;
            display: flex;
            flex-direction: column;
            overflow: hidden !important;
        }

        .content-wrapper {
            background-color: var(--bg-light) !important;
            flex: 1 1 auto !important;
            height: calc(100vh - 50px) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding-bottom: 30px;
        }

        .table-matrix th, .table-matrix td { 
            vertical-align: middle !important; 
            text-align: center;
            padding: 12px 8px !important; 
            font-size: 13px;
        }

        .table-matrix th:first-child, .table-matrix td:first-child {
            font-weight: 700;
            background: #fafafa;
            text-align: left;
            width: 110px;
        }

        .box { 
            border-radius: 4px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
            border-top: 3px solid var(--brand-primary); 
            background: #fff;
            margin-bottom: 20px;
        }

        .box-header { padding: 15px 20px; border-bottom: 1px solid #f4f4f4; }
        .box-body { padding: 20px; }
        
        .class-switcher select {
            border-radius: 4px;
            border: 1px solid #ccc;
            padding: 6px 12px;
            height: 34px;
            font-size: 13px;
            background: #fff;
        }

        .slot-subject {
            font-weight: 600;
            color: #1e293b;
            display: block;
        }

        .slot-teacher {
            font-size: 11px;
            color: #64748b;
            display: block;
            margin-top: 3px;
            font-style: italic;
        }

        .empty-slot {
            color: #cbd5e1;
            font-style: italic;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <!-- Content Header -->
            <section class="content-header" style="padding: 15px 20px;">
                <h1>
                    <i class="fa fa-calendar-o text-blue"></i> Timetable Matrix: {{ $schoolClass->class_name }}
                    <small>Room: {{ $schoolClass->room_number ?? 'N/A' }}</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="{{ route('timetable.index') }}">Timetable</a></li>
                    <li class="active">{{ $schoolClass->class_name }}</li>
                </ol>
            </section>

            <!-- Main content -->
            <section class="content" style="padding: 0 20px;">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title" style="font-weight: 600;">Weekly Schedule Grid</h3>
                        <div class="box-tools pull-right class-switcher" style="top: 8px;">
                            <label style="font-weight: 500; font-size: 12px; margin-right: 5px; color: #666;">Switch Class:</label>
                            <select onchange="if(this.value) window.location.href='/admin/timetable/class/' + this.value;">
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}" {{ $c->id == $schoolClass->id ? 'selected' : '' }}>
                                        {{ $c->class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="box-body">
                        @if($timetables->isEmpty())
                            <div class="alert alert-warning text-center" style="margin: 0; border-radius: 4px;">
                                <i class="fa fa-exclamation-triangle"></i> No timetable slots have been scheduled or generated for <strong>{{ $schoolClass->class_name }}</strong> yet.
                            </div>
                        @else
                            @php
                                // Extract unique time blocks sorted chronologically for table columns
                                $timeSlots = $timetables->sortBy('start_time')->map(function($slot) {
                                    return \Carbon\Carbon::parse($slot->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($slot->end_time)->format('H:i');
                                })->unique()->values();

                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                            @endphp

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-matrix">
                                    <thead>
                                        <tr style="background: #f4f4f4; color: #333; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                                            <th style="text-align: left;">Day / Time</th>
                                            @foreach($timeSlots as $time)
                                                <th>{{ $time }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($days as $day)
                                            <tr>
                                                <td><span class="text-blue">{{ $day }}</span></td>
                                                @foreach($timeSlots as $time)
                                                    @php
                                                        // Find slot matching current day and time block
                                                        $matchedSlot = $timetables->first(function($slot) use ($day, $time) {
                                                            $slotTime = \Carbon\Carbon::parse($slot->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($slot->end_time)->format('H:i');
                                                            return $slot->day === $day && $slotTime === $time;
                                                        });
                                                    @endphp
                                                    <td>
                                                        @if($matchedSlot)
                                                            <span class="slot-subject">{{ $matchedSlot->subject?->subject_name ?? 'N/A' }}</span>
                                                            <span class="slot-teacher">{{ $matchedSlot->teacher?->name ?? 'Unassigned' }}</span>
                                                        @else
                                                            <span class="empty-slot">—</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>
        
        @include('layouts.footer')
    </div>

    @include('components.scripts')
</body>
</html>