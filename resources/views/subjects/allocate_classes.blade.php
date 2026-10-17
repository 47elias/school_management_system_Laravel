{{-- resources/views/subjects/allocate_classes.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Class Subject Allocations | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Class & Stream Subject Allocations</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Batch assign subjects to Form levels (Form 1, Form 2...) or manage specific class stream overrides</p>
        </div>

        <div class="flex items-center space-x-2.5">
            <a href="{{ Route::has('subjects.index') ? route('subjects.index') : url('/subjects') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all hover:bg-slate-200 dark:hover:bg-slate-600">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Dashboard
            </a>
            <a href="{{ Route::has('subjects.allocate_teachers') ? route('subjects.allocate_teachers') : url('/subjects/allocate-teachers') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-chalkboard-user mr-1.5"></i> Allocate Teachers &rarr;
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ 
            assignScope: 'grade', 
            itemType: 'subject', 
            searchQuery: '',
            subjectSearch: '',
            selectedSubjects: [],
            selectAll: false,
            toggleSelectAll() {
                this.selectAll = !this.selectAll;
                if (this.selectAll) {
                    this.selectedSubjects = [
                        @foreach($subjects as $sb)
                            '{{ $sb->id }}',
                        @endforeach
                    ];
                } else {
                    this.selectedSubjects = [];
                }
            },
            editingStream: null,
            streamModalOpen: false,
            openStreamModal(streamId, streamName, gradeName) {
                this.editingStream = { id: streamId, name: streamName, grade: gradeName };
                this.streamModalOpen = true;
            }
         }" 
         class="space-y-6">

        {{-- Flash Messages --}}
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

        {{-- Validation Errors Display --}}
        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 shadow-sm">
                <div class="flex items-center space-x-3 mb-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                    <h4 class="text-xs font-bold uppercase tracking-wider">Please fix the following errors:</h4>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 font-medium pl-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {{-- LEFT COLUMN: BATCH ALLOCATION FORM --}}
            <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center space-x-2.5">
                        <i class="fa-solid fa-sitemap text-emerald-400"></i>
                        <h3 class="text-sm font-bold tracking-tight text-white">Batch Allocation Studio</h3>
                    </div>
                    <span class="text-[10px] font-mono bg-emerald-950/80 text-emerald-300 border border-emerald-800/60 px-2.5 py-0.5 rounded-full font-bold">
                        Multi-Select
                    </span>
                </div>

                {{-- Scope Switchers --}}
                <div class="p-4 bg-slate-50/50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700/60 space-y-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">1. Target Scope</span>
                        <div class="inline-flex p-1 rounded-2xl bg-slate-200/70 dark:bg-slate-900 text-xs font-bold w-full justify-between">
                            <button type="button" @click="assignScope = 'grade'" :class="assignScope === 'grade' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500'" class="flex-1 py-2 rounded-xl transition-all flex items-center justify-center">
                                <i class="fa-solid fa-layer-group mr-1.5"></i> By Grade Level
                            </button>
                            <button type="button" @click="assignScope = 'stream'" :class="assignScope === 'stream' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-500'" class="flex-1 py-2 rounded-xl transition-all flex items-center justify-center">
                                <i class="fa-solid fa-chalkboard mr-1.5"></i> Single Stream
                            </button>
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">2. Allocation Type</span>
                        <div class="inline-flex p-1 rounded-2xl bg-slate-200/70 dark:bg-slate-900 text-xs font-bold w-full justify-between">
                            <button type="button" @click="itemType = 'subject'" :class="itemType === 'subject' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500'" class="flex-1 py-2 rounded-xl transition-all flex items-center justify-center">
                                <i class="fa-solid fa-check-double mr-1.5"></i> Multiple Subjects
                            </button>
                            <button type="button" @click="itemType = 'combination'" :class="itemType === 'combination' ? 'bg-white dark:bg-slate-800 text-purple-600 dark:text-purple-400 shadow-sm' : 'text-slate-500'" class="flex-1 py-2 rounded-xl transition-all flex items-center justify-center">
                                <i class="fa-solid fa-box-archive mr-1.5"></i> Subject Package
                            </button>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ Route::has('class-subjects.store') ? route('class-subjects.store') : url('/class-subjects') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="scope" :value="assignScope">
                    <input type="hidden" name="allocation_type" :value="itemType">

                    {{-- Target Grade Level --}}
                    <div x-show="assignScope === 'grade'">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Target Grade / Form Level <span class="text-rose-500">*</span>
                        </label>
                        <select name="grade" :required="assignScope === 'grade'" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Choose Grade Level --</option>
                            @if(isset($gradeLevels) && count($gradeLevels) > 0)
                                @foreach($gradeLevels as $gl)
                                    @php 
                                        $glName = is_object($gl) ? ($gl->name ?? '') : (string)$gl;
                                        $displayGl = Str::startsWith(strtolower(trim($glName)), 'form') ? trim($glName) : 'Form ' . trim($glName);
                                    @endphp
                                    <option value="{{ $displayGl }}">{{ $displayGl }} (Applies to all streams in this grade)</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Target Single Stream --}}
                    <div x-show="assignScope === 'stream'" style="display: none;">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Target Class Stream <span class="text-rose-500">*</span>
                        </label>
                        <select name="class_id" :required="assignScope === 'stream'" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Choose Class Stream --</option>
                            @if(isset($schoolClasses) && count($schoolClasses) > 0)
                                @foreach($schoolClasses as $sc)
                                    <option value="{{ $sc->id }}">{{ $sc->class_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- MULTI-SELECT SUBJECT CHECKBOX GRID --}}
                    <div x-show="itemType === 'subject'">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                                Select Subjects to Allocate <span class="text-rose-500">*</span>
                            </label>
                            <button type="button" @click="toggleSelectAll()" class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                                <span x-text="selectAll ? 'Deselect All' : 'Select All'"></span>
                            </button>
                        </div>

                        <div class="relative mb-2">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" x-model="subjectSearch" placeholder="Filter subject list..." class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none">
                        </div>

                        <div class="space-y-1.5 max-h-56 overflow-y-auto p-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700">
                            @forelse($subjects as $sb)
                                @php $sbSearch = strtolower($sb->subject_name . ' ' . $sb->subject_code . ' ' . $sb->type); @endphp
                                <label x-show="!subjectSearch || '{{ $sbSearch }}'.includes(subjectSearch.toLowerCase().trim())" 
                                       class="flex items-center justify-between p-2 rounded-xl hover:bg-white dark:hover:bg-slate-800 transition-colors cursor-pointer border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                                    <div class="flex items-center space-x-2.5 min-w-0 pr-2">
                                        <input type="checkbox" name="subject_ids[]" value="{{ $sb->id }}" x-model="selectedSubjects" 
                                               class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 dark:bg-slate-800 border-slate-300 dark:border-slate-600">
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $sb->subject_name }}</span>
                                    </div>
                                    <div class="flex items-center space-x-1 shrink-0">
                                        <span class="font-mono text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                            {{ $sb->subject_code }}
                                        </span>
                                    </div>
                                </label>
                            @empty
                                <div class="p-4 text-center text-xs text-slate-400 italic">No subjects available.</div>
                            @endforelse
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1 block">
                            Selected: <strong x-text="selectedSubjects.length" class="text-indigo-600 dark:text-indigo-400"></strong> subject(s)
                        </span>
                    </div>

                    {{-- COMBINATION PACKAGE SELECTOR --}}
                    <div x-show="itemType === 'combination'" style="display: none;">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Select Subject Combination Package <span class="text-rose-500">*</span>
                        </label>
                        <select name="combination_id" :required="itemType === 'combination'" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                            <option value="">-- Choose Combination Package --</option>
                            @if(isset($combinations) && count($combinations) > 0)
                                @foreach($combinations as $cb)
                                    <option value="{{ $cb->id }}">{{ $cb->name }} ({{ $cb->code ?? 'N/A' }})</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- ACADEMIC YEAR --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Academic Session</label>
                        <input type="text" name="academic_year" value="{{ date('Y') }}" readonly class="w-full px-3.5 py-2 text-xs font-mono font-bold rounded-xl bg-slate-100 dark:bg-slate-900 text-slate-500 cursor-not-allowed border border-slate-200 dark:border-slate-700">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all flex items-center justify-center">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Save Class Allocations
                        </button>
                    </div>
                </form>
            </div>

            {{-- RIGHT COLUMN: EACH DISTINCT GRADE LEVEL (FORM 1, FORM 2...) & ITS SUB-STREAMS --}}
            <div class="lg:col-span-7 space-y-4">
                
                {{-- Search Filter --}}
                <div class="relative w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="Filter Grade Levels or Streams..." 
                           class="w-full pl-10 pr-4 py-2.5 text-xs rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                </div>

                @php
                    // Build distinct grade list (Form 1, Form 2, Form 3...)
                    $gradesList = collect();

                    if (isset($gradeLevels) && count($gradeLevels) > 0) {
                        foreach ($gradeLevels as $gl) {
                            $gId = is_object($gl) ? ($gl->id ?? null) : null;
                            $gName = is_object($gl) ? ($gl->name ?? '') : (string)$gl;
                            $gOrder = is_object($gl) ? ($gl->level_order ?? 0) : 0;
                            
                            $displayGl = Str::startsWith(strtolower(trim($gName)), 'form') 
                                ? trim($gName) 
                                : 'Form ' . trim($gName);

                            $gradesList->push([
                                'id' => $gId,
                                'raw_name' => trim($gName),
                                'display_name' => $displayGl,
                                'order' => $gOrder
                            ]);
                        }
                    } else {
                        // Fallback: extract distinct grades from classes
                        $allClasses = isset($schoolClasses) ? $schoolClasses : collect();
                        $extracted = $allClasses->map(function($sc) {
                            $cName = is_object($sc) ? ($sc->class_name ?? $sc->name ?? '') : (string)$sc;
                            if (preg_match('/^(Form\s*\d+|\d+)/i', $cName, $matches)) {
                                $m = $matches[1];
                                return Str::startsWith(strtolower($m), 'form') ? $m : 'Form ' . $m;
                            }
                            return $cName;
                        })->filter()->unique()->values();

                        foreach($extracted as $idx => $extName) {
                            $displayGl = Str::startsWith(strtolower(trim($extName)), 'form') ? trim($extName) : 'Form ' . trim($extName);
                            $gradesList->push([
                                'id' => null,
                                'raw_name' => $extName,
                                'display_name' => $displayGl,
                                'order' => $idx + 1
                            ]);
                        }
                    }

                    // Sort by order/name
                    $gradesList = $gradesList->sortBy('order')->values();
                @endphp

                @forelse($gradesList as $index => $gradeInfo)
                    @php
                        $gId = $gradeInfo['id'];
                        $gRaw = $gradeInfo['raw_name'];
                        $gDisplay = $gradeInfo['display_name'];
                        $gNum = preg_replace('/[^0-9]/', '', $gDisplay);

                        // Find sub-streams belonging to this Grade Level
                        $matchingStreams = collect();
                        $allClasses = isset($schoolClasses) ? $schoolClasses : collect();

                        foreach ($allClasses as $sc) {
                            $cId = is_object($sc) ? ($sc->id ?? null) : null;
                            $cName = is_object($sc) ? ($sc->class_name ?? $sc->name ?? '') : (string)$sc;
                            $cGradeId = is_object($sc) ? ($sc->grade_level_id ?? null) : null;
                            $cGradeStr = is_object($sc) ? ($sc->grade_level ?? $sc->grade ?? '') : '';

                            $cleanCName = strtolower(trim($cName));
                            $cleanGDisplay = strtolower(trim($gDisplay));
                            $cleanGRaw = strtolower(trim($gRaw));

                            // Skip if class_name is identical to parent grade
                            if ($cleanCName === $cleanGDisplay || $cleanCName === $cleanGRaw) {
                                continue;
                            }

                            $isMatch = false;

                            if ($gId && $cGradeId && $gId == $cGradeId) {
                                $isMatch = true;
                            } elseif (!empty($cGradeStr) && (
                                strtolower(trim($cGradeStr)) === $cleanGRaw || 
                                strtolower(trim($cGradeStr)) === $cleanGDisplay ||
                                ($gNum && preg_replace('/[^0-9]/', '', $cGradeStr) === $gNum)
                            )) {
                                $isMatch = true;
                            } elseif (!empty($cName)) {
                                if (Str::startsWith($cleanCName, $cleanGDisplay) || 
                                    ($gNum && preg_match('/^(form\s*' . $gNum . '|' . $gNum . ')[\s\-A-Z0-9]/i', $cleanCName))) {
                                    $isMatch = true;
                                }
                            }

                            if ($isMatch) {
                                $matchingStreams->push([
                                    'id' => $cId,
                                    'name' => $cName
                                ]);
                            }
                        }

                        $uniqueStreams = $matchingStreams->unique('name')->values();

                        // Master Grade Allocations
                        $masterGradeAllocations = $assignments->filter(function($a) use ($gRaw, $gDisplay, $gNum) {
                            if (!empty($a->class_id)) return false;
                            
                            $aGrade = $a->grade ?? '';
                            if (empty($aGrade)) return false;

                            return strtolower(trim($aGrade)) === strtolower(trim($gRaw)) ||
                                   strtolower(trim($aGrade)) === strtolower(trim($gDisplay)) ||
                                   ($gNum && preg_replace('/[^0-9]/', '', $aGrade) === $gNum);
                        });

                        // Stream-Specific Allocations
                        $streamAllocations = $assignments->filter(function($a) use ($uniqueStreams) {
                            if (empty($a->class_id)) return false;
                            return $uniqueStreams->pluck('id')->contains($a->class_id) || 
                                   $uniqueStreams->pluck('name')->contains($a->schoolClass->class_name ?? '');
                        });

                        $searchHaystack = strtolower($gDisplay . ' ' . $uniqueStreams->pluck('name')->implode(' '));
                    @endphp

                    {{-- COLLAPSIBLE GRADE ACCORDION CARD (FORM 1, FORM 2, FORM 3...) --}}
                    <div x-data="{ isOpen: false, activeSubTab: 'master' }" 
                         x-show="!searchQuery || '{{ $searchHaystack }}'.includes(searchQuery.toLowerCase().trim())"
                         class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-all">
                        
                        {{-- Accordion Header --}}
                        <div @click="isOpen = !isOpen" class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between cursor-pointer select-none hover:bg-slate-850 transition-colors">
                            <div class="flex items-center space-x-3.5">
                                <div class="w-9 h-9 rounded-2xl bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-400 font-bold text-xs font-mono shrink-0">
                                    #{{ $index + 1 }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                        <span>{{ $gDisplay }}</span>
                                        <span class="text-[10px] font-mono font-bold bg-indigo-950 px-2.5 py-0.5 rounded-full text-indigo-300 border border-indigo-800/60">
                                            {{ $uniqueStreams->count() }} {{ Str::plural('Stream', $uniqueStreams->count()) }}
                                        </span>
                                    </h3>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $masterGradeAllocations->count() }} Master Grade Subjects &bull; {{ $streamAllocations->count() }} Stream Overrides
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-200" :class="{ 'rotate-180': isOpen }"></i>
                            </div>
                        </div>

                        {{-- Accordion Content --}}
                        <div x-show="isOpen" x-collapse class="p-6 border-t border-slate-100 dark:border-slate-700/60 space-y-4">
                            
                            {{-- Sub Tabs --}}
                            <div class="flex border-b border-slate-200 dark:border-slate-700/60 space-x-6 text-xs font-bold">
                                <button type="button" @click="activeSubTab = 'master'" 
                                        :class="activeSubTab === 'master' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 pb-2' : 'text-slate-400 pb-2 hover:text-slate-700 dark:hover:text-slate-200'">
                                    <i class="fa-solid fa-layer-group mr-1.5"></i> {{ $gDisplay }} Master Subjects ({{ $masterGradeAllocations->count() }})
                                </button>
                                <button type="button" @click="activeSubTab = 'streams'" 
                                        :class="activeSubTab === 'streams' ? 'border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 pb-2' : 'text-slate-400 pb-2 hover:text-slate-700 dark:hover:text-slate-200'">
                                    <i class="fa-solid fa-chalkboard mr-1.5"></i> Streams Breakout ({{ $uniqueStreams->count() }})
                                </button>
                            </div>

                            {{-- SUB TAB 1: MASTER GRADE SUBJECTS --}}
                            <div x-show="activeSubTab === 'master'">
                                <div class="w-full">
                                    <table class="w-full text-xs table-fixed divide-y divide-slate-100 dark:divide-slate-700/60">
                                        <thead>
                                            <tr class="text-slate-400 uppercase font-bold text-[10px] border-b border-slate-200 dark:border-slate-700">
                                                <th class="text-left pb-2 w-1/3">Subject Title</th>
                                                <th class="text-center pb-2 w-1/4">Code</th>
                                                <th class="text-center pb-2 w-1/4">Type</th>
                                                <th class="text-right pb-2 w-12">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                            @forelse($masterGradeAllocations as $ga)
                                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30">
                                                    <td class="py-2.5 font-bold text-slate-800 dark:text-slate-100 break-words pr-2">
                                                        {{ $ga->subject->subject_name ?? 'N/A' }}
                                                    </td>
                                                    <td class="py-2.5 text-center font-mono text-[10px] font-bold text-slate-600 dark:text-slate-400">
                                                        {{ $ga->subject->subject_code ?? 'N/A' }}
                                                    </td>
                                                    <td class="py-2.5 text-center text-slate-500 font-semibold">
                                                        {{ $ga->subject->type ?? 'Core' }}
                                                    </td>
                                                    <td class="py-2.5 text-right">
                                                        <form action="{{ Route::has('class-subjects.destroy') ? route('class-subjects.destroy', $ga->id) : url('/class-subjects/' . $ga->id) }}" method="POST" onsubmit="return confirm('Unassign subject from {{ $gDisplay }}?');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="text-rose-500 p-1 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg"><i class="fa-solid fa-trash-can"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="py-6 text-center text-slate-400 italic">No master subjects allocated to {{ $gDisplay }}. Use the left form to batch select and add subjects.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- SUB TAB 2: STREAMS BREAKOUT & SINGLE STREAM OVERRIDES --}}
                            <div x-show="activeSubTab === 'streams'" style="display: none;">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @forelse($uniqueStreams as $stData)
                                        @php
                                            $sName = $stData['name'];
                                            $sClassId = $stData['id'];

                                            $specificStreamSubjects = $streamAllocations->filter(function($sa) use ($sClassId, $sName) {
                                                if ($sClassId && isset($sa->class_id) && $sa->class_id == $sClassId) {
                                                    return true;
                                                }
                                                return ($sa->schoolClass->class_name ?? '') === $sName;
                                            });
                                        @endphp

                                        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/40 flex flex-col justify-between space-y-3">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-xs font-black text-slate-800 dark:text-slate-100 flex items-center">
                                                    <i class="fa-solid fa-chalkboard text-emerald-500 mr-2"></i> {{ $sName }}
                                                </h4>
                                                
                                                {{-- Single Stream Edit Action --}}
                                                <button type="button" @click="openStreamModal('{{ $sClassId }}', '{{ $sName }}', '{{ $gDisplay }}')" 
                                                        class="px-2.5 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold transition-all flex items-center shadow-sm">
                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit Stream
                                                </button>
                                            </div>

                                            <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 p-3 rounded-xl border border-slate-100 dark:border-slate-700">
                                                <div class="text-[9px] uppercase tracking-wider font-bold text-slate-400 mb-1">Inherited {{ $gDisplay }} Subjects:</div>
                                                @forelse($masterGradeAllocations as $mSub)
                                                    <div class="flex items-center justify-between text-[11px] font-medium text-slate-700 dark:text-slate-300">
                                                        <span class="truncate"><i class="fa-solid fa-check text-indigo-500 mr-1"></i> {{ $mSub->subject->subject_name ?? 'N/A' }}</span>
                                                        <span class="text-[9px] font-mono text-slate-400">Master</span>
                                                    </div>
                                                @empty
                                                    <span class="text-[10px] text-slate-400 italic">None inherited</span>
                                                @endforelse

                                                <div class="text-[9px] uppercase tracking-wider font-bold text-emerald-500 mt-2 mb-1">Stream Specific Overrides:</div>
                                                @forelse($specificStreamSubjects as $sSub)
                                                    <div class="flex items-center justify-between text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                                        <span class="truncate"><i class="fa-solid fa-plus text-[9px] mr-1"></i> {{ $sSub->subject->subject_name ?? 'N/A' }}</span>
                                                        <form action="{{ Route::has('class-subjects.destroy') ? route('class-subjects.destroy', $sSub->id) : url('/class-subjects/' . $sSub->id) }}" method="POST" onsubmit="return confirm('Remove stream override?');" class="inline-block">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="text-rose-500 hover:text-rose-700 text-[10px]"><i class="fa-solid fa-xmark"></i></button>
                                                        </form>
                                                    </div>
                                                @empty
                                                    <span class="text-[10px] text-slate-400 italic">No stream specific overrides</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-span-2 py-4 text-center text-xs text-slate-400 italic">No streams defined under {{ $gDisplay }}.</div>
                                    @endforelse
                                </div>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 text-slate-400">
                        No Grade Levels found in system setup.
                    </div>
                @endforelse
            </div>

        </div>

        {{-- SINGLE STREAM EDIT MODAL --}}
        <div x-show="streamModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div @click.away="streamModalOpen = false" 
                 class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full overflow-hidden shadow-xl border border-slate-200 dark:border-slate-700">
                
                <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                    <h3 class="text-sm font-bold text-white flex items-center">
                        <i class="fa-solid fa-pen-to-square text-emerald-400 mr-2"></i> Edit Stream: <span x-text="editingStream?.name" class="ml-1 text-emerald-400"></span>
                    </h3>
                    <button type="button" @click="streamModalOpen = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form method="POST" action="{{ Route::has('class-subjects.store') ? route('class-subjects.store') : url('/class-subjects') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="scope" value="stream">
                    <input type="hidden" name="allocation_type" value="subject">
                    <input type="hidden" name="class_id" :value="editingStream?.id">

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Add Subject specifically to <span x-text="editingStream?.name" class="font-bold text-indigo-600 dark:text-indigo-400"></span>
                        </label>
                        <select name="subject_id" required class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                            <option value="">-- Select Subject --</option>
                            @foreach($subjects as $sb)
                                <option value="{{ $sb->id }}">{{ $sb->subject_name }} ({{ $sb->subject_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Academic Session</label>
                        <input type="text" name="academic_year" value="{{ date('Y') }}" readonly class="w-full px-3.5 py-2 text-xs font-mono font-bold rounded-xl bg-slate-100 dark:bg-slate-900 text-slate-500 cursor-not-allowed">
                    </div>

                    <div class="pt-2 flex items-center justify-end space-x-2">
                        <button type="button" @click="streamModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm">
                            Save Stream Override
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection