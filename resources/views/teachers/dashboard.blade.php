{{-- resources/views/teachers/dashboard.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Teacher Dashboard')

@section('header')
    <div class="w-full"><h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Teacher Dashboard</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Your teaching overview, quick actions and profile.</p></div>
@endsection

@section('content')
@php
    $formClass   = $assignedClass ?? $myClass ?? null;
    $myClasses   = $myClasses ?? collect();
    $classCount  = $myClasses->count();
@endphp

<div class="space-y-6">

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        {{-- Welcome and teaching overview --}}
        <section class="xl:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-950 via-indigo-800 to-violet-700 text-white p-6 sm:p-8 shadow-lg shadow-indigo-950/10">
            <div class="relative z-10 space-y-5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold text-indigo-100"><i class="fa-solid fa-sun text-amber-300"></i>{{ now()->format('l, d F Y') }}</span>
                    @if($activeTerm)<span class="inline-flex items-center gap-2 rounded-full border border-emerald-300/30 bg-emerald-400/15 px-3 py-1.5 text-xs font-bold text-emerald-100"><i class="fa-solid fa-calendar-check"></i>{{ $activeTerm->term_name }}{{ $activeTerm->academic_year ? ' · ' . $activeTerm->academic_year : '' }}</span>@endif
                </div>
                <div>
                    <p class="text-sm font-semibold text-indigo-200">Your teaching at a glance</p>
                    <h2 class="mt-1 text-3xl sm:text-4xl font-black tracking-tight">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ \Illuminate\Support\Str::of($teacher->name)->before(' ') }}.</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-indigo-100">Your classes, subjects and work for this term are together here. Use the shortcuts to record class activities, enter exam scores or check your student lists.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @forelse($myClasses->take(5) as $item)<span class="rounded-xl border {{ $item->is_form_teacher ? 'border-emerald-300/30 bg-emerald-400/15 text-emerald-100' : 'border-white/15 bg-white/10 text-indigo-50' }} px-3 py-2 text-xs font-bold"><i class="fa-solid {{ $item->is_form_teacher ? 'fa-chalkboard-user' : 'fa-school' }} mr-1.5 opacity-80"></i>{{ $item->class_name }}</span>@empty<span class="text-sm text-indigo-100">No classes assigned yet. Contact the school administrator for your allocations.</span>@endforelse
                    @if($myClasses->count() > 5)<a href="{{ route('teacher.subjects') }}" class="rounded-xl border border-white/15 bg-white/10 px-3 py-2 text-xs font-bold text-white">+{{ $myClasses->count() - 5 }} more</a>@endif
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    <a href="{{ route('teacher.activities.index') }}" class="rounded-xl bg-white px-4 py-2.5 text-xs font-extrabold text-indigo-900 shadow-sm hover:bg-indigo-50"><i class="fa-solid fa-list-check mr-1.5"></i>Record class activity</a>
                    <a href="{{ route('teacher.exams.index') }}" class="rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-xs font-bold text-white hover:bg-white/15"><i class="fa-solid fa-pen-to-square mr-1.5"></i>Open exams</a>
                </div>
            </div>
            <div class="pointer-events-none absolute -right-16 -bottom-24 h-72 w-72 rounded-full border-[36px] border-white/5"></div>
            <div class="pointer-events-none absolute right-20 -top-24 h-52 w-52 rounded-full bg-violet-400/20 blur-3xl"></div>
        </section>

        {{-- Teacher profile snapshot --}}
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700/70 bg-gradient-to-br from-white to-indigo-50/70 dark:from-slate-800 dark:to-slate-800 p-5 sm:p-6 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-lg font-black text-white shadow-md shadow-indigo-600/20">{{ $teacherInitials ?: 'T' }}</div>
                    <div class="min-w-0"><p class="text-[10px] font-extrabold uppercase tracking-[.16em] text-indigo-600 dark:text-indigo-300">Teacher profile</p><h3 class="mt-1 truncate text-lg font-black text-slate-900 dark:text-white">{{ $teacher->name }}</h3><p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ ucfirst($teacher->role ?? 'Teacher') }} account</p></div>
                </div>
                <a href="{{ route('teacher.profile') }}" aria-label="Edit teacher profile" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 dark:border-slate-600 bg-white/80 dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-slate-600"><i class="fa-solid fa-pen"></i></a>
            </div>
            <div class="mt-5 space-y-3 border-t border-slate-200/80 dark:border-slate-700 pt-4">
                <div class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-300"><i class="fa-solid fa-id-badge text-xs"></i></span><div class="min-w-0"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Staff number</p><p class="truncate text-xs font-bold text-slate-700 dark:text-slate-200">{{ $teacher->employee_id ?: ($teacher->ec_number ?: 'Not added') }}</p></div></div>
                <div class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-950/70 text-sky-600 dark:text-sky-300"><i class="fa-solid fa-envelope text-xs"></i></span><div class="min-w-0"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Email address</p><p class="truncate text-xs font-bold text-slate-700 dark:text-slate-200">{{ $teacher->email ?: 'Not added' }}</p></div></div>
                <div class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-300"><i class="fa-solid fa-phone text-xs"></i></span><div class="min-w-0"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Phone number</p><p class="truncate text-xs font-bold text-slate-700 dark:text-slate-200">{{ $teacher->phone_number ?: 'Add your phone number' }}</p></div></div>
            </div>
            <a href="{{ route('teacher.profile') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/40 px-4 py-2.5 text-xs font-extrabold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-950/70">View and update profile <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
        </section>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
        @php
            $stats = [
                ['label' => 'My Classes',     'value' => $classCount,                 'icon' => 'fa-school',         'color' => 'teal'],
                ['label' => 'My Subjects',    'value' => $assignedSubjectsCount ?? 0, 'icon' => 'fa-book-bookmark',  'color' => 'indigo'],
                ['label' => 'Total Students', 'value' => $totalStudentsCount ?? 0,    'icon' => 'fa-user-graduate',  'color' => 'emerald'],
                ['label' => 'Activities This Term','value' => $activitiesCount ?? 0, 'icon' => 'fa-list-check', 'color' => 'amber'],
                ['label' => 'Exams This Term','value' => $examCount ?? 0, 'hint' => ($examsRecordedCount ?? 0) . ' marks entered', 'icon' => 'fa-pen-to-square', 'color' => 'purple'],
            ];
        @endphp
        @foreach($stats as $stat)
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 p-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $stat['label'] }}</p>
                        <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ $stat['value'] }}</h3>
                        @if(!empty($stat['hint']))<p class="mt-1 text-[10px] font-semibold text-slate-500 dark:text-slate-400">{{ $stat['hint'] }}</p>@endif
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
