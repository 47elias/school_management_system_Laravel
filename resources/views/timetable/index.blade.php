{{-- resources/views/timetable/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Class Timetable Management | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Timetable Management</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Schedules and period allocations across all active streams</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Jump to Class --}}
            <select onchange="if(this.value) window.location.href='/admin/timetable/class/' + this.value;" 
                    class="px-3 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                <option value="">-- Jump to Class View --</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                @endforeach
            </select>

            <button type="submit" form="generate-timetable"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-wand-magic-sparkles mr-2"></i> GENERATE ALL STREAMS
            </button>

            <a href="{{ Route::has('timetable.create') ? route('timetable.create') : url('/timetable/create') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-plus mr-2"></i> ADD SLOT
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ search: '' }" class="space-y-6">

        <form id="generate-timetable" action="{{ route('timetable.generate') }}" method="POST"
              onsubmit="return confirm('Generate schedules for all active streams? Existing timetable slots will be replaced.');"
              class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-5 space-y-5">
            @csrf
            <div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">School day setup</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Choose the daily lesson pattern and add assemblies or breaks. Activities are placed in every active stream and do not count as lesson periods.</p>
            </div>
            @if($errors->any())
                <div class="rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 p-3 text-xs text-rose-700 dark:text-rose-300">{{ $errors->first() }}</div>
            @endif
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Periods per day
                    <input type="number" name="periods_per_day" min="1" max="12" required value="{{ old('periods_per_day', $settings['periods_per_day']) }}" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                </label>
                <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">First period starts
                    <input type="time" name="day_start" required value="{{ old('day_start', $settings['day_start']) }}" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                </label>
                <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Period length (minutes)
                    <input type="number" name="period_duration" min="20" max="180" required value="{{ old('period_duration', $settings['period_duration']) }}" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                </label>
                <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Gap between periods (minutes)
                    <input type="number" name="period_gap" min="0" max="60" required value="{{ old('period_gap', $settings['period_gap']) }}" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                </label>
            </div>
            <div class="space-y-3" id="school-events">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Breaks and activities</h3>
                    <button type="button" id="add-school-event" class="text-xs font-bold text-indigo-600 hover:text-indigo-700"><i class="fa-solid fa-plus mr-1"></i>Add activity</button>
                </div>
                @php
                    $schoolEvents = old('events', $settings['events'] ?? []);
                @endphp
                @foreach($schoolEvents as $eventIndex => $event)
                    <div class="school-event grid grid-cols-1 sm:grid-cols-4 gap-3 items-end rounded-xl bg-slate-50 dark:bg-slate-900/50 p-3">
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Label
                            <input name="events[{{ $eventIndex }}][label]" required maxlength="40" value="{{ $event['label'] }}" placeholder="e.g. Assembly" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                        </label>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Place after period
                            <input name="events[{{ $eventIndex }}][after_period]" type="number" min="0" max="12" required value="{{ $event['after_period'] }}" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                            <span class="font-normal text-slate-400">0 = before first period</span>
                        </label>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Duration (minutes)
                            <input name="events[{{ $eventIndex }}][duration]" type="number" min="5" max="180" required value="{{ $event['duration'] }}" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                        </label>
                        <button type="button" class="remove-school-event justify-self-start sm:justify-self-end rounded-lg px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40">Remove</button>
                    </div>
                @endforeach
            </div>
        </form>

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
                    <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('error') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        {{-- Main Table Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-calendar-days text-indigo-500 mr-2.5"></i> Scheduled Slots Directory
                </h3>

                <div class="w-full sm:w-64">
                    <input type="text" x-model="search" placeholder="Filter timetable..." 
                           class="w-full px-3.5 py-1.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Class</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Day</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Time Slot</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Subject / Break</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Teacher</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Room</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($timetables ?? [] as $slot)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($slot->class_id)
                                    <a href="{{ Route::has('timetable.show') ? route('timetable.show', $slot->class_id) : url('/timetable/class/' . $slot->class_id) }}" 
                                       title="View Class Schedule"
                                       class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50 hover:underline">
                                        {{ $slot->schoolClass?->class_name ?? 'N/A' }} 
                                        <i class="fa-solid fa-arrow-up-right-from-square ml-1.5 text-[9px]"></i>
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                        Global / All
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800 dark:text-slate-100">
                                {{ $slot->day }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-600 dark:text-slate-300">
                                <i class="fa-regular fa-clock text-slate-400 mr-1.5"></i>
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-800 dark:text-slate-100">
                                {{ $slot->subject?->subject_name ?? $slot->special_type ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300">
                                {{ $slot->teacher?->name ?? 'Unassigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded font-mono font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    {{ $slot->room_number ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-1">
                                <a href="{{ Route::has('timetable.edit') ? route('timetable.edit', $slot->id) : url('/timetable/' . $slot->id . '/edit') }}" 
                                   class="p-1.5 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 inline-block transition-colors" 
                                   title="Edit Slot">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>

                                <form action="{{ Route::has('timetable.destroy') ? route('timetable.destroy', $slot->id) : url('/timetable/' . $slot->id) }}" 
                                      method="POST" onsubmit="return confirm('Are you sure you want to remove this timetable slot?');" class="inline-block">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" title="Delete Slot">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-calendar-xmark text-3xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                                No scheduled slots found. Click "Add Slot" or "Auto-Generate" to start.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('school-events');
        const addButton = document.getElementById('add-school-event');
        let nextIndex = {{ count($schoolEvents) }};

        addButton.addEventListener('click', () => {
            if (container.querySelectorAll('.school-event').length >= 8) return;
            const row = document.createElement('div');
            row.className = 'school-event grid grid-cols-1 sm:grid-cols-4 gap-3 items-end rounded-xl bg-slate-50 dark:bg-slate-900/50 p-3';
            row.innerHTML = `
                <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Label
                    <input name="events[${nextIndex}][label]" required maxlength="40" placeholder="e.g. Assembly" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                </label>
                <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Place after period
                    <input name="events[${nextIndex}][after_period]" type="number" min="0" max="12" required value="2" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                    <span class="font-normal text-slate-400">0 = before first period</span>
                </label>
                <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Duration (minutes)
                    <input name="events[${nextIndex}][duration]" type="number" min="5" max="180" required value="20" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                </label>
                <button type="button" class="remove-school-event justify-self-start sm:justify-self-end rounded-lg px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40">Remove</button>`;
            container.appendChild(row);
            nextIndex++;
        });

        container.addEventListener('click', (event) => {
            if (event.target.closest('.remove-school-event')) event.target.closest('.school-event').remove();
        });
    });
</script>
@endpush
