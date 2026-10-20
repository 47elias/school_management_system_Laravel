{{-- resources/views/timetable/create.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Create Schedule Slot | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div x-data="{ showDeleteModal: false }" class="flex flex-col md:flex-row md:items-center md:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Create Schedule Slot</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Timetable planning and period allocations</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" @click="showDeleteModal = true"
                    class="inline-flex items-center px-3.5 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 text-xs font-bold transition-all border border-rose-200 dark:border-rose-800/50">
                <i class="fa-solid fa-trash-can mr-2"></i> Delete Global Breaks
            </button>

            <div class="inline-flex items-center px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/50 text-amber-700 dark:text-amber-400 text-xs font-bold">
                <i class="fa-solid fa-clock mr-2"></i> Duration:
                <input type="number" id="manual_duration" value="{{ request('dur', 40) }}" min="1" 
                       class="w-12 ml-1.5 px-1.5 py-0.5 text-center font-mono font-bold rounded-lg bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-500">
                <span class="ml-1">mins</span>
            </div>

            <button type="button" id="toggleSpecial" 
                    class="inline-flex items-center px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-mug-hot mr-2"></i> ADD BREAK / LUNCH (ALL CLASSES)
            </button>

            <a href="{{ Route::has('timetable.index') ? route('timetable.index') : url('/timetable') }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ showDeleteModal: false }" class="space-y-6">

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-ban text-rose-600 dark:text-rose-400 text-lg"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Conflict Detected!</h4>
                        <p class="text-xs font-medium mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        {{-- Form Container --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-calendar-plus text-emerald-500 mr-2.5"></i> Slot Details
                </h3>
            </div>

            <form action="{{ Route::has('timetable.store') ? route('timetable.store') : url('/timetable') }}" method="POST" class="p-6 space-y-6">
                @csrf

                {{-- Row 1: Target Audience & Subject / Special Label --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                            Target Audience <span class="text-rose-500">*</span>
                        </label>
                        <div id="class_select_wrapper">
                            <select id="class_id" name="class_id" class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">-- Choose Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}"
                                            data-class-teacher-id="{{ $class->teacher_id ?? '' }}"
                                            data-class-teacher-name="{{ $class->teacher->name ?? 'No Class Teacher Assigned' }}">
                                        {{ $class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="all_classes_wrapper" style="display:none;" class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/50 text-indigo-700 dark:text-indigo-300 text-xs font-bold flex items-center">
                            <i class="fa-solid fa-users mr-2 text-indigo-500"></i> Global Slot: Applying to ALL Classes
                        </div>
                        <input type="hidden" name="apply_to_all_classes" id="apply_to_all_classes" value="0">
                    </div>

                    <div id="subject_container">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                            Select Subject <span class="text-rose-500">*</span>
                        </label>
                        <select id="subject_id" name="subject_id" required disabled
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-60">
                            <option value="">Select class first...</option>
                        </select>
                    </div>

                    <div id="special_container" style="display:none;">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                            Break / Lunch Label (Visible on Timetable) <span class="text-rose-500">*</span>
                        </label>
                        <select id="special_type" name="special_type" 
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="Assembly">Assembly</option>
                            <option value="Short Break">Short Break</option>
                            <option value="Morning Break">Morning Break</option>
                            <option value="Tea Break">Tea Break</option>
                            <option value="Lunch">Lunch</option>
                            <option value="Sports/Club">Sports/Club</option>
                        </select>
                    </div>
                </div>

                {{-- Row 2: Assigned Teacher & Room --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6" id="teacher_row">
                    <div class="md:col-span-8">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                            Assigned Teacher
                        </label>
                        <div class="relative rounded-xl">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <input type="text" id="teacher_display" readonly placeholder="Assigned teacher will appear here..."
                                   class="w-full pl-10 pr-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 cursor-not-allowed">
                        </div>
                        <input type="hidden" id="teacher_id" name="teacher_id">
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                            Room Number (Optional)
                        </label>
                        <input type="text" name="room_number" placeholder="e.g. Room 101"
                               class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                {{-- Row 3: Day, Period, Start & End Times --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                            Day of Week <span class="text-rose-500">*</span>
                        </label>
                        <div id="day_selector_container">
                            <select name="day" id="day_select" required
                                    class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option>Monday</option>
                                <option>Tuesday</option>
                                <option>Wednesday</option>
                                <option>Thursday</option>
                                <option>Friday</option>
                            </select>
                        </div>
                        <div id="all_days_label" style="display:none;" class="pt-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/50">
                                <i class="fa-solid fa-rotate mr-1.5"></i> FULL WEEK (MON-FRI)
                            </span>
                        </div>
                        <input type="hidden" name="apply_all_days" id="apply_all_days" value="0">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                            Select Period
                        </label>
                        <select id="period_number" name="period_number" 
                                class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Choose Period --</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">Period {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                            Start Time <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" name="start_time" id="start_time" required value="07:30"
                               class="w-full px-3.5 py-2.5 text-xs font-mono font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                            End Time <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" name="end_time" id="end_time" required
                               class="w-full px-3.5 py-2.5 text-xs font-mono font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center space-x-3">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> SAVE TO TIMETABLE
                    </button>
                    <a href="{{ Route::has('timetable.index') ? route('timetable.index') : url('/timetable') }}" 
                       class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                        CANCEL
                    </a>
                </div>
            </form>
        </div>

        {{-- Modal: Manage Global Breaks --}}
        <div x-show="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                <div @click="showDeleteModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-800 rounded-3xl shadow-2xl z-10 border border-slate-200 dark:border-slate-700">
                    <form action="{{ Route::has('timetable.bulk_delete_special') ? route('timetable.bulk_delete_special') : url('/timetable/bulk-delete-special') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('DELETE')

                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
                            <h3 class="text-base font-bold text-rose-600 dark:text-rose-400 flex items-center">
                                <i class="fa-solid fa-trash-can mr-2"></i> Delete Global Breaks
                            </h3>
                            <button type="button" @click="showDeleteModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i class="fa-solid fa-xmark text-base"></i>
                            </button>
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            This action will remove the selected break type from <strong class="text-slate-900 dark:text-white">ALL classes</strong> on the timetable.
                        </p>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Identify Break to Remove <span class="text-rose-500">*</span>
                            </label>
                            <select name="special_label" required 
                                    class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500">
                                <option value="Assembly">Assembly</option>
                                <option value="Short Break">Short Break</option>
                                <option value="Morning Break">Morning Break</option>
                                <option value="Tea Break">Tea Break</option>
                                <option value="Lunch">Lunch</option>
                                <option value="Sports/Club">Sports/Club</option>
                            </select>
                        </div>

                        <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/50 text-amber-800 dark:text-amber-300 text-xs flex items-start space-x-2">
                            <i class="fa-solid fa-circle-info text-amber-500 mt-0.5"></i>
                            <span>This only deletes slots where no specific teacher is assigned (Global Breaks).</span>
                        </div>

                        <div class="pt-3 flex items-center justify-end space-x-2 border-t border-slate-100 dark:border-slate-700/60">
                            <button type="button" @click="showDeleteModal = false" 
                                    class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                                Close
                            </button>
                            <button type="submit" onclick="return confirm('Are you absolutely sure?')"
                                    class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-sm">
                                CONFIRM BULK DELETE
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let isSpecial = false;
            let baseStartTime = "07:30";

            const pad = (n) => String(n).padStart(2, '0');

            const periodEl = document.getElementById('period_number');
            const durationEl = document.getElementById('manual_duration');
            const startTimeEl = document.getElementById('start_time');
            const endTimeEl = document.getElementById('end_time');

            function updateTimes() {
                const period = parseInt(periodEl.value);
                const periodDuration = parseInt(durationEl.value) || 0;

                if (!period || periodDuration <= 0) return;

                let [hours, mins] = baseStartTime.split(':').map(Number);
                let totalMinutesStart = (hours * 60) + mins + ((period - 1) * periodDuration);
                let startH = Math.floor(totalMinutesStart / 60) % 24;
                let startM = totalMinutesStart % 60;

                let totalMinutesEnd = totalMinutesStart + periodDuration;
                let endH = Math.floor(totalMinutesEnd / 60) % 24;
                let endM = totalMinutesEnd % 60;

                startTimeEl.value = `${pad(startH)}:${pad(startM)}`;
                endTimeEl.value = `${pad(endH)}:${pad(endM)}`;
            }

            periodEl.addEventListener('change', updateTimes);
            durationEl.addEventListener('input', updateTimes);

            startTimeEl.addEventListener('change', function() {
                const period = parseInt(periodEl.value) || 1;
                const duration = parseInt(durationEl.value) || 0;
                const manualStart = this.value;

                let [h, m] = manualStart.split(':').map(Number);
                let totalMins = (h * 60) + m - ((period - 1) * duration);

                let baseH = Math.floor(totalMins / 60) % 24;
                let baseM = totalMins % 60;
                baseStartTime = `${pad(baseH)}:${pad(baseM)}`;

                let endTotalMins = (h * 60) + m + duration;
                endTimeEl.value = `${pad(Math.floor(endTotalMins/60)%24)}:${pad(endTotalMins%60)}`;
            });

            // Toggle Special Break / Lunch
            const toggleSpecialBtn = document.getElementById('toggleSpecial');
            const classSelectWrapper = document.getElementById('class_select_wrapper');
            const allClassesWrapper = document.getElementById('all_classes_wrapper');
            const applyToAllClasses = document.getElementById('apply_to_all_classes');
            const subjectContainer = document.getElementById('subject_container');
            const subjectSelect = document.getElementById('subject_id');
            const specialContainer = document.getElementById('special_container');
            const teacherRow = document.getElementById('teacher_row');
            const teacherIdInput = document.getElementById('teacher_id');
            const teacherDisplayInput = document.getElementById('teacher_display');
            const daySelectorContainer = document.getElementById('day_selector_container');
            const allDaysLabel = document.getElementById('all_days_label');
            const applyAllDays = document.getElementById('apply_all_days');

            toggleSpecialBtn.addEventListener('click', function() {
                isSpecial = !isSpecial;
                if(isSpecial) {
                    this.innerHTML = '<i class="fa-solid fa-book mr-2"></i> ADD SINGLE SUBJECT';
                    classSelectWrapper.style.display = 'none';
                    allClassesWrapper.style.display = 'flex';
                    applyToAllClasses.value = '1';
                    subjectContainer.style.display = 'none';
                    subjectSelect.required = false;
                    subjectSelect.disabled = true;
                    subjectSelect.value = '';
                    specialContainer.style.display = 'block';
                    teacherRow.style.display = 'none';
                    teacherIdInput.value = '';
                    teacherDisplayInput.value = '';
                    daySelectorContainer.style.display = 'none';
                    allDaysLabel.style.display = 'block';
                    applyAllDays.value = '1';
                } else {
                    this.innerHTML = '<i class="fa-solid fa-mug-hot mr-2"></i> ADD BREAK / LUNCH (ALL CLASSES)';
                    classSelectWrapper.style.display = 'block';
                    allClassesWrapper.style.display = 'none';
                    applyToAllClasses.value = '0';
                    subjectContainer.style.display = 'block';
                    subjectSelect.required = true;
                    subjectSelect.disabled = false;
                    specialContainer.style.display = 'none';
                    teacherRow.style.display = 'grid';
                    daySelectorContainer.style.display = 'block';
                    allDaysLabel.style.display = 'none';
                    applyAllDays.value = '0';
                }
            });

            // AJAX Call to load subjects based on Class Selection
            const classSelect = document.getElementById('class_id');
            classSelect.addEventListener('change', function() {
                const classId = this.value;

                teacherDisplayInput.value = '';
                teacherIdInput.value = '';
                subjectSelect.innerHTML = '<option value="">-- Choose Subject --</option>';

                if (!isSpecial && classId) {
                    subjectSelect.innerHTML = '<option value="">Loading subjects...</option>';
                    subjectSelect.disabled = true;

                    fetch(`/api/classes/${classId}/subjects`)
                        .then(response => response.json())
                        .then(data => {
                            subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';

                            data.forEach(sub => {
                                let subjectId = sub.id || sub.subject_id;
                                let subjectName = sub.subject_name || sub.name;
                                let teacherId = sub.teacher_id || '';
                                let teacherName = sub.teacher_name || '';

                                const opt = document.createElement('option');
                                opt.value = subjectId;
                                opt.setAttribute('data-teacher-id', teacherId);
                                opt.setAttribute('data-teacher-name', teacherName);
                                opt.textContent = subjectName;
                                subjectSelect.appendChild(opt);
                            });

                            subjectSelect.disabled = false;
                        })
                        .catch(err => {
                            console.error("Failed to load subjects:", err);
                            subjectSelect.innerHTML = '<option value="">Error loading items</option>';
                        });
                }
            });

            // Detect Subject Change & Fallback to Class Teacher
            subjectSelect.addEventListener('change', function() {
                const selectedOpt = this.options[this.selectedIndex];
                const selectedClassOpt = classSelect.options[classSelect.selectedIndex];

                if (this.value) {
                    let subjectTeacherId = selectedOpt.getAttribute('data-teacher-id');
                    let subjectTeacherName = selectedOpt.getAttribute('data-teacher-name');

                    if (subjectTeacherId && subjectTeacherName) {
                        teacherDisplayInput.value = subjectTeacherName;
                        teacherIdInput.value = subjectTeacherId;
                    } else if(selectedClassOpt) {
                        teacherDisplayInput.value = selectedClassOpt.getAttribute('data-class-teacher-name') || '';
                        teacherIdInput.value = selectedClassOpt.getAttribute('data-class-teacher-id') || '';
                    }
                }
            });
        });
    </script>
@endsection
