{{-- resources/views/classes/assign_subjects.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Assign Subjects | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Class-Subject Assignment</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage curriculum mappings and subject allocations</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ Route::has('classes.index') ? route('classes.index') : url('/classes') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Classes
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Notifications --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Success</h4>
                        <p class="text-xs font-medium mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 space-y-1">
                <h4 class="text-xs font-bold uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> Errors Detected
                </h4>
                <ul class="list-disc list-inside text-xs font-medium pl-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Left Column: Assignment Form --}}
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-link text-emerald-500 mr-2.5"></i> Assign Subjects to Class
                        </h3>
                    </div>

                    <form method="POST" action="{{ Route::has('classes.assign.store') ? route('classes.assign.store') : url('/classes/assign-subjects') }}" class="p-6 space-y-5">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                Select Class <span class="text-rose-500">*</span>
                            </label>
                            <select name="class_id" required 
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Choose a Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->class_name }} ({{ $class->class_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                Select Subjects <span class="text-rose-500">*</span>
                            </label>
                            <div class="max-h-72 overflow-y-auto bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl p-3.5 space-y-2">
                                @foreach($subjects as $subject)
                                    <label class="flex items-center space-x-3 p-2 rounded-lg hover:bg-white dark:hover:bg-slate-800 transition-colors cursor-pointer border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                                        <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" 
                                               class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                                        <div class="text-xs">
                                            <strong class="text-slate-800 dark:text-slate-100 font-mono">{{ $subject->subject_code }}</strong>
                                            <span class="text-slate-500 dark:text-slate-400 ml-1.5">— {{ $subject->subject_name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-2">
                                Select all subjects that should be taught in this class for the current academic term.
                            </p>
                        </div>

                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow">
                                <i class="fa-solid fa-floppy-disk mr-2"></i> Save Assignments
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Right Column: Current Assignments Table --}}
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-layer-group text-indigo-500 mr-2.5"></i> Current Class Subjects
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-1/3">Class Details</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Assigned Subjects</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                                @foreach($classes as $class)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block">{{ $class->class_name }}</strong>
                                        <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-600 mt-1 inline-block">
                                            {{ $class->class_code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($class->subjects->count() > 0)
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($class->subjects as $assignedSubject)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                                                        {{ $assignedSubject->subject_name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs italic text-slate-400 dark:text-slate-500 inline-flex items-center">
                                                <i class="fa-solid fa-circle-exclamation mr-1.5"></i> No subjects assigned
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection