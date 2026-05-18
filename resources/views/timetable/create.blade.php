<!DOCTYPE html>
<html>
<head>
    <title>Add Timetable Slot | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('components.adminlte')
    <style>
        .box { border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid #00a65a; }
        .form-group label { font-weight: 600; color: #555; }
        .teacher-readonly { background-color: #f4f4f4 !important; font-weight: bold; color: #00a65a; }
        .all-classes-alert {
            padding: 7px 12px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            color: #4338ca;
            border-radius: 4px;
            font-weight: 600;
        }
        .time-badge { background: #f39c12; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; }
        .time-badge input { color: #333; width: 50px; border: none; border-radius: 2px; padding: 0 3px; }
        .auto-calc-active { border-color: #00a65a; background-color: #f0fff4; transition: all 0.5s; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    <i class="fa fa-calendar-plus-o text-success"></i> Create Schedule Slot
                    <small>Timetable Planning</small>
                </h1>
            </section>

            <section class="content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-ban"></i> Conflict Detected!</h4>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Slot Details</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-sm btn-default" data-toggle="modal" data-target="#manageGlobalSlots">
                                <i class="fa fa-trash"></i> DELETE GLOBAL BREAKS
                            </button>
                            <span class="time-badge">
                                <i class="fa fa-clock-o"></i> Default Duration:
                                <input type="number" id="manual_duration" value="{{ request('dur', 40) }}" min="1"> mins
                            </span>
                            <button type="button" id="toggleSpecial" class="btn btn-sm btn-warning" style="margin-left:10px">
                                <i class="fa fa-coffee"></i> ADD BREAK / LUNCH (ALL CLASSES)
                            </button>
                        </div>
                    </div>

                    <form action="{{ route('timetable.store') }}" method="POST">
                        @csrf
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Target Audience</label>
                                    <div id="class_select_wrapper">
                                        <select id="class_id" name="class_id" class="form-control select2">
                                            <option value="">-- Choose Class --</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="all_classes_wrapper" style="display:none;">
                                        <div class="all-classes-alert">
                                            <i class="fa fa-users"></i> Global Slot: Applying to ALL Classes
                                        </div>
                                    </div>
                                    <input type="hidden" name="apply_to_all_classes" id="apply_to_all_classes" value="0">
                                </div>

                                <div class="col-md-6 form-group" id="subject_container">
                                    <label>Select Subject</label>
                                    <select id="subject_id" name="subject_id" class="form-control select2" required disabled>
                                        <option value="">Select class first...</option>
                                    </select>
                                </div>

                                <div class="col-md-6 form-group" id="special_container" style="display:none;">
                                    <label>Break/Lunch Label (Visible on Timetable)</label>
                                    <select id="special_type" name="special_type" class="form-control">
                                        <option value="Assembly">Assembly</option>
                                        <option value="Short Break">Short Break</option>
                                        <option value="Morning Break">Morning Break</option>
                                        <option value="Tea Break">Tea Break</option>
                                        <option value="Lunch">Lunch</option>
                                        <option value="Sports/Club">Sports/Club</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row" id="teacher_row">
                                <div class="col-md-8 form-group">
                                    <label>Assigned Teacher</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-user-circle"></i></span>
                                        <input type="text" id="teacher_display" class="form-control teacher-readonly" readonly placeholder="Assigned teacher will appear here...">
                                    </div>
                                    <input type="hidden" id="teacher_id" name="teacher_id">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Room Number (Optional)</label>
                                    <input type="text" name="room_number" class="form-control" placeholder="e.g. Room 101">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label>Day of Week</label>
                                    <div id="day_selector_container">
                                        <select name="day" id="day_select" class="form-control" required>
                                            <option>Monday</option>
                                            <option>Tuesday</option>
                                            <option>Wednesday</option>
                                            <option>Thursday</option>
                                            <option>Friday</option>
                                        </select>
                                    </div>
                                    <div id="all_days_label" style="display:none; padding-top: 7px;">
                                        <span class="label label-info"><i class="fa fa-repeat"></i> FULL WEEK (MON-FRI)</span>
                                    </div>
                                    <input type="hidden" name="apply_all_days" id="apply_all_days" value="0">
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Select Period</label>
                                    <select id="period_number" name="period_number" class="form-control">
                                        <option value="">-- Choose Period --</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">Period {{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Start Time</label>
                                    <input type="time" name="start_time" id="start_time" class="form-control" required value="07:30">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>End Time</label>
                                    <input type="time" name="end_time" id="end_time" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <button type="submit" class="btn btn-success btn-flat">
                                <i class="fa fa-save"></i> SAVE TO TIMETABLE
                            </button>
                            <a href="{{ route('timetable.index') }}" class="btn btn-default btn-flat">CANCEL</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <div class="modal fade" id="manageGlobalSlots" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title text-danger"><i class="fa fa-trash"></i> Delete Global Break/Lunch Slots</h4>
                </div>
                <form action="{{ route('timetable.bulk_delete_special') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p>This action will remove the selected break type from <b>ALL classes</b> on the timetable.</p>
                        <div class="form-group">
                            <label>Identify Break to Remove:</label>
                            <select name="special_label" class="form-control" required>
                                <option value="Assembly">Assembly</option>
                                <option value="Short Break">Short Break</option>
                                <option value="Morning Break">Morning Break</option>
                                <option value="Tea Break">Tea Break</option>
                                <option value="Lunch">Lunch</option>
                                <option value="Sports/Club">Sports/Club</option>
                            </select>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fa fa-info-circle"></i> This only deletes slots where no specific teacher is assigned (Global Breaks).
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you absolutely sure?')">CONFIRM BULK DELETE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('layouts.footer')
    @include('components.scripts')

    <script>
        $(document).ready(function() {
            let isSpecial = false;
            let baseStartTime = "07:30";

            const pad = (n) => String(n).padStart(2, '0');

            function updateTimes() {
                const period = parseInt($('#period_number').val());
                const periodDuration = parseInt($('#manual_duration').val()) || 0;

                if (!period || periodDuration <= 0) return;

                let [hours, mins] = baseStartTime.split(':').map(Number);
                let totalMinutesStart = (hours * 60) + mins + ((period - 1) * periodDuration);
                let startH = Math.floor(totalMinutesStart / 60) % 24;
                let startM = totalMinutesStart % 60;

                let totalMinutesEnd = totalMinutesStart + periodDuration;
                let endH = Math.floor(totalMinutesEnd / 60) % 24;
                let endM = totalMinutesEnd % 60;

                $('#start_time').val(`${pad(startH)}:${pad(startM)}`).addClass('auto-calc-active');
                $('#end_time').val(`${pad(endH)}:${pad(endM)}`).addClass('auto-calc-active');

                setTimeout(() => { $('.form-control').removeClass('auto-calc-active'); }, 800);
            }

            $('#period_number, #manual_duration').on('change keyup', updateTimes);

            $('#start_time').on('change', function() {
                const period = parseInt($('#period_number').val()) || 1;
                const duration = parseInt($('#manual_duration').val()) || 0;
                const manualStart = $(this).val();

                let [h, m] = manualStart.split(':').map(Number);
                let totalMins = (h * 60) + m - ((period - 1) * duration);

                let baseH = Math.floor(totalMins / 60) % 24;
                let baseM = totalMins % 60;
                baseStartTime = `${pad(baseH)}:${pad(baseM)}`;

                let endTotalMins = (h * 60) + m + duration;
                $('#end_time').val(`${pad(Math.floor(endTotalMins/60)%24)}:${pad(endTotalMins%60)}`);
            });

            $('#toggleSpecial').on('click', function() {
                isSpecial = !isSpecial;
                if(isSpecial) {
                    $(this).html('<i class="fa fa-book"></i> ADD SINGLE SUBJECT');
                    $('#class_select_wrapper').hide();
                    $('#all_classes_wrapper').show();
                    $('#apply_to_all_classes').val('1');
                    $('#subject_container').hide();
                    $('#subject_id').prop('required', false).prop('disabled', true);
                    $('#special_container').show();
                    $('#teacher_row').hide();
                    $('#day_selector_container').hide();
                    $('#all_days_label').show();
                    $('#apply_all_days').val('1');
                } else {
                    $(this).html('<i class="fa fa-coffee"></i> ADD BREAK / LUNCH (ALL CLASSES)');
                    $('#class_select_wrapper').show();
                    $('#all_classes_wrapper').hide();
                    $('#apply_to_all_classes').val('0');
                    $('#subject_container').show();
                    $('#subject_id').prop('required', true).prop('disabled', false);
                    $('#special_container').hide();
                    $('#teacher_row').show();
                    $('#day_selector_container').show();
                    $('#all_days_label').hide();
                    $('#apply_all_days').val('0');
                }
            });

            $('#class_id').on('change', function() {
                const classId = $(this).val();
                if (!isSpecial && classId) {
                    $('#subject_id').html('<option value="">Loading...</option>').prop('disabled', true);
                    fetch(`/api/classes/${classId}/subjects`)
                        .then(response => response.json())
                        .then(data => {
                            $('#subject_id').html('<option value="">-- Select Subject --</option>');
                            data.forEach(sub => {
                                $('#subject_id').append(`<option value="${sub.subject_id}" data-teacher-id="${sub.teacher_id}" data-teacher-name="${sub.teacher_name}">${sub.subject_name}</option>`);
                            });
                            $('#subject_id').prop('disabled', false);
                        });
                }
            });

            $('#subject_id').on('change', function() {
                const selected = $(this).find(':selected');
                if (selected.val()) {
                    $('#teacher_display').val(selected.data('teacher-name'));
                    $('#teacher_id').val(selected.data('teacher-id'));
                }
            });
        });
    </script>
</body>
</html>
