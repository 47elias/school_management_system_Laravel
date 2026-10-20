{{-- resources/views/activities/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Continuous Assessment | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Continuous Assessment</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">School-wide daily activity marks, independent of exams</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ Route::has('activities.analytics') ? route('activities.analytics') : url('/activities/analytics') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition-all">
                <i class="fa-solid fa-chart-column mr-2"></i> View Analytics Dashboard
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Filters Bar --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-4 transition-colors">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Grade / Form</label>
                    <select name="grade_level_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                        <option value="">All Levels</option>
                        @foreach($gradeLevels as $grade)<option value="{{ $grade->id }}" @selected(request('grade_level_id') == $grade->id)>{{ $grade->name }}</option>@endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Term</label>
                    <select name="term_id" onchange="this.form.submit()" 
                            class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($terms as $t)
                            <option value="{{ $t->id }}" {{ $selectedTerm && $selectedTerm->id == $t->id ? 'selected' : '' }}>
                                {{ $t->term_name }} ({{ $t->academic_year }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Class</label>
                    <select name="class_id" onchange="this.form.submit()" 
                            class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Subject</label>
                    <select name="subject_id" onchange="this.form.submit()" 
                            class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->subject_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Activity Type</label>
                    <select name="type" onchange="this.form.submit()" 
                            class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Types</option>
                        @foreach(['classwork','homework','quiz','participation','practical','project','other'] as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </form>
        </div>

        {{-- Logged Activities Directory --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-list-check text-indigo-500 mr-2.5"></i> Logged Activities
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Teacher</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Subject</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Class</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Recorded</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Avg %</th>
                            <th scope="col" class="px-6 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">View</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($activities as $activity)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800 dark:text-slate-100">
                                    {{ $activity->title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300">
                                    {{ $activity->creator->name ?? $activity->subjectAssignment->teacher->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                                        {{ $activity->type_label ?? $activity->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-700 dark:text-slate-300 font-medium">
                                    {{ $activity->subjectAssignment->subject->subject_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                        {{ $selectedClass?->class_name ?? ($activity->subjectAssignment->schoolClass->class_name ?? (($activity->subjectAssignment->grade ?? 'All levels') . ' - All Streams')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400 font-mono">
                                    {{ $activity->formatted_date }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ $activity->visible_marks_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $activity->visible_average_percent }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right"><a href="{{ route('activities.show', $activity->id) . '?' . http_build_query(request()->only(['term_id', 'grade_level_id', 'class_id', 'subject_id', 'type'])) }}" class="inline-flex items-center rounded-lg bg-indigo-50 dark:bg-indigo-950/50 px-3 py-2 font-bold text-indigo-700 dark:text-indigo-300"><i class="fa-solid fa-eye mr-1.5"></i>View Marks</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-folder-open text-2xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                                    No activities logged for this filter context.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($activities->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700/60">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
