{{-- resources/views/classes/edit.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Assign Teacher - ' . ($class?->class_name ?? 'Class') . ' | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Manage Class</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Assign Form Teacher to {{ $class?->class_name }}</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ Route::has('classes.index') ? route('classes.index') : url('/classes') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Cancel & Return
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 space-y-1">
                <h4 class="text-xs font-bold uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> Form Submission Error
                </h4>
                <ul class="list-disc list-inside text-xs font-medium pl-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Form Column --}}
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-user-pen text-indigo-500 mr-2.5"></i> Form Teacher Assignment
                        </h3>
                    </div>

                    <form method="POST" action="{{ Route::has('classes.update') ? route('classes.update', $class?->id) : url('/classes/' . $class?->id) }}" class="p-6 space-y-5">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="class_name" value="{{ $class?->class_name }}">

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Class Name</label>
                            <input type="text" value="{{ $class?->class_name }}" disabled 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-100 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                Form Teacher <span class="text-rose-500">*</span>
                            </label>
                            <select name="teacher_id" 
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- No Teacher Assigned --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ ($class?->teacher_id == $teacher->id) ? 'selected' : '' }}>
                                        {{ $teacher->name }} (ID: {{ $teacher->employee_id ?? $teacher->id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pt-2 space-y-2">
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow">
                                <i class="fa-solid fa-floppy-disk mr-2"></i> Save Assignment
                            </button>
                            <a href="{{ Route::has('classes.index') ? route('classes.index') : url('/classes') }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Guidelines Column --}}
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 space-y-4 transition-colors">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center border-b border-slate-100 dark:border-slate-700 pb-3">
                        <i class="fa-solid fa-circle-info text-sky-500 mr-2.5"></i> Assignment Guidelines
                    </h3>
                    
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        You are assigning a primary Form Teacher to <strong class="text-indigo-600 dark:text-indigo-400">{{ $class?->class_name }}</strong>.
                    </p>

                    <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300">
                        <li class="flex items-start space-x-2.5">
                            <i class="fa-solid fa-check text-emerald-500 mt-0.5"></i>
                            <span>The assigned teacher gains primary oversight to view and manage students enrolled in this class.</span>
                        </li>
                        <li class="flex items-start space-x-2.5">
                            <i class="fa-solid fa-check text-emerald-500 mt-0.5"></i>
                            <span>Form Teachers can record attendance, issue report cards, and track academic progression.</span>
                        </li>
                        <li class="flex items-start space-x-2.5">
                            <i class="fa-solid fa-check text-emerald-500 mt-0.5"></i>
                            <span>Each class supports one primary Form Teacher at a time. Re-assigning will override the existing allocation.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection