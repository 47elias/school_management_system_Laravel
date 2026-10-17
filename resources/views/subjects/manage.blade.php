{{-- resources/views/subjects/manage.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Manage Subjects & Combinations | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Subject & Combination Studio</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Create subjects, set pass marks, and build subject combinations for streams</p>
        </div>

        <div class="flex items-center space-x-2.5">
            <a href="{{ Route::has('subjects.index') ? route('subjects.index') : url('/subjects') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to Dashboard
            </a>
            <a href="{{ Route::has('subjects.allocate_classes') ? route('subjects.allocate_classes') : url('/subjects/allocate-classes') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-sitemap mr-1.5"></i> Class Allocations &rarr;
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ searchQuery: '' }" class="space-y-6">

        {{-- Session Flash Messages --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Success</h4>
                        <p class="text-xs font-medium mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Error</h4>
                        <p class="text-xs font-medium mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>
        @endif

        {{-- SECTION 1: CREATE FORMS (SUBJECT & COMBINATION) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {{-- CREATE SUBJECT FORM --}}
            <div class="lg:col-span-6 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center space-x-2.5">
                        <i class="fa-solid fa-plus-circle text-indigo-400"></i>
                        <h3 class="text-sm font-bold tracking-tight text-white">Define New Subject</h3>
                    </div>
                    <span class="text-[10px] font-mono bg-indigo-950/80 text-indigo-300 border border-indigo-800/60 px-2 py-0.5 rounded-full font-bold">
                        Individual
                    </span>
                </div>

                <form method="POST" action="{{ Route::has('subjects.store') ? route('subjects.store') : url('/subjects') }}" class="p-6 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Subject Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="subject_name" placeholder="e.g. Mathematics, Physics" required
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Subject Code <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="subject_code" placeholder="e.g. MATH101, PHY202" required
                               class="w-full px-3.5 py-2.5 text-xs font-mono uppercase font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-7">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Type</label>
                            <select name="type" class="w-full px-3 py-2.5 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="Core">Core</option>
                                <option value="Elective">Elective</option>
                                <option value="Practical">Practical</option>
                            </select>
                        </div>

                        <div class="col-span-5">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Pass Mark (%)</label>
                            <input type="number" name="pass_mark" value="50" min="0" max="100" required
                                   class="w-full px-3 py-2.5 text-xs font-mono font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition-all">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Save Subject
                        </button>
                    </div>
                </form>
            </div>

            {{-- CREATE SUBJECT COMBINATION FORM --}}
            <div class="lg:col-span-6 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center space-x-2.5">
                        <i class="fa-solid fa-layer-group text-purple-400"></i>
                        <h3 class="text-sm font-bold tracking-tight text-white">Create Subject Combination</h3>
                    </div>
                    <span class="text-[10px] font-mono bg-purple-950/80 text-purple-300 border border-purple-800/60 px-2 py-0.5 rounded-full font-bold">
                        Package
                    </span>
                </div>

                <form method="POST" action="{{ Route::has('subject-combinations.store') ? route('subject-combinations.store') : url('/subject-combinations') }}" class="p-6 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Combination Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" placeholder="e.g. Pure Sciences, Commercials A" required
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Combination Code</label>
                            <input type="text" name="code" placeholder="e.g. SCI-01" class="w-full px-3.5 py-2.5 text-xs font-mono font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Target Grade Level</label>
                            <select name="grade" class="w-full px-3 py-2.5 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">All Form Levels</option>
                                @if(isset($gradeLevels))
                                    @foreach($gradeLevels as $gl)
                                        @php $glName = is_object($gl) ? $gl->name : $gl; @endphp
                                        <option value="{{ $glName }}">{{ $glName }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Select Included Subjects <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2 max-h-36 overflow-y-auto p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                            @foreach($subjects as $sb)
                                <label class="flex items-center space-x-2 text-xs text-slate-700 dark:text-slate-300 font-medium">
                                    <input type="checkbox" name="subject_ids[]" value="{{ $sb->id }}" class="rounded text-purple-600">
                                    <span class="truncate">{{ $sb->subject_name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-sm transition-all">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Save Combination Package
                        </button>
                    </div>
                </form>
            </div>

        </div>

        {{-- SECTION 2: DIRECTORIES (FIT-TO-WIDTH TABLES) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {{-- SUBJECT DIRECTORY TABLE --}}
            <div class="lg:col-span-6 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                    <h3 class="text-sm font-bold text-white flex items-center">
                        <i class="fa-solid fa-list-check text-indigo-400 mr-2"></i> Subject Directory ({{ $subjects->count() }})
                    </h3>
                </div>

                <div class="w-full">
                    <table class="w-full divide-y divide-slate-100 dark:divide-slate-700/60 text-xs table-fixed">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-200 dark:border-slate-700/60 uppercase font-bold text-[10px] bg-slate-50/50 dark:bg-slate-900/50">
                                <th scope="col" class="px-4 py-3 text-left w-1/4">Code</th>
                                <th scope="col" class="px-4 py-3 text-left w-1/3">Subject Name</th>
                                <th scope="col" class="px-3 py-3 text-center w-1/5">Type</th>
                                <th scope="col" class="px-3 py-3 text-center w-1/6">Pass %</th>
                                <th scope="col" class="px-3 py-3 text-right w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($subjects as $subject)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-4 py-3 font-mono font-bold text-slate-700 dark:text-slate-300">{{ $subject->subject_code }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-100 break-words pr-2">{{ $subject->subject_name }}</td>
                                    <td class="px-3 py-3 text-center font-bold text-slate-600 dark:text-slate-400">{{ $subject->type }}</td>
                                    <td class="px-3 py-3 text-center font-mono font-bold text-slate-700 dark:text-slate-300">{{ $subject->pass_mark }}%</td>
                                    <td class="px-3 py-3 text-right">
                                        <form action="{{ Route::has('subjects.destroy') ? route('subjects.destroy', $subject->id) : url('/subjects/' . $subject->id) }}" method="POST" onsubmit="return confirm('Delete subject?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-rose-500 p-1 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg"><i class="fa-solid fa-trash-can"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-6 text-center text-slate-400">No subjects defined.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- COMBINATIONS DIRECTORY TABLE --}}
            <div class="lg:col-span-6 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                    <h3 class="text-sm font-bold text-white flex items-center">
                        <i class="fa-solid fa-layer-group text-purple-400 mr-2"></i> Combinations Directory
                    </h3>
                </div>

                <div class="w-full">
                    <table class="w-full divide-y divide-slate-100 dark:divide-slate-700/60 text-xs table-fixed">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-200 dark:border-slate-700/60 uppercase font-bold text-[10px] bg-slate-50/50 dark:bg-slate-900/50">
                                <th scope="col" class="px-4 py-3 text-left w-1/3">Combination</th>
                                <th scope="col" class="px-3 py-3 text-center w-1/4">Scope</th>
                                <th scope="col" class="px-4 py-3 text-left w-1/3">Subjects Count</th>
                                <th scope="col" class="px-3 py-3 text-right w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @if(isset($combinations) && $combinations->count() > 0)
                                @foreach($combinations as $comb)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-100 break-words pr-2">
                                            {{ $comb->name }}
                                        </td>
                                        <td class="px-3 py-3 text-center font-semibold text-slate-500">{{ $comb->grade ?? 'All Forms' }}</td>
                                        <td class="px-4 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                            {{ isset($comb->subjects) ? count($comb->subjects) : 0 }} Subjects
                                        </td>
                                        <td class="px-3 py-3 text-right">
                                            <form action="{{ Route::has('subject-combinations.destroy') ? route('subject-combinations.destroy', $comb->id) : url('/subject-combinations/' . $comb->id) }}" method="POST" onsubmit="return confirm('Delete combination?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-rose-500 p-1 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="4" class="px-6 py-6 text-center text-slate-400">No subject combinations defined.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
@endsection