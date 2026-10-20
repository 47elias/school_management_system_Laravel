{{-- resources/views/teachers/dashboard.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Teacher Dashboard')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
                <span>Teacher Dashboard</span>
                @if($activeTerm)
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 font-extrabold border border-indigo-200 dark:border-indigo-800/50 uppercase tracking-wide">
                        {{ $activeTerm->term_name ?? 'Active Term' }}
                    </span>
                @endif
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Welcome back, <strong class="text-slate-700 dark:text-slate-200 font-bold">{{ auth()->user()->name }}</strong>.
                Here are the classes and subjects assigned to you.
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/60">
                <i class="fa-solid fa-calendar-day text-indigo-500 mr-2"></i>{{ date('l, d M Y') }}
            </span>
        </div>
    </div>
@endsection

@section('content')
@php
    $formClass   = $assignedClass ?? $myClass ?? null;
    $myClasses   = $myClasses ?? collect();
    $classCount  = $myClasses->count();
@endphp

<div class="space-y-6">

    {{-- Hero: summary of assigned classes --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-700 via-indigo-600 to-violet-700 text-white p-6 sm:p-8 shadow-md">
        <div class="relative z-10 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-indigo-100 text-xs font-bold">
                    <i class="fa-solid fa-graduation-cap"></i> Teacher Dashboard
                </span>
                @if($formClass)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 text-xs font-black">
                        <i class="fa-solid fa-chalkboard-user text-emerald-400"></i>
                        Form Class: {{ $formClass->class_name }}
                    </span>
                @endif
            </div>

            <h2 class="text-2xl sm:text-3xl font-black tracking-tight">
                @if($classCount > 0)
                    You are assigned to {{ $classCount }} {{ \Illuminate\Support\Str::plural('class', $classCount) }}
                @else
                    No class assigned yet
                @endif
            </h2>

            @if($classCount > 0)
                <div class="flex flex-wrap gap-2 pt-1">
                    @foreach($myClasses as $item)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $item->is_form_teacher ? 'bg-emerald-400/20 border-emerald-300/40 text-emerald-100' : 'bg-white/10 border-white/20 text-indigo-50' }}">
                            {{ $item->class_name }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-indigo-100 max-w-xl">
                    No class or subject has been assigned to you yet. Please contact the school administrator.
                </p>
            @endif
        </div>
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-white/5 blur-2xl pointer-events-none"></div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $stats = [
                ['label' => 'My Classes',     'value' => $classCount,                 'icon' => 'fa-school',         'color' => 'teal'],
                ['label' => 'My Subjects',    'value' => $assignedSubjectsCount ?? 0, 'icon' => 'fa-book-bookmark',  'color' => 'indigo'],
                ['label' => 'Total Students', 'value' => $totalStudentsCount ?? 0,    'icon' => 'fa-user-graduate',  'color' => 'emerald'],
                ['label' => 'Exams This Term','value' => $examsRecordedCount ?? 0,    'icon' => 'fa-pen-to-square',  'color' => 'purple'],
            ];
        @endphp
        @foreach($stats as $stat)
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 p-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $stat['label'] }}</p>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ $stat['value'] }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-{{ $stat['color'] }}-50 dark:bg-{{ $stat['color'] }}-950/60 text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400 border border-{{ $stat['color'] }}-200 dark:border-{{ $stat['color'] }}-800/50 flex items-center justify-center text-lg">
                        <i class="fa-solid {{ $stat['icon'] }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ASSIGNED CLASSES --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/90 flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-chalkboard-user text-indigo-500 text-base"></i>
                <span>My Assigned Classes</span>
            </h3>
            @if(Route::has('teacher.subjects'))
                <a href="{{ route('teacher.subjects') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">All allocations</a>
            @endif
        </div>

        @if($classCount > 0)
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($myClasses as $item)
                    @php $cls = $item; @endphp
                    <div class="rounded-2xl border {{ $item->is_form_teacher ? 'border-emerald-300 dark:border-emerald-700/60 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-slate-200 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-900/30' }} p-4 flex flex-col">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="text-lg font-black text-slate-800 dark:text-slate-100 truncate">{{ $cls->class_name }}</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $cls->gradeLevel->name ?? 'Grade N/A' }}
                                    @if($cls->room_number) &bull; Room {{ $cls->room_number }} @endif
                                </p>
                            </div>
                            @if($item->is_form_teacher)
                                <span class="shrink-0 px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 text-[10px] font-extrabold uppercase border border-emerald-200 dark:border-emerald-800/50">
                                    Form Teacher
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300">
                            <i class="fa-solid fa-users text-slate-400"></i>
                            {{ $item->student_count }} {{ \Illuminate\Support\Str::plural('student', $item->student_count) }}
                            @if($cls->capacity)
                                <span class="text-slate-400 font-medium">/ {{ $cls->capacity }}</span>
                            @endif
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5 flex-1">
                            @forelse($item->subjects as $subjectName)
                                <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-[11px] font-bold border border-indigo-200/60 dark:border-indigo-800/50">
                                    {{ $subjectName }}
                                </span>
                            @empty
                                <span class="text-[11px] text-slate-400 italic">No subject allocated here (form teacher only)</span>
                            @endforelse
                        </div>

                        @if(Route::has('teacher.my_class'))
                            <a href="{{ route('teacher.my_class') }}" class="mt-4 inline-flex items-center justify-center px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors">
                                <i class="fa-solid fa-users mr-1.5"></i> View Class List
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-12 px-4 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700/60 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-folder-open text-xl"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">No classes assigned</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    Please contact the school administrator to be assigned a class or subject.
                </p>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Subject allocations --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/90">
                <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-book-bookmark text-indigo-500 text-base"></i>
                    <span>My Subjects</span>
                </h3>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($assignedSubjects ?? [] as $assignment)
                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="px-2.5 py-1 rounded-lg bg-teal-100 dark:bg-teal-950/80 text-teal-700 dark:text-teal-300 font-extrabold text-xs border border-teal-200 dark:border-teal-800/50">
                                <i class="fa-solid fa-school text-[11px] mr-1"></i>{{ $assignment->class_label }}
                            </span>
                            <h4 class="font-bold text-sm text-slate-800 dark:text-slate-100">
                                {{ $assignment->subject->subject_name ?? 'Subject' }}
                            </h4>
                            @if(!empty($assignment->subject->subject_code))
                                <span class="font-mono text-[10px] px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold border border-slate-200 dark:border-slate-600">
                                    {{ $assignment->subject->subject_code }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if(Route::has('teacher.activities.index'))
                                <a href="{{ route('teacher.activities.index') }}" class="px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 text-xs font-bold border border-indigo-200/50 dark:border-indigo-800/50">
                                    <i class="fa-solid fa-list-check mr-1"></i> Activities
                                </a>
                            @endif
                            @if(Route::has('teacher.exams.index'))
                                <a href="{{ route('teacher.exams.index') }}" class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 text-xs font-bold border border-emerald-200/50 dark:border-emerald-800/50">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Exams
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 px-4 text-center text-xs text-slate-500 dark:text-slate-400">
                        No subjects allocated to you yet.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Quick actions + recent exams --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-xs p-5 space-y-3">
                <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100 border-b border-slate-100 dark:border-slate-700/60 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-bolt text-amber-500"></i><span>Quick Operations</span>
                </h3>
                @php
                    $links = [
                        ['teacher.activities.index', 'Record Activity Marks', 'fa-plus-circle'],
                        ['teacher.exams.index',      'Enter Exam Scores',     'fa-pen-nib'],
                        ['teacher.my_class',         'My Students',           'fa-address-book'],
                    ];
                @endphp
                @foreach($links as [$routeName, $label, $icon])
                    @if(Route::has($routeName))
                        <a href="{{ route($routeName) }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 hover:bg-indigo-50/80 dark:hover:bg-indigo-950/40 border border-slate-200/80 dark:border-slate-700/60 text-xs font-bold text-slate-700 dark:text-slate-200">
                            <span class="flex items-center gap-3"><i class="fa-solid {{ $icon }} text-indigo-500"></i>{{ $label }}</span>
                            <i class="fa-solid fa-chevron-right text-slate-400 text-[10px]"></i>
                        </a>
                    @endif
                @endforeach
            </div>

            @if(($recentExams ?? collect())->isNotEmpty())
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-xs p-5">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100 border-b border-slate-100 dark:border-slate-700/60 pb-3 mb-3">Recent Exams</h3>
                    <ul class="space-y-2.5">
                        @foreach($recentExams as $exam)
                            <li class="text-xs">
                                <p class="font-bold text-slate-700 dark:text-slate-200">{{ $exam->exam_name }}</p>
                                <p class="text-slate-500 dark:text-slate-400">
                                    {{ $exam->subject->subject_name ?? 'Subject' }} &bull; {{ $exam->schoolClass->class_name ?? ($exam->subjectAssignment?->grade ? $exam->subjectAssignment->grade . ' · All Streams' : 'All Streams') }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
