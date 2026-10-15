{{-- resources/views/timetable/show.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Class Timetable | ' . ($schoolClass->class_name ?? 'Matrix'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4 print:hidden">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">
                Timetable Matrix: {{ $schoolClass->class_name }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Room: <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $schoolClass->room_number ?? 'N/A' }}</span>
            </p>
        </div>

        <div class="flex items-center space-x-3">
            {{-- Class Switcher --}}
            <div class="flex items-center space-x-2">
                <label class="text-xs font-bold uppercase text-slate-400">Switch Class:</label>
                <select onchange="if(this.value) window.location.href='/admin/timetable/class/' + this.value;"
                        class="px-3 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $c->id == $schoolClass->id ? 'selected' : '' }}>
                            {{ $c->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button onclick="window.print()" 
                    class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-print mr-2"></i> Print Grid
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Printable Header --}}
        <div class="hidden print:block text-center border-b-2 border-slate-900 pb-4 mb-6">
            <h2 class="text-2xl font-black uppercase text-slate-900">{{ env('SCHOOL_NAME', 'SCHOOL MANAGEMENT SYSTEM') }}</h2>
            <h4 class="text-base font-bold text-slate-700 mt-1">Weekly Class Timetable: {{ $schoolClass->class_name }}</h4>
            <p class="text-xs text-slate-500 font-mono mt-0.5">Room: {{ $schoolClass->room_number ?? 'N/A' }}</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors print:border-none print:shadow-none">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 print:hidden">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-table-cells text-indigo-500 mr-2.5"></i> Weekly Schedule Grid
                </h3>
            </div>

            <div class="p-6">
                @if($timetables->isEmpty())
                    <div class="p-8 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/50 text-amber-800 dark:text-amber-200 text-center">
                        <i class="fa-solid fa-triangle-exclamation text-3xl mb-2 text-amber-500 block"></i>
                        <span class="font-bold text-sm block">No Timetable Slots Scheduled</span>
                        <span class="text-xs text-amber-600 dark:text-amber-400 mt-1 block">
                            No timetable slots have been scheduled or generated for <strong>{{ $schoolClass->class_name }}</strong> yet.
                        </span>
                    </div>
                @else
                    @php
                        $timeSlots = $timetables->sortBy('start_time')->map(function($slot) {
                            return \Carbon\Carbon::parse($slot->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($slot->end_time)->format('H:i');
                        })->unique()->values();

                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                    @endphp

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs text-center">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th scope="col" class="px-4 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider min-w-[100px]">
                                        Day / Time
                                    </th>
                                    @foreach($timeSlots as $time)
                                        <th scope="col" class="px-4 py-3.5 font-mono font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider min-w-[130px]">
                                            {{ $time }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                                @foreach($days as $day)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-4 py-4 text-left font-bold text-indigo-600 dark:text-indigo-400 bg-slate-50/50 dark:bg-slate-900/30">
                                            {{ $day }}
                                        </td>
                                        @foreach($timeSlots as $time)
                                            @php
                                                $matchedSlot = $timetables->first(function($slot) use ($day, $time) {
                                                    $slotTime = \Carbon\Carbon::parse($slot->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($slot->end_time)->format('H:i');
                                                    return $slot->day === $day && $slotTime === $time;
                                                });
                                            @endphp
                                            <td class="px-3 py-3 vertical-align-middle">
                                                @if($matchedSlot)
                                                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/60 space-y-1">
                                                        <strong class="block text-slate-800 dark:text-slate-100 font-bold leading-tight">
                                                            {{ $matchedSlot->subject?->subject_name ?? $matchedSlot->special_type ?? 'N/A' }}
                                                        </strong>
                                                        <span class="block text-[10px] text-slate-400 italic">
                                                            {{ $matchedSlot->teacher?->name ?? 'Unassigned' }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-slate-300 dark:text-slate-600 font-serif italic text-base">—</span>
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

    </div>
@endsection