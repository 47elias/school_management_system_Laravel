{{-- resources/views/classes/assign_subjects.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Class-Subject Allocation | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Curriculum & Subject Allocation</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Assign subjects to entire Grade Levels or customize specific streams</p>
        </div>
        <div>
            <a href="{{ Route::has('classes.index') ? route('classes.index') : url('/classes') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 shadow-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Classes
            </a>
        </div>
    </div>
@endsection

@section('content')
    @php
        // Prepare JSON data for Alpine.js dynamic pre-checking
        $classesJson = $classes->mapWithKeys(function ($item) {
            return [$item->id => [
                'id' => $item->id,
                'name' => $item->class_name,
                'grade_id' => $item->grade_level_id,
                'subject_ids' => $item->subjects->pluck('id')->toArray()
            ]];
        });

        $gradesJson = $gradeLevels->mapWithKeys(function ($item) {
            return [$item->id => [
                'id' => $item->id,
                'name' => $item->name,
                'streams' => $item->schoolClasses->map(fn($c) => ['id' => $c->id, 'name' => $c->class_name])->values()->toArray(),
                'all_subject_ids' => $item->schoolClasses->flatMap(fn($c) => $c->subjects->pluck('id'))->unique()->values()->toArray()
            ]];
        });
    @endphp

    <div x-data="{
            mode: 'grade', // 'grade' or 'stream'
            selectedGradeId: '',
            selectedClassId: '',
            selectedSubjects: [],
            subjectFilterQuery: '',
            matrixSearchQuery: '',
            expandAllMatrix: false,
            classesMap: {{ Js::from($classesJson) }},
            gradesMap: {{ Js::from($gradesJson) }},

            onGradeChange() {
                if (this.selectedGradeId && this.gradesMap[this.selectedGradeId]) {
                    this.selectedSubjects = [...this.gradesMap[this.selectedGradeId].all_subject_ids];
                } else {
                    this.selectedSubjects = [];
                }
            },

            onClassChange() {
                if (this.selectedClassId && this.classesMap[this.selectedClassId]) {
                    this.selectedSubjects = [...this.classesMap[this.selectedClassId].subject_ids];
                } else {
                    this.selectedSubjects = [];
                }
            },

            selectAllSubjects(allIds) {
                this.selectedSubjects = [...allIds];
            },

            clearAllSubjects() {
                this.selectedSubjects = [];
            }
         }" 
         class="space-y-6">

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
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- Left Column: Assignment Form Controls --}}
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    
                    {{-- Assignment Mode Tabs --}}
                    <div class="p-2 bg-slate-100 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 grid grid-cols-2 gap-1">
                        <button type="button" 
                                @click="mode = 'grade'; selectedSubjects = []; selectedClassId = '';"
                                :class="{ 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm font-black': mode === 'grade', 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-bold': mode !== 'grade' }"
                                class="py-2.5 px-3 rounded-xl text-xs text-center transition-all flex items-center justify-center">
                            <i class="fa-solid fa-layer-group mr-2 text-indigo-500"></i> Grade Level Bulk
                        </button>

                        <button type="button" 
                                @click="mode = 'stream'; selectedSubjects = []; selectedGradeId = '';"
                                :class="{ 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm font-black': mode === 'stream', 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-bold': mode !== 'stream' }"
                                class="py-2.5 px-3 rounded-xl text-xs text-center transition-all flex items-center justify-center">
                            <i class="fa-solid fa-sliders mr-2 text-indigo-500"></i> Stream Custom
                        </button>
                    </div>

                    <form method="POST" action="{{ Route::has('classes.storeAssignments') ? route('classes.storeAssignments') : (Route::has('classes.assign.store') ? route('classes.assign.store') : url('/classes/assign-subjects')) }}" class="p-6 space-y-5">
                        @csrf
                        <input type="hidden" name="assignment_mode" :value="mode">

                        {{-- MODE 1: Grade Level Bulk Selection --}}
                        <template x-if="mode === 'grade'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                        Select Target Grade Level / Form <span class="text-rose-500">*</span>
                                    </label>
                                    <select name="grade_level_id" x-model="selectedGradeId" @change="onGradeChange()" required 
                                            class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-bold">
                                        <option value="">-- Choose Grade Level --</option>
                                        @foreach($gradeLevels as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->schoolClasses->count() }} streams)</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Affected Streams Summary Tag --}}
                                <div x-show="selectedGradeId && gradesMap[selectedGradeId]" class="p-3 bg-indigo-50/70 dark:bg-indigo-950/40 rounded-2xl border border-indigo-200/80 dark:border-indigo-800/50 text-xs">
                                    <span class="text-[10px] font-bold uppercase text-indigo-600 dark:text-indigo-400 block mb-1">
                                        <i class="fa-solid fa-circle-info mr-1"></i> Will sync subjects to all streams:
                                    </span>
                                    <div class="flex flex-wrap gap-1">
                                        <template x-for="st in (gradesMap[selectedGradeId] ? gradesMap[selectedGradeId].streams : [])" :key="st.id">
                                            <span class="bg-white dark:bg-slate-800 px-2 py-0.5 rounded-md text-[10px] font-bold border border-indigo-200 dark:border-indigo-700 text-slate-700 dark:text-slate-200" x-text="st.name"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- MODE 2: Stream-Specific Customization Selection --}}
                        <template x-if="mode === 'stream'">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                                    Select Specific Class Stream <span class="text-rose-500">*</span>
                                </label>
                                <select name="class_id" x-model="selectedClassId" @change="onClassChange()" required 
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-bold">
                                    <option value="">-- Choose Specific Stream --</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}">{{ $c->class_name }} ({{ $c->class_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </template>

                        {{-- Subject Selection List with Quick Filter --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                    Select Curriculum Subjects <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex items-center space-x-2 text-[10px]">
                                    <button type="button" @click="selectAllSubjects({{ Js::from($subjects->pluck('id')) }})" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Select All</button>
                                    <span class="text-slate-300 dark:text-slate-600">|</span>
                                    <button type="button" @click="clearAllSubjects()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-medium">Clear</button>
                                </div>
                            </div>

                            {{-- Filter Subject Input --}}
                            <div class="relative mb-2">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                <input type="text" x-model="subjectFilterQuery" placeholder="Filter subjects list..." 
                                       class="w-full pl-8 pr-3 py-1.5 text-[11px] rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="max-h-80 overflow-y-auto bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 space-y-1.5">
                                @foreach($subjects as $subject)
                                    @php
                                        $subSearch = strtolower($subject->subject_code . ' ' . $subject->subject_name);
                                    @endphp
                                    <label x-show="!subjectFilterQuery || '{{ $subSearch }}'.includes(subjectFilterQuery.toLowerCase())"
                                           class="flex items-center space-x-3 p-2 rounded-xl hover:bg-white dark:hover:bg-slate-800 transition-colors cursor-pointer border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                                        <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" 
                                               x-model.number="selectedSubjects"
                                               class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                                        <div class="text-xs">
                                            <strong class="text-slate-800 dark:text-slate-100 font-mono">{{ $subject->subject_code }}</strong>
                                            <span class="text-slate-500 dark:text-slate-400 ml-1.5">— {{ $subject->subject_name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                                <i class="fa-solid fa-floppy-disk mr-2"></i> 
                                <span x-text="mode === 'grade' ? 'Apply to All Grade Streams' : 'Save Stream Customization'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            {{-- Right Column: Collapsible Grade Matrix Overview --}}
            <div class="lg:col-span-7 space-y-4">
                
                {{-- Matrix Search Bar & Global Accordion Toggle Controls --}}
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="relative w-full sm:w-80">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" x-model="matrixSearchQuery" placeholder="Search Grade, Stream, or Subject..." 
                               class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" @click="$dispatch('toggle-matrix-all', true)" 
                                class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                            <i class="fa-solid fa-angles-down mr-1"></i> Expand All
                        </button>
                        <button type="button" @click="$dispatch('toggle-matrix-all', false)" 
                                class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                            <i class="fa-solid fa-angles-up mr-1"></i> Collapse All
                        </button>
                    </div>
                </div>

                {{-- Collapsible Grade Level Cards (Default Collapsed) --}}
                @foreach($gradeLevels as $grade)
                    @php
                        $gradeSearchString = strtolower($grade->name . ' ' . $grade->schoolClasses->pluck('class_name')->implode(' ') . ' ' . $grade->schoolClasses->flatMap(fn($c) => $c->subjects->pluck('subject_name'))->unique()->implode(' '));
                    @endphp

                    <div x-data="{ 
                            isMatrixOpen: false,
                            searchContext: '{{ $gradeSearchString }}',
                            matchesMatrixSearch() {
                                if (!matrixSearchQuery.trim()) return true;
                                return this.searchContext.includes(matrixSearchQuery.toLowerCase().trim());
                            }
                         }" 
                         x-show="matchesMatrixSearch()"
                         @toggle-matrix-all.window="isMatrixOpen = $event.detail"
                         x-effect="if (matrixSearchQuery.trim().length > 0 && matchesMatrixSearch()) isMatrixOpen = true"
                         class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-all">
                        
                        {{-- Clickable Grade Accordion Header --}}
                        <div @click="isMatrixOpen = !isMatrixOpen" 
                             class="px-5 py-3.5 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between cursor-pointer select-none group hover:bg-slate-850 transition-colors">
                            
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-layer-group text-indigo-400 text-sm"></i>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-white">
                                    {{ $grade->name }}
                                </h4>
                                <span class="text-[10px] font-mono bg-slate-800 px-2.5 py-0.5 rounded-full text-slate-300 border border-slate-700">
                                    {{ $grade->schoolClasses->count() }} {{ Str::plural('Stream', $grade->schoolClasses->count()) }}
                                </span>
                            </div>

                            <div class="flex items-center space-x-2">
                                <button type="button" @click.stop="mode = 'grade'; selectedGradeId = '{{ $grade->id }}'; onGradeChange();" 
                                        class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-indigo-600 hover:bg-indigo-700 text-white transition-all">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit Grade Subjects
                                </button>
                                
                                <button type="button" class="p-1.5 text-slate-400 group-hover:text-white transition-transform duration-200" :class="{ 'rotate-180': isMatrixOpen }">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Collapsible Stream Subjects Content --}}
                        <div x-show="isMatrixOpen" x-collapse class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                            @forelse($grade->schoolClasses as $class)
                                @php
                                    $classSearchString = strtolower($class->class_name . ' ' . $class->class_code . ' ' . $class->subjects->pluck('subject_name')->implode(' '));
                                @endphp
                                <div x-show="!matrixSearchQuery || '{{ $classSearchString }}'.includes(matrixSearchQuery.toLowerCase())"
                                     class="p-4 flex flex-col sm:flex-row sm:items-start justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    
                                    <div class="sm:w-1/3 shrink-0">
                                        <div class="flex items-center justify-between">
                                            <strong class="text-slate-800 dark:text-slate-100 font-bold block">{{ $class->class_name }}</strong>
                                            <button type="button" 
                                                    @click="mode = 'stream'; selectedClassId = '{{ $class->id }}'; onClassChange();" 
                                                    class="sm:hidden text-[10px] font-bold text-indigo-600 dark:text-indigo-400">
                                                Customize
                                            </button>
                                        </div>
                                        <span class="font-mono text-[10px] text-slate-400 block mt-0.5">{{ $class->class_code }}</span>
                                        <button type="button" 
                                                @click="mode = 'stream'; selectedClassId = '{{ $class->id }}'; onClassChange();" 
                                                class="hidden sm:inline-flex items-center text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline mt-1">
                                            <i class="fa-solid fa-sliders mr-1 text-[9px]"></i> Customize Stream
                                        </button>
                                    </div>

                                    <div class="sm:w-2/3">
                                        @if($class->subjects->count() > 0)
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($class->subjects as $sub)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                                                        {{ $sub->subject_name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-[11px] italic text-slate-400 flex items-center">
                                                <i class="fa-solid fa-circle-exclamation mr-1"></i> No subjects assigned yet
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-xs italic text-slate-400">
                                    No streams created under {{ $grade->name }}.
                                </div>
                            @endforelse
                        </div>

                    </div>
                @endforeach
            </div>

        </div>
    </div>
@endsection