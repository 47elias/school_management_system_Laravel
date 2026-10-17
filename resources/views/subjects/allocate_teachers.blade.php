{{-- resources/views/subjects/allocate_teachers.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Teacher Allocations | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Faculty Teaching Load Allocations</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Assign educators to Grade levels or specific class streams directly from database records</p>
        </div>

        <div class="flex items-center space-x-2.5">
            <a href="{{ Route::has('subjects.index') ? route('subjects.index') : url('/subjects') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all hover:bg-slate-200 dark:hover:bg-slate-600">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Dashboard
            </a>
            <a href="{{ Route::has('subjects.allocate_classes') ? route('subjects.allocate_classes') : url('/subjects/allocate-classes') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-sitemap mr-1.5"></i> Class Allocations
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ 
            assignScope: 'grade', 
            teacherSearch: '', 
            selectedTeacherId: '', 
            selectedTeacherName: '', 
            teacherDropdownOpen: false, 
            searchQuery: '',
            editingStream: null,
            streamModalOpen: false,
            openStreamModal(streamId, streamName, gradeName) {
                this.editingStream = { id: streamId, name: streamName, grade: gradeName };
                this.streamModalOpen = true;
            },
            selectTeacher(id, name) {
                this.selectedTeacherId = id;
                this.selectedTeacherName = name;
                this.teacherDropdownOpen = false;
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
            
            {{-- LEFT COLUMN: SEARCHABLE TEACHER ALLOCATION FORM --}}
            <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                    <div class="flex items-center space-x-2.5">
                        <i class="fa-solid fa-chalkboard-user text-amber-400"></i>
                        <h3 class="text-sm font-bold tracking-tight text-white">Assign Faculty Load</h3>
                    </div>
                    <span class="text-[10px] font-mono bg-amber-950/80 text-amber-300 border border-amber-800/60 px-2.5 py-0.5 rounded-full font-bold">
                        Faculty Hub
                    </span>
                </div>

                {{-- Scope Switchers --}}
                <div class="p-4 bg-slate-50/50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700/60 space-y-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Target Allocation Scope</span>
                    <div class="inline-flex p-1 rounded-2xl bg-slate-200/70 dark:bg-slate-900 text-xs font-bold w-full justify-between">
                        <button type="button" @click="assignScope = 'grade'" :class="assignScope === 'grade' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500'" class="flex-1 py-2 rounded-xl transition-all flex items-center justify-center">
                            <i class="fa-solid fa-layer-group mr-1.5"></i> By Grade Level
                        </button>
                        <button type="button" @click="assignScope = 'stream'" :class="assignScope === 'stream' ? 'bg-white dark:bg-slate-800 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-500'" class="flex-1 py-2 rounded-xl transition-all flex items-center justify-center">
                            <i class="fa-solid fa-chalkboard mr-1.5"></i> Per Single Stream
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ Route::has('subject-assignments.store') ? route('subject-assignments.store') : url('/subject-assignments') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="scope" :value="assignScope">

                    {{-- SEARCHABLE TEACHER SELECTOR --}}
                    <div class="relative">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Select Educator <span class="text-rose-500">*</span>
                        </label>
                        <input type="hidden" name="teacher_id" :value="selectedTeacherId" required>

                        <div @click="teacherDropdownOpen = !teacherDropdownOpen" 
                             class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 cursor-pointer flex items-center justify-between">
                            <span x-text="selectedTeacherName ? selectedTeacherName : '-- Search & Choose Educator --'" :class="{ 'text-slate-400': !selectedTeacherName }"></span>
                            <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
                        </div>

                        {{-- Search Popup Dropdown --}}
                        <div x-show="teacherDropdownOpen" @click.away="teacherDropdownOpen = false" x-cloak 
                             class="absolute z-30 left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl p-3 space-y-2">
                            
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" x-model="teacherSearch" placeholder="Type educator name or ID..." 
                                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="max-h-48 overflow-y-auto space-y-1">
                                @forelse($teachers as $teacher)
                                    @php 
                                        $tName = is_object($teacher) ? ($teacher->name ?? '') : (string)$teacher; 
                                        $tId = is_object($teacher) ? ($teacher->id ?? '') : '';
                                        $tSearch = strtolower($tName . ' ' . $tId);
                                    @endphp
                                    <div x-show="!teacherSearch || '{{ $tSearch }}'.includes(teacherSearch.toLowerCase().trim())" 
                                         @click="selectTeacher('{{ $tId }}', '{{ addslashes($tName) }}')"
                                         class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer flex items-center justify-between transition-colors">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-[10px] font-bold">
                                                <i class="fa-solid fa-user-tie"></i>
                                            </div>
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $tName }}</span>
                                        </div>
                                        <span class="text-[9px] font-mono text-slate-400">#{{ $tId }}</span>
                                    </div>
                                @empty
                                    <div class="p-3 text-center text-xs text-slate-400 italic">No educators found in system.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Target Grade Level --}}
                    <div x-show="assignScope === 'grade'">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Target Grade Level <span class="text-rose-500">*</span>
                        </label>
                        <select name="grade" :required="assignScope === 'grade'" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Choose Grade Level --</option>
                            @forelse($gradeLevels as $gl)
                                @php 
                                    $glName = is_object($gl) ? ($gl->name ?? $gl->grade_level ?? $gl->grade ?? '') : (string)$gl;
                                @endphp
                                @if(!empty($glName))
                                    <option value="{{ $glName }}">{{ $glName }} (Master Educator for all streams)</option>
                                @endif
                            @empty
                                <option value="" disabled>No Grade Levels found in database</option>
                            @endforelse
                        </select>
                    </div>

                    {{-- Target Single Stream --}}
                    <div x-show="assignScope === 'stream'" style="display: none;">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Target Class Stream <span class="text-rose-500">*</span>
                        </label>
                        <select name="class_id" :required="assignScope === 'stream'" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="">-- Choose Class Stream --</option>
                            @if(isset($schoolClasses) && count($schoolClasses) > 0)
                                @foreach($schoolClasses as $sc)
                                    <option value="{{ $sc->id }}">{{ $sc->class_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Target Subject --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Target Subject <span class="text-rose-500">*</span>
                        </label>
                        <select name="subject_id" required class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="">-- Select Subject --</option>
                            @foreach($subjects as $sb)
                                <option value="{{ $sb->id }}">{{ $sb->subject_name }} ({{ $sb->subject_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Academic Year --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Academic Session</label>
                        <input type="text" name="academic_year" value="{{ date('Y') }}" readonly class="w-full px-3.5 py-2 text-xs font-mono font-bold rounded-xl bg-slate-100 dark:bg-slate-900 text-slate-500 cursor-not-allowed border border-slate-200 dark:border-slate-700">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm transition-all flex items-center justify-center">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Save Faculty Teaching Load
                        </button>
                    </div>
                </form>
            </div>

            {{-- RIGHT COLUMN: DYNAMIC DATABASE GRADE ACCORDION DIRECTORY --}}
            <div class="lg:col-span-7 space-y-4">
                
                {{-- Global Search Bar --}}
                <div class="relative w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="Filter by Educator, Subject, Grade, or Stream..." 
                           class="w-full pl-10 pr-4 py-2.5 text-xs rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500 shadow-sm">
                </div>

                @forelse($gradeLevels as $index => $gl)
                    @php
                        $gId = is_object($gl) ? ($gl->id ?? null) : null;
                        $gDisplay = is_object($gl) ? ($gl->name ?? $gl->grade_level ?? $gl->grade ?? '') : (string)$gl;

                        // Find sub-streams belonging to this Grade Level in the DB
                        $matchingStreams = collect();
                        $allClasses = isset($schoolClasses) ? $schoolClasses : collect();

                        foreach ($allClasses as $sc) {
                            $cId = is_object($sc) ? ($sc->id ?? null) : null;
                            $cName = is_object($sc) ? ($sc->class_name ?? $sc->name ?? '') : (string)$sc;
                            $cGradeId = is_object($sc) ? ($sc->grade_level_id ?? null) : null;
                            $cGradeStr = is_object($sc) ? ($sc->grade_level ?? $sc->grade ?? '') : '';

                            $isMatch = false;

                            if ($gId && $cGradeId && $gId == $cGradeId) {
                                $isMatch = true;
                            } elseif (!empty($cGradeStr) && strtolower(trim($cGradeStr)) === strtolower(trim($gDisplay))) {
                                $isMatch = true;
                            } elseif (!empty($cName) && strtolower(trim($cName)) !== strtolower(trim($gDisplay))) {
                                if (Str::startsWith(strtolower(trim($cName)), strtolower(trim($gDisplay)))) {
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

                        // Master Grade Educator Allocations matching exact DB grade string
                        $masterGradeAllocations = $assignments->filter(function($a) use ($gDisplay) {
                            if (!empty($a->class_id)) return false;
                            return !empty($a->grade) && strtolower(trim($a->grade)) === strtolower(trim($gDisplay));
                        });

                        // Stream-Specific Educator Allocations
                        $streamAllocations = $assignments->filter(function($a) use ($uniqueStreams) {
                            if (empty($a->class_id)) return false;
                            return $uniqueStreams->pluck('id')->contains($a->class_id) || 
                                   $uniqueStreams->pluck('name')->contains($a->schoolClass->class_name ?? '');
                        });

                        $searchHaystack = strtolower(
                            $gDisplay . ' ' . 
                            $masterGradeAllocations->pluck('teacher.name')->implode(' ') . ' ' . 
                            $masterGradeAllocations->pluck('subject.subject_name')->implode(' ') . ' ' . 
                            $streamAllocations->pluck('teacher.name')->implode(' ') . ' ' . 
                            $uniqueStreams->pluck('name')->implode(' ')
                        );
                    @endphp

                    {{-- COLLAPSIBLE GRADE CARD --}}
                    <div x-data="{ isOpen: false, activeSubTab: 'master' }" 
                         x-show="!searchQuery || '{{ addslashes($searchHaystack) }}'.includes(searchQuery.toLowerCase().trim())"
                         class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-all">
                        
                        {{-- Accordion Header --}}
                        <div @click="isOpen = !isOpen" class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between cursor-pointer select-none hover:bg-slate-850 transition-colors">
                            <div class="flex items-center space-x-3.5">
                                <div class="w-9 h-9 rounded-2xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 font-bold text-xs font-mono shrink-0">
                                    #{{ $index + 1 }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                        <span>{{ $gDisplay }}</span>
                                        <span class="text-[10px] font-mono font-bold bg-amber-950 px-2.5 py-0.5 rounded-full text-amber-300 border border-amber-800/60">
                                            {{ $uniqueStreams->count() }} {{ Str::plural('Stream', $uniqueStreams->count()) }}
                                        </span>
                                    </h3>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $masterGradeAllocations->count() }} Grade Master Educators &bull; {{ $streamAllocations->count() }} Stream Educators
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-200" :class="{ 'rotate-180': isOpen }"></i>
                            </div>
                        </div>

                        {{-- Accordion Body --}}
                        <div x-show="isOpen" x-collapse class="p-6 border-t border-slate-100 dark:border-slate-700/60 space-y-4">
                            
                            {{-- Sub Tabs --}}
                            <div class="flex border-b border-slate-200 dark:border-slate-700/60 space-x-6 text-xs font-bold">
                                <button type="button" @click="activeSubTab = 'master'" 
                                        :class="activeSubTab === 'master' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 pb-2' : 'text-slate-400 pb-2 hover:text-slate-700 dark:hover:text-slate-200'">
                                    <i class="fa-solid fa-user-tie mr-1.5"></i> {{ $gDisplay }} Master Educators ({{ $masterGradeAllocations->count() }})
                                </button>
                                <button type="button" @click="activeSubTab = 'streams'" 
                                        :class="activeSubTab === 'streams' ? 'border-b-2 border-amber-500 text-amber-600 dark:text-amber-400 pb-2' : 'text-slate-400 pb-2 hover:text-slate-700 dark:hover:text-slate-200'">
                                    <i class="fa-solid fa-chalkboard-user mr-1.5"></i> Stream Educators Breakout ({{ $uniqueStreams->count() }})
                                </button>
                            </div>

                            {{-- SUB TAB 1: MASTER GRADE TEACHERS --}}
                            <div x-show="activeSubTab === 'master'">
                                <div class="w-full">
                                    <table class="w-full text-xs table-fixed divide-y divide-slate-100 dark:divide-slate-700/60">
                                        <thead>
                                            <tr class="text-slate-400 uppercase font-bold text-[10px] border-b border-slate-200 dark:border-slate-700">
                                                <th class="text-left pb-2 w-1/3">Assigned Educator</th>
                                                <th class="text-left pb-2 w-1/3">Subject</th>
                                                <th class="text-center pb-2 w-1/5">Academic Year</th>
                                                <th class="text-right pb-2 w-20">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                            @forelse($masterGradeAllocations as $ga)
                                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30">
                                                    <td class="py-2.5 font-bold text-slate-800 dark:text-slate-100 break-words pr-2">
                                                        @if($ga->teacher)
                                                            <div class="flex items-center space-x-2">
                                                                <div class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px] font-bold shrink-0">
                                                                    <i class="fa-solid fa-user-tie"></i>
                                                                </div>
                                                                <span class="truncate">{{ $ga->teacher->name }}</span>
                                                            </div>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-700/60 text-slate-400 dark:text-slate-500">
                                                                <i class="fa-solid fa-user-slash mr-1 text-[9px]"></i> Unassigned
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 font-bold text-slate-800 dark:text-slate-100 break-words pr-2">
                                                        {{ $ga->subject->subject_name ?? 'N/A' }}
                                                    </td>
                                                    <td class="py-2.5 text-center font-mono text-[10px] font-bold text-slate-600 dark:text-slate-400">
                                                        {{ $ga->academic_year }}
                                                    </td>
                                                    <td class="py-2.5 text-right">
                                                        @if($ga->teacher)
                                                            <form action="{{ Route::has('subject-assignments.destroy') ? route('subject-assignments.destroy', $ga->id) : url('/subject-assignments/' . $ga->id) }}" method="POST" onsubmit="return confirm('Unassign {{ addslashes($ga->teacher->name) }} from {{ addslashes($ga->subject->subject_name ?? 'this subject') }}?');">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="text-rose-500 p-1 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg" title="Unassign Teacher">
                                                                    <i class="fa-solid fa-user-xmark"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button type="button" @click="assignScope = 'grade'; selectedTeacherId = ''; selectedTeacherName = ''; $nextTick(() => { document.querySelector('select[name=grade]').value = '{{ addslashes($gDisplay) }}'; document.querySelector('select[name=subject_id]').value = '{{ $ga->subject_id }}'; })" class="px-2 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 text-[10px] font-bold transition-all flex items-center justify-end ml-auto">
                                                                <i class="fa-solid fa-user-plus mr-1"></i> Assign
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="py-6 text-center text-slate-400 italic">No Master Educators allocated for {{ $gDisplay }}. Use the left form to assign educators at the Grade level.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- SUB TAB 2: STREAMS BREAKOUT & SINGLE STREAM ASSIGNMENTS --}}
                            <div x-show="activeSubTab === 'streams'" style="display: none;">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @forelse($uniqueStreams as $stData)
                                        @php
                                            $sName = $stData['name'];
                                            $sClassId = $stData['id'];

                                            $specificStreamAssignments = $streamAllocations->filter(function($sa) use ($sClassId, $sName) {
                                                if ($sClassId && isset($sa->class_id) && $sa->class_id == $sClassId) {
                                                    return true;
                                                }
                                                return ($sa->schoolClass->class_name ?? '') === $sName;
                                            });
                                        @endphp

                                        <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/40 flex flex-col justify-between space-y-3">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-xs font-black text-slate-800 dark:text-slate-100 flex items-center">
                                                    <i class="fa-solid fa-chalkboard text-amber-500 mr-2"></i> {{ $sName }}
                                                </h4>
                                                
                                                <button type="button" @click="openStreamModal('{{ $sClassId }}', '{{ addslashes($sName) }}', '{{ addslashes($gDisplay) }}')" 
                                                        class="px-2.5 py-1 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold transition-all flex items-center shadow-sm">
                                                    <i class="fa-solid fa-user-plus mr-1"></i> Assign Teacher
                                                </button>
                                            </div>

                                            <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 p-3 rounded-xl border border-slate-100 dark:border-slate-700">
                                                <div class="text-[9px] uppercase tracking-wider font-bold text-amber-500 mb-1">Stream Educators:</div>
                                                @forelse($specificStreamAssignments as $sAssign)
                                                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-800 dark:text-slate-200 py-1 border-b border-slate-50 dark:border-slate-700/50 last:border-none">
                                                        <div class="truncate pr-2">
                                                            <span class="text-slate-400 font-semibold block text-[10px]">{{ $sAssign->subject->subject_name ?? 'N/A' }}</span>
                                                            @if($sAssign->teacher)
                                                                <span class="text-amber-600 dark:text-amber-400 font-bold"><i class="fa-solid fa-user-tie text-[9px] mr-1"></i> {{ $sAssign->teacher->name }}</span>
                                                            @else
                                                                <span class="text-slate-400 font-medium italic text-[10px]">Unassigned</span>
                                                            @endif
                                                        </div>
                                                        @if($sAssign->teacher)
                                                            <form action="{{ Route::has('subject-assignments.destroy') ? route('subject-assignments.destroy', $sAssign->id) : url('/subject-assignments/' . $sAssign->id) }}" method="POST" onsubmit="return confirm('Unassign {{ addslashes($sAssign->teacher->name) }}?');" class="shrink-0">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-[10px]" title="Unassign Teacher"><i class="fa-solid fa-user-xmark"></i></button>
                                                            </form>
                                                        @else
                                                            <button type="button" @click="openStreamModal('{{ $sClassId }}', '{{ addslashes($sName) }}', '{{ addslashes($gDisplay) }}')" class="text-amber-500 hover:text-amber-600 text-[10px] font-bold shrink-0">
                                                                <i class="fa-solid fa-user-plus mr-1"></i> Assign
                                                            </button>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <span class="text-[10px] text-slate-400 italic">No stream-specific educators assigned</span>
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
                        No Grade Levels found in database.
                    </div>
                @endforelse
            </div>

        </div>

        {{-- SINGLE STREAM TEACHER ASSIGNMENT MODAL --}}
        <div x-show="streamModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div @click.away="streamModalOpen = false" 
                 class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full overflow-hidden shadow-xl border border-slate-200 dark:border-slate-700">
                
                <div class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between border-b border-slate-800">
                    <h3 class="text-sm font-bold text-white flex items-center">
                        <i class="fa-solid fa-user-plus text-amber-400 mr-2"></i> Assign Educator: <span x-text="editingStream?.name" class="ml-1 text-amber-400"></span>
                    </h3>
                    <button type="button" @click="streamModalOpen = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <form method="POST" action="{{ Route::has('subject-assignments.store') ? route('subject-assignments.store') : url('/subject-assignments') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="scope" value="stream">
                    <input type="hidden" name="class_id" :value="editingStream?.id">

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Select Educator <span class="text-rose-500">*</span>
                        </label>
                        <select name="teacher_id" required class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                            <option value="">-- Choose Educator --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                            Target Subject <span class="text-rose-500">*</span>
                        </label>
                        <select name="subject_id" required class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                            <option value="">-- Choose Subject --</option>
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
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm">
                            Save Stream Educator
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection