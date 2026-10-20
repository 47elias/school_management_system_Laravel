{{-- resources/views/classes/manage.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Class & Grade Level Management')

@section('header')
    <div x-data class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Class Architecture</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage Grade Levels / Forms and expand sub-class streams</p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            {{-- Button to Add New Grade Level --}}
            <button @click="$dispatch('open-grade-modal')" 
                    class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-folder-plus mr-2 text-indigo-400"></i> ADD GRADE LEVEL
            </button>

            {{-- Button to Add New Class Stream --}}
            <button @click="$dispatch('open-stream-modal', { gradeId: '' })" 
                    class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-plus mr-2"></i> ADD SUB-CLASS STREAM
            </button>

            <a href="{{ Route::has('classes.assignSubjects') ? route('classes.assignSubjects') : url('/classes/assign-subjects') }}" 
               class="inline-flex items-center px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-book-bookmark mr-1.5 text-indigo-500"></i> Assign Subjects
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ 
            showGradeModal: false, 
            showStreamModal: false, 
            selectedGradeId: '',
            searchQuery: '',
            expandAllState: false
         }" 
         @open-grade-modal.window="showGradeModal = true"
         @open-stream-modal.window="selectedGradeId = $event.detail.gradeId; showStreamModal = true"
         class="space-y-6">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('error') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>
        @endif

        {{-- Search & Global Accordion Controls --}}
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="relative w-full sm:w-96">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model="searchQuery" placeholder="Search by Grade (e.g. Form 1), Stream, Code, or Teacher..." 
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center space-x-2">
                <button @click="expandAllState = true; $dispatch('toggle-all', true)" 
                        class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    <i class="fa-solid fa-angles-down mr-1"></i> Expand All Grades
                </button>
                <button @click="expandAllState = false; $dispatch('toggle-all', false)" 
                        class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    <i class="fa-solid fa-angles-up mr-1"></i> Collapse All
                </button>
            </div>
        </div>

        {{-- Collapsible Grade Level Sections --}}
        @forelse($gradeLevels as $grade)
            @php
                $streamSearchTerms = $grade->schoolClasses->map(function($c) {
                    $teacherName = $c->classTeacher->name ?? $c->teacher->name ?? '';
                    return strtolower($c->class_name . ' ' . $c->class_code . ' ' . $c->stream . ' ' . $teacherName);
                })->values()->toJson();
            @endphp

            <div x-data="{ 
                    isOpen: false,
                    gradeName: '{{ strtolower($grade->name) }}',
                    streams: {{ $streamSearchTerms }},
                    matchesSearch() {
                        if (!searchQuery.trim()) return true;
                        const q = searchQuery.toLowerCase().trim();
                        if (this.gradeName.includes(q)) return true;
                        return this.streams.some(s => s.includes(q));
                    }
                 }" 
                 x-show="matchesSearch()"
                 @toggle-all.window="isOpen = $event.detail"
                 x-effect="if (searchQuery.trim().length > 0 && matchesSearch()) isOpen = true"
                 class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-all">
                
                {{-- Clickable Header Accordion Banner --}}
                <div @click="isOpen = !isOpen" 
                     class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between cursor-pointer select-none group hover:bg-slate-850 transition-colors">
                    
                    <div class="flex items-center space-x-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-400 font-bold text-sm font-mono shrink-0">
                            #{{ $grade->level_order }}
                        </div>
                        <div>
                            <h3 class="text-base font-bold tracking-tight text-white flex items-center">
                                {{ $grade->name }}
                                <span class="ml-3 text-[11px] font-normal text-slate-400 bg-slate-800 px-2.5 py-0.5 rounded-full border border-slate-700 font-mono">
                                    {{ $grade->schoolClasses->count() }} {{ Str::plural('Stream', $grade->schoolClasses->count()) }}
                                </span>
                            </h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Total Enrolled: <strong class="text-indigo-300">{{ $grade->students->count() }} Students</strong>
                            </p>
                        </div>
                    </div>

                    {{-- Actions & Chevron Dropdown Indicator --}}
                    <div class="flex items-center space-x-3" @click.stop>
                        <button @click="$dispatch('open-stream-modal', { gradeId: '{{ $grade->id }}' })" 
                                class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                            <i class="fa-solid fa-plus mr-1.5 text-[10px]"></i> Add Stream
                        </button>

                        @if($grade->schoolClasses->count() === 0)
                            <form action="{{ route('grade-levels.destroy', $grade->id) }}" method="POST" onsubmit="return confirm('Delete Grade Level {{ $grade->name }}?');" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-xl text-rose-400 hover:text-rose-300 hover:bg-rose-950/50 transition-colors" title="Delete Empty Grade Level">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        @endif

                        <button @click="isOpen = !isOpen" class="p-2 rounded-xl text-slate-400 group-hover:text-white transition-transform duration-200" :class="{ 'rotate-180': isOpen }">
                            <i class="fa-solid fa-chevron-down text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Expandable Dropdown Content (Streams Grid) --}}
                <div x-show="isOpen" x-collapse class="p-6 border-t border-slate-100 dark:border-slate-700/60">
                    @if($grade->schoolClasses->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            @foreach($grade->schoolClasses as $class)
                                @php
                                    $teacherName = $class->classTeacher->name ?? $class->teacher->name ?? 'Unassigned';
                                    $searchString = strtolower($class->class_name . ' ' . $class->class_code . ' ' . $class->stream . ' ' . $teacherName);
                                @endphp
                                <div x-show="!searchQuery || '{{ $searchString }}'.includes(searchQuery.toLowerCase()) || gradeName.includes(searchQuery.toLowerCase())"
                                     class="p-5 rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/40 flex flex-col justify-between space-y-4 hover:border-indigo-500 dark:hover:border-indigo-500/80 transition-all group">
                                    
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="text-base font-black text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                {{ $class->class_name }}
                                            </h4>
                                            <span class="text-[10px] font-mono text-slate-400 block mt-0.5">Code: {{ $class->class_code }}</span>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">
                                            Stream {{ $class->stream }}
                                        </span>
                                    </div>

                                    <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900/50 p-3 rounded-xl border border-slate-100 dark:border-slate-700/50">
                                        <p class="flex items-center justify-between">
                                            <span class="text-slate-400 flex items-center"><i class="fa-solid fa-user-tie text-indigo-500 mr-2 w-4"></i> Form Teacher:</span>
                                            <strong class="text-slate-800 dark:text-slate-200">{{ $teacherName }}</strong>
                                        </p>
                                        <p class="flex items-center justify-between">
                                            <span class="text-slate-400 flex items-center"><i class="fa-solid fa-door-open text-indigo-500 mr-2 w-4"></i> Room:</span>
                                            <strong class="text-slate-800 dark:text-slate-200">{{ $class->room_number ?? 'N/A' }}</strong>
                                        </p>
                                        <p class="flex items-center justify-between">
                                            <span class="text-slate-400 flex items-center"><i class="fa-solid fa-users text-indigo-500 mr-2 w-4"></i> Enrolled:</span>
                                            <strong class="font-mono text-slate-800 dark:text-slate-200">{{ $class->students->count() }} / {{ $class->capacity }}</strong>
                                        </p>
                                    </div>

                                    <div class="pt-2 flex items-center justify-between text-xs">
                                        <a href="{{ Route::has('classes.showStudents') ? route('classes.showStudents', $class->id) : url('/classes/' . $class->id . '/students') }}" 
                                           class="inline-flex items-center font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                            <i class="fa-solid fa-users-rectangle mr-1.5"></i> Class Roster ({{ $class->students->count() }})
                                        </a>

                                        <div class="flex items-center space-x-1">
                                            <a href="{{ Route::has('classes.edit') ? route('classes.edit', $class->id) : url('/classes/' . $class->id . '/edit') }}" 
                                               class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Edit Class">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>

                                            <form action="{{ Route::has('classes.destroy') ? route('classes.destroy', $class->id) : url('/classes/' . $class->id) }}" method="POST" onsubmit="return confirm('Delete stream {{ $class->class_name }}?');" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" title="Delete Stream">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 bg-slate-50 dark:bg-slate-900/30 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                            <i class="fa-solid fa-layer-group text-2xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">No sub-class streams created under {{ $grade->name }} yet.</p>
                            <button @click="$dispatch('open-stream-modal', { gradeId: '{{ $grade->id }}' })" 
                                    class="mt-3 inline-flex items-center px-3.5 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-bold">
                                <i class="fa-solid fa-plus mr-1.5 text-[10px]"></i> Create First Stream
                            </button>
                        </div>
                    @endif
                </div>

            </div>
        @empty
            <div class="text-center py-12 bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-8">
                <i class="fa-solid fa-folder-open text-4xl text-slate-300 dark:text-slate-600 mb-3 block"></i>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">No Grade Levels Found</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Start by adding your first Grade Level or Form (e.g. Form 1, Grade 1) to build your school structure.</p>
                <button @click="$dispatch('open-grade-modal')" class="mt-4 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold shadow-sm">
                    <i class="fa-solid fa-folder-plus mr-2"></i> Add First Grade Level
                </button>
            </div>
        @endforelse

        {{-- Modal 1: Add Dynamic Grade Level --}}
        <div x-show="showGradeModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                <div @click="showGradeModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle bg-white dark:bg-slate-800 rounded-3xl shadow-2xl z-10 border border-slate-200 dark:border-slate-700">
                    <form action="{{ route('grade-levels.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                                <i class="fa-solid fa-folder-plus text-indigo-500 mr-2"></i> Add New Grade Level / Form
                            </h3>
                            <button type="button" @click="showGradeModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Grade / Form Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" placeholder="e.g. Form 1, Grade 7, Lower 6" required 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Level Order (For Hierarchy Sorting) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="level_order" value="{{ $gradeLevels->count() + 1 }}" min="1" required 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span class="text-[10px] text-slate-400 mt-1 block">Determines display sequence (e.g., 1 for Form 1, 2 for Form 2).</span>
                        </div>

                        <div class="pt-3 flex items-center justify-end space-x-2 border-t border-slate-100 dark:border-slate-700">
                            <button type="button" @click="showGradeModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm">Create Grade Level</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal 2: Add Class Stream under Grade Level --}}
        <div x-show="showStreamModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                <div @click="showStreamModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle bg-white dark:bg-slate-800 rounded-3xl shadow-2xl z-10 border border-slate-200 dark:border-slate-700">
                    <form action="{{ route('classes.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                                <i class="fa-solid fa-plus text-indigo-500 mr-2"></i> Add Sub-Class Stream
                            </h3>
                            <button type="button" @click="showStreamModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Parent Grade Level <span class="text-rose-500">*</span>
                            </label>
                            <select name="grade_level_id" x-model="selectedGradeId" required class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Choose Grade Level --</option>
                                @foreach($gradeLevels as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Stream Identifier <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="stream" placeholder="e.g. A, B, C, East" required class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <span class="text-[10px] text-slate-400 mt-1 block">Produces names like "Form 1 A".</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Room Number
                                </label>
                                <input type="text" name="room_number" placeholder="e.g. Room 102" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Capacity Limit
                                </label>
                                <input type="number" name="capacity" value="40" min="1" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Form Teacher
                                </label>
                                <select name="class_teacher_id" class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">-- Unassigned --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="pt-3 flex items-center justify-end space-x-2 border-t border-slate-100 dark:border-slate-700">
                            <button type="button" @click="showStreamModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm">Save Stream</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection