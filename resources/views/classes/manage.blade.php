{{-- resources/views/classes/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Manage Classes | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">School Classes</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage academic class structure and teacher allocations</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ Route::has('classes.assign') ? route('classes.assign') : url('/classes/assign-subjects') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow">
                <i class="fa-solid fa-link mr-2 text-xs"></i> Assign Subjects
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Left Column: Add Class Form --}}
            <div class="lg:col-span-4">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-square-plus text-indigo-500 mr-2.5"></i> Add New Class
                        </h3>
                    </div>

                    <form method="POST" action="{{ Route::has('classes.store') ? route('classes.store') : url('/classes/store') }}" class="p-6 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Class Name</label>
                            <input type="text" name="class_name" placeholder="e.g. Grade 7" required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Class Code</label>
                            <input type="text" name="class_code" placeholder="e.g. G7A" required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Room Number</label>
                            <input type="text" name="room_number" placeholder="e.g. Room 10"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow">
                                <i class="fa-solid fa-floppy-disk mr-2"></i> Create Class
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Right Column: Existing Classes Table --}}
            <div class="lg:col-span-8">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-list text-emerald-500 mr-2.5"></i> Existing Classes
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Code</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Form Teacher</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Room</th>
                                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($classes as $class)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-600">
                                            {{ $class->class_code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <strong class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $class->class_name }}</strong>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($class->teacher)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/50">
                                                <i class="fa-solid fa-user-tie mr-1.5"></i> {{ $class->teacher->name }}
                                            </span>
                                        @else
                                            <span class="text-xs italic text-slate-400 dark:text-slate-500 inline-flex items-center">
                                                <i class="fa-solid fa-circle-exclamation mr-1.5"></i> Not Assigned
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300 font-medium">
                                        {{ $class->room_number ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                        <div class="flex items-center justify-end space-x-1">
                                            <a href="{{ Route::has('classes.edit') ? route('classes.edit', $class->id) : url('/classes/' . $class->id . '/edit') }}" 
                                               class="p-2 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors"
                                               title="Assign Form Teacher">
                                                <i class="fa-solid fa-user-plus text-sm"></i>
                                            </a>
                                            <a href="{{ Route::has('classes.students') ? route('classes.students', $class->id) : url('/classes/' . $class->id . '/students') }}" 
                                               class="p-2 rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-colors"
                                               title="View Class Students">
                                                <i class="fa-solid fa-users text-sm"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                        No classes created yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection