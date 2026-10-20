{{-- resources/views/students/show.blade.php --}}
@extends('layouts.admin.app')

@section('title', $student->surname . ', ' . $student->name . ' | Profile')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Student Profile</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Detailed Record & Academic History</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ Route::has('students.index') ? route('students.index') : url('/students/manage') }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Directory
            </a>
            <a href="{{ Route::has('students.edit') ? route('students.edit', $student->id) : url('/students/' . $student->id . '/edit') }}" 
               class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow">
                <i class="fa-solid fa-pen-to-square mr-2"></i> Edit Profile
            </a>
        </div>
    </div>
@endsection

@section('content')
    @php
        $avatarBg = ($student->gender == 'Male') ? '3b82f6' : 'ec4899';
        $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($student->name . ' ' . $student->surname) . "&background={$avatarBg}&color=fff&size=256";
        $streamName = $student->schoolClass->class_name ?? $student->grade ?? 'Unassigned';
    @endphp

    <div class="space-y-6" x-data="{ activeTab: '{{ request()->query('section') === 'academic' || request()->has('exam_page') || request()->has('activity_page') ? 'academic' : (request()->query('section') === 'financial' || request()->has('payment_page') ? 'financial' : 'profile') }}' }">

        {{-- Hero Header Card --}}
        <div class="bg-slate-900 rounded-3xl shadow-lg border border-slate-800 p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10 text-slate-100 pointer-events-none">
                <i class="fa-solid fa-graduation-cap text-[180px]"></i>
            </div>

            <div class="flex flex-col md:flex-row items-center md:items-start justify-between gap-6 relative z-10">
                {{-- Avatar & Core Info --}}
                <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6 text-center sm:text-left">
                    <div class="relative">
                        <img src="{{ $student->photo_path ? asset('storage/' . $student->photo_path) : $avatarUrl }}" 
                             x-on:error="$el.src = '{{ $avatarUrl }}'" 
                             alt="{{ $student->name }}" 
                             class="w-24 h-24 rounded-2xl object-cover border-2 border-indigo-500/40 shadow-lg bg-slate-800">
                        <span class="absolute -bottom-2 -right-2 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-indigo-500 text-white shadow-sm">
                            {{ $student->gender }}
                        </span>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <h2 class="text-2xl font-extrabold text-white tracking-tight">{{ $student->surname }}, {{ $student->name }}</h2>
                            @if(strtolower($student->status) == 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                    <i class="fa-solid fa-circle text-[6px] mr-1.5"></i> ACTIVE
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                    <i class="fa-solid fa-circle text-[6px] mr-1.5"></i> INACTIVE
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs text-slate-400 font-medium">
                            <span class="inline-flex items-center font-mono">
                                <i class="fa-solid fa-id-badge text-indigo-400 mr-1.5"></i> {{ $student->student_number }}
                            </span>
                            <span class="inline-flex items-center">
                                <i class="fa-solid fa-layer-group text-indigo-400 mr-1.5"></i> {{ $streamName }}
                            </span>
                            @if($student->term)
                                <span class="inline-flex items-center">
                                    <i class="fa-solid fa-calendar-days text-indigo-400 mr-1.5"></i> {{ $student->term->term_name }} ({{ $student->term->academic_year }})
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Links --}}
                <div class="flex items-center space-x-2">
                    @if(Route::has('students.financials'))
                        <a href="{{ route('students.financials', $student->id) }}" 
                           class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 border border-slate-700 transition-colors">
                            <i class="fa-solid fa-receipt text-emerald-400 mr-2"></i> Financials
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <nav role="tablist" aria-label="Student record sections" class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-700">
            <button type="button" role="tab" @click="activeTab = 'profile'" :aria-selected="activeTab === 'profile'" :class="activeTab === 'profile' ? 'border-indigo-600 text-indigo-700 dark:text-indigo-300' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'" class="border-b-2 px-4 py-3 text-sm font-semibold">Profile</button>
            <button type="button" role="tab" @click="activeTab = 'academic'" :aria-selected="activeTab === 'academic'" :class="activeTab === 'academic' ? 'border-indigo-600 text-indigo-700 dark:text-indigo-300' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'" class="border-b-2 px-4 py-3 text-sm font-semibold">Academic history</button>
            <button type="button" role="tab" @click="activeTab = 'financial'" :aria-selected="activeTab === 'financial'" :class="activeTab === 'financial' ? 'border-indigo-600 text-indigo-700 dark:text-indigo-300' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'" class="border-b-2 px-4 py-3 text-sm font-semibold">Financial record</button>
        </nav>

        <div x-show="activeTab === 'profile'">
        {{-- Profile details --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Column 1 & 2: Personal & Contact Overview --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Personal Information --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center mb-4 border-b border-slate-100 dark:border-slate-700/80 pb-3">
                        <i class="fa-solid fa-user text-indigo-500 mr-2.5"></i> Personal Information
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">National ID</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-1 block font-mono">
                                {{ $student->national_id ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Date of Birth</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-1 block">
                                {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M, Y') : 'N/A' }}
                                @if($student->date_of_birth)
                                    <span class="text-slate-400 font-normal">({{ \Carbon\Carbon::parse($student->date_of_birth)->age }} yrs)</span>
                                @endif
                            </span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Guardian Phone</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-1 block">
                                {{ $student->phone ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Portal Email Address</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-1 block truncate">
                                {{ $student->email ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Guardian & Emergency Contact --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center mb-4 border-b border-slate-100 dark:border-slate-700/80 pb-3">
                        <i class="fa-solid fa-users text-indigo-500 mr-2.5"></i> Guardian & Emergency Contacts
                    </h3>

                    <div class="space-y-4">
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Emergency Contact</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-1 block">
                                {{ $student->emergency_contact ?? $student->phone ?? 'No emergency contact recorded' }}
                            </span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Residential Address</span>
                            <p class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1 leading-relaxed">
                                {{ $student->address ?? 'No physical address recorded.' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Column 3: Academic Metadata & Stream Details --}}
            <div class="space-y-6">

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center mb-4 border-b border-slate-100 dark:border-slate-700/80 pb-3">
                        <i class="fa-solid fa-graduation-cap text-indigo-500 mr-2.5"></i> Academic Details
                    </h3>

                    <ul class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                        <li class="py-3 flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Assigned Class Stream</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 px-2.5 py-1 rounded-lg border border-indigo-200 dark:border-indigo-800/50">
                                {{ $streamName }}
                            </span>
                        </li>

                        <li class="py-3 flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Form Teacher</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">
                                {{ $student->schoolClass->teacher->name ?? $student->schoolClass->classTeacher->name ?? 'Unassigned' }}
                            </span>
                        </li>

                        <li class="py-3 flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Current Term</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">
                                {{ $student->term ? $student->term->term_name . ' (' . $student->term->academic_year . ')' : 'N/A' }}
                            </span>
                        </li>

                        <li class="py-3 flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Enrollment Date</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">
                                {{ $student->enrollment_date?->format('d M, Y') ?? $student->created_at?->format('d M, Y') ?? 'N/A' }}
                            </span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
        </div>

        <section x-show="activeTab === 'academic'" x-cloak class="space-y-6">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Academic history</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Exam and class activity results recorded since {{ \Carbon\Carbon::parse($enrollmentDate)->format('d M Y') }}.</p>
            </div>

            <form method="GET" action="{{ url('/students/' . $student->id) }}" class="flex flex-col gap-3 rounded-xl border border-teal-200 bg-teal-50 p-4 sm:flex-row sm:items-end dark:border-teal-900/60 dark:bg-teal-950/30">
                <input type="hidden" name="section" value="academic">
                <label for="academic-term" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Show records for</label>
                <select id="academic-term" name="term_id" class="min-w-56 rounded-lg border-slate-300 bg-white text-sm text-slate-800 focus:border-teal-600 focus:ring-teal-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">All terms since enrolment</option>
                    @foreach($enrollmentTerms as $term)
                        <option value="{{ $term->id }}" @selected($selectedTermId === $term->id)>{{ $term->display_name }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Show records</button>
                @if($selectedTerm)<span class="text-xs font-medium text-teal-800 dark:text-teal-200">Showing {{ $selectedTerm->display_name }}</span>@endif
            </form>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Exam papers recorded</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($examMarksCount) }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Average: {{ number_format($examAverage, 1) }}%</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Class activities recorded</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($activityMarksCount) }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Average: {{ number_format($activityAverage, 1) }}%</p>
                </div>
            </div>

            <details open class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                <summary class="cursor-pointer list-none border-b border-slate-200 px-5 py-4 font-bold text-slate-900 marker:hidden hover:bg-amber-50 dark:border-slate-700 dark:text-white dark:hover:bg-slate-700/50">Exam results <span class="ml-2 text-xs font-medium text-slate-500">{{ number_format($examMarksCount) }} papers</span><span class="float-right text-xs font-medium text-teal-700 dark:text-teal-300">Show / hide</span></summary>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/40 dark:text-slate-400"><tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Exam / subject</th><th class="px-5 py-3">Term</th><th class="px-5 py-3">Mark</th><th class="px-5 py-3">Teacher comment</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($examHistory as $mark)
                                @php $maxMark = max((float) ($mark->max_score ?: 100), 1); @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $mark->exam?->exam_date?->format('d M Y') ?? 'N/A' }}</td>
                                    <td class="px-5 py-3"><span class="block font-semibold text-slate-800 dark:text-slate-100">{{ $mark->exam?->exam_name ?? 'Exam' }}</span><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $mark->exam?->subject?->subject_name ?? $mark->subject ?? 'Subject not recorded' }}</span></td>
                                    <td class="px-5 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $mark->exam?->term?->display_name ?? 'Term not recorded' }}</td>
                                    <td class="whitespace-nowrap px-5 py-3"><span class="font-semibold text-slate-800 dark:text-slate-100">{{ rtrim(rtrim(number_format((float) $mark->score, 2), '0'), '.') }}/{{ rtrim(rtrim(number_format($maxMark, 2), '0'), '.') }}</span><span class="ml-1.5 text-xs text-slate-500">({{ number_format(((float) $mark->score / $maxMark) * 100, 1) }}%)</span></td>
                                    <td class="max-w-xs px-5 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $mark->teacher_comment ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No exam results have been recorded since enrolment.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($examHistory->hasPages())<div class="border-t border-slate-200 px-5 py-3 dark:border-slate-700">{{ $examHistory->links() }}</div>@endif
            </details>

            <details open class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                <summary class="cursor-pointer list-none border-b border-slate-200 px-5 py-4 font-bold text-slate-900 marker:hidden hover:bg-amber-50 dark:border-slate-700 dark:text-white dark:hover:bg-slate-700/50">Class activities and tests <span class="ml-2 text-xs font-medium text-slate-500">{{ number_format($activityMarksCount) }} records</span><span class="float-right text-xs font-medium text-teal-700 dark:text-teal-300">Show / hide</span></summary>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/40 dark:text-slate-400"><tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Activity</th><th class="px-5 py-3">Subject / class</th><th class="px-5 py-3">Term</th><th class="px-5 py-3">Mark</th><th class="px-5 py-3">Comment</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($activityHistory as $activityMark)
                                @php $activity = $activityMark->classActivity; $activityMax = max((float) ($activity?->max_score ?: 100), 1); $assignment = $activity?->subjectAssignment; @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $activity?->activity_date?->format('d M Y') ?? 'N/A' }}</td>
                                    <td class="px-5 py-3"><span class="block font-semibold text-slate-800 dark:text-slate-100">{{ $activity?->title ?? 'Class activity' }}</span><span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ ucfirst($activity?->type ?? 'activity') }}</span></td>
                                    <td class="px-5 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $assignment?->subject?->subject_name ?? 'Subject not recorded' }}<span class="mt-1 block text-slate-400">{{ $assignment?->schoolClass?->class_name ?? ($assignment?->grade ? $assignment->grade . ' - All Streams' : 'Class not recorded') }}</span></td>
                                    <td class="px-5 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $activity?->term?->display_name ?? 'Term not recorded' }}</td>
                                    <td class="whitespace-nowrap px-5 py-3"><span class="font-semibold text-slate-800 dark:text-slate-100">{{ rtrim(rtrim(number_format((float) $activityMark->score, 2), '0'), '.') }}/{{ rtrim(rtrim(number_format($activityMax, 2), '0'), '.') }}</span><span class="ml-1.5 text-xs text-slate-500">({{ number_format(((float) $activityMark->score / $activityMax) * 100, 1) }}%)</span></td>
                                    <td class="max-w-xs px-5 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $activityMark->comment ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No class activity marks have been recorded since enrolment.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($activityHistory->hasPages())<div class="border-t border-slate-200 px-5 py-3 dark:border-slate-700">{{ $activityHistory->links() }}</div>@endif
            </details>
        </section>

        <section x-show="activeTab === 'financial'" x-cloak class="space-y-6">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Financial record</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Payment history from enrolment on {{ \Carbon\Carbon::parse($enrollmentDate)->format('d M Y') }}.</p>
            </div>

            <form method="GET" action="{{ url('/students/' . $student->id) }}" class="flex flex-col gap-3 rounded-xl border border-teal-200 bg-teal-50 p-4 sm:flex-row sm:items-end dark:border-teal-900/60 dark:bg-teal-950/30">
                <input type="hidden" name="section" value="financial">
                <label for="financial-term" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Show records for</label>
                <select id="financial-term" name="term_id" class="min-w-56 rounded-lg border-slate-300 bg-white text-sm text-slate-800 focus:border-teal-600 focus:ring-teal-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">All terms since enrolment</option>
                    @foreach($enrollmentTerms as $term)
                        <option value="{{ $term->id }}" @selected($selectedTermId === $term->id)>{{ $term->display_name }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Show records</button>
                @if($selectedTerm)<span class="text-xs font-medium text-teal-800 dark:text-teal-200">Showing {{ $selectedTerm->display_name }}</span>@endif
            </form>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-teal-200 bg-teal-50 p-5 dark:border-teal-900/60 dark:bg-teal-950/30"><p class="text-xs font-semibold text-teal-900 dark:text-teal-200">{{ $selectedTerm ? 'Paid in selected term' : 'Paid since enrolment' }}</p><p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">${{ number_format($totalPaidSinceEnrollment, 2) }}</p><p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ number_format($paymentCount) }} recorded {{ $paymentCount === 1 ? 'payment' : 'payments' }}</p></div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800"><p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Fees for {{ $financialTerm?->display_name ?? 'current term' }}</p><p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">${{ number_format($expectedFees, 2) }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $financialTerm?->display_name ?? 'No active term selected' }}</p></div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800"><p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Paid in this term</p><p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">${{ number_format($paidInCurrentTerm, 2) }}</p><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $financialTerm?->display_name ?? 'No term selected' }}</p></div>
                <div class="rounded-xl border {{ $financialStatus === 'Balance due' ? 'border-amber-200 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/30' : ($financialStatus === 'Settled' ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/30' : 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800') }} p-5"><p class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $financialTerm?->display_name ?? 'Selected term' }} status</p><p class="mt-2 text-xl font-bold {{ $financialStatus === 'Balance due' ? 'text-amber-800 dark:text-amber-300' : ($financialStatus === 'Settled' ? 'text-emerald-800 dark:text-emerald-300' : 'text-slate-700 dark:text-slate-200') }}">{{ $financialStatus }}</p><p class="mt-1 text-xs text-slate-600 dark:text-slate-300">{{ $currentTermBalance > 0 ? '$' . number_format($currentTermBalance, 2) . ' remaining' : ($currentTermBalance < 0 ? '$' . number_format(abs($currentTermBalance), 2) . ' credit' : ($expectedFees <= 0 ? 'Add a fee schedule to calculate balance' : 'No balance due')) }}</p></div>
            </div>

            @if($paymentsByTerm->isNotEmpty())
                <details open class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                    <summary class="cursor-pointer list-none border-b border-slate-200 px-5 py-4 font-bold text-slate-900 hover:bg-amber-50 dark:border-slate-700 dark:text-white dark:hover:bg-slate-700/50">Payments by term <span class="ml-2 text-xs font-medium text-slate-500">{{ $paymentsByTerm->count() }} terms</span><span class="float-right text-xs font-medium text-teal-700 dark:text-teal-300">Show / hide</span></summary>
                    <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/40 dark:text-slate-400"><tr><th class="px-5 py-3">Term</th><th class="px-5 py-3">Payments</th><th class="px-5 py-3">Total received</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach($paymentsByTerm as $termPayments)
                                <tr><td class="px-5 py-3 text-slate-700 dark:text-slate-200">{{ $termPayments->term?->display_name ?? 'Term record unavailable' }}</td><td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ number_format($termPayments->payment_count) }}</td><td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-100">${{ number_format($termPayments->total_paid, 2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table></div>
                </details>
            @endif

            <details open class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                <summary class="cursor-pointer list-none border-b border-slate-200 px-5 py-4 font-bold text-slate-900 hover:bg-amber-50 dark:border-slate-700 dark:text-white dark:hover:bg-slate-700/50">Payment transactions <span class="ml-2 text-xs font-medium text-slate-500">{{ number_format($paymentCount) }} records</span><span class="float-right text-xs font-medium text-teal-700 dark:text-teal-300">Show / hide</span></summary>
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/40 dark:text-slate-400"><tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Term</th><th class="px-5 py-3">Method / reference</th><th class="px-5 py-3">Notes</th><th class="px-5 py-3 text-right">Amount</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($paymentHistory as $payment)
                            <tr><td class="whitespace-nowrap px-5 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $payment->payment_date?->format('d M Y') ?? 'N/A' }}</td><td class="px-5 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $payment->term?->display_name ?? 'Term not recorded' }}</td><td class="px-5 py-3"><span class="block text-slate-700 dark:text-slate-200">{{ $payment->payment_method ?: 'Not recorded' }}</span><span class="mt-1 block font-mono text-xs text-slate-400">{{ $payment->reference_no ?: '-' }}</span></td><td class="max-w-xs px-5 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $payment->remarks ?: '-' }}</td><td class="whitespace-nowrap px-5 py-3 text-right font-semibold text-slate-800 dark:text-slate-100">${{ number_format((float) $payment->amount_paid, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">No payments have been recorded since enrolment.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
                @if($paymentHistory->hasPages())<div class="border-t border-slate-200 px-5 py-3 dark:border-slate-700">{{ $paymentHistory->links() }}</div>@endif
            </details>
        </section>

    </div>
@endsection
