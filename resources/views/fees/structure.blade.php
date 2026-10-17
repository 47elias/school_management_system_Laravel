{{-- resources/views/fees/structure.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Fee Configuration Studio | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div x-data class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Fee Architecture Studio</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure master Form-level fees (auto-applies across all streams like Form 2A, 2B, 2C), class streams, and student overrides</p>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            {{-- Button to Open Universal Fee Studio Modal --}}
            <button @click="$dispatch('open-fee-modal')" 
                    class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-plus mr-2"></i> ADD FEE STRUCTURE
            </button>

            @if(isset($currentTerm))
            <form action="{{ Route::has('fees.structure.process_invoices') ? route('fees.structure.process_invoices') : url('/fees/structure/process-invoices') }}" 
                  method="POST" onsubmit="return confirm('Are you sure you want to charge all students for {{ $currentTerm->term_name }}? This action will auto-apply all Grade, Stream, and Student fee structures to student ledgers.');">
                @csrf
                <input type="hidden" name="term_id" value="{{ $currentTerm->id }}">
                <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm transition-all">
                    <i class="fa-solid fa-bolt mr-2"></i> CHARGE ALL STUDENTS
                </button>
            </form>
            @endif
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ 
            showFeeModal: false,
            modalScope: 'grade',
            selectedGrades: [], 
            searchQuery: '',
            expandAllState: false
         }" 
         @open-fee-modal.window="showFeeModal = true"
         class="space-y-6">

        {{-- Flash Alerts --}}
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

        {{-- Top Bar: Target Academic Term Selector & Scope Legend --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-4 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form method="GET" action="{{ Route::has('fees.structure') ? route('fees.structure') : url('/fees/structure') }}" class="flex flex-wrap items-center gap-3">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center">
                    <i class="fa-solid fa-calendar-days mr-2 text-indigo-500"></i> Target Term:
                </label>
                <select name="term_id" onchange="this.form.submit()" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($terms as $t)
                        <option value="{{ $t->id }}" {{ (isset($currentTerm) && $currentTerm->id == $t->id) ? 'selected' : '' }}>
                            {{ $t->term_name }} ({{ $t->academic_year }})
                        </option>
                    @endforeach
                </select>

                @if(isset($currentTerm) && $currentTerm->is_current)
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                        ACTIVE SESSION
                    </span>
                @endif
            </form>

            <div class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400 font-medium">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-bold border border-indigo-200 dark:border-indigo-800/40" title="Auto-cascades to Form 2A, 2B, 2C">
                    <i class="fa-solid fa-sitemap mr-1.5"></i> Form Level (Auto-Cascades)
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 font-bold border border-emerald-200 dark:border-emerald-800/40">
                    <i class="fa-solid fa-diagram-project mr-1.5"></i> Stream Specific
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 font-bold border border-amber-200 dark:border-amber-800/40">
                    <i class="fa-solid fa-user-gear mr-1.5"></i> Student Override
                </span>
            </div>
        </div>

        {{-- Search & Global Accordion Controls --}}
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="relative w-full sm:w-96">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model="searchQuery" placeholder="Search by Form (e.g. Form 2), Stream (2A), or Fee Name..." 
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center space-x-2">
                <button @click="expandAllState = true; $dispatch('toggle-all', true)" 
                        class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    <i class="fa-solid fa-angles-down mr-1"></i> Expand All Forms
                </button>
                <button @click="expandAllState = false; $dispatch('toggle-all', false)" 
                        class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                    <i class="fa-solid fa-angles-up mr-1"></i> Collapse All
                </button>
            </div>
        </div>

        {{--
            NORMALIZE PARENT FORM LISTING & INCLUDE EMPTY CLASSES:
            Extract top-level Form names (e.g., "Form 2") so there is ONLY ONE main outer accordion block per Form level.
        --}}
        @php
            if (isset($gradeLevels) && count($gradeLevels) > 0) {
                $parentGrades = $gradeLevels;
            } else {
                $rawGrades = collect($grades);
                $cleanedGradeNames = $rawGrades->map(function($g) {
                    return trim(preg_replace('/\s+[A-Z0-9]$/i', '', $g));
                })->unique()->values();

                $parentGrades = $cleanedGradeNames;
            }
        @endphp

        {{-- MAIN SINGLE ACCORDION PER PARENT FORM (Form 1, Form 2, Form 3...) --}}
        @forelse($parentGrades as $gradeIndex => $gradeObj)
            @php
                $gradeName = is_object($gradeObj) ? $gradeObj->name : $gradeObj;
                $gradeId = is_object($gradeObj) ? $gradeObj->id : null;
                $levelOrder = is_object($gradeObj) ? ($gradeObj->level_order ?? ($gradeIndex + 1)) : ($gradeIndex + 1);
                
                // 1. Fetch ALL class streams belonging to this Grade/Form from database (INCLUDING EMPTY CLASSES)
                if (is_object($gradeObj) && isset($gradeObj->schoolClasses) && count($gradeObj->schoolClasses) > 0) {
                    $streamsInGrade = $gradeObj->schoolClasses;
                } elseif (isset($schoolClasses) && count($schoolClasses) > 0) {
                    $streamsInGrade = $schoolClasses->filter(function($sc) use ($gradeId, $gradeName) {
                        if ($gradeId && isset($sc->grade_level_id) && $sc->grade_level_id == $gradeId) {
                            return true;
                        }
                        $cName = $sc->class_name ?? $sc->name ?? '';
                        $cGrade = $sc->grade_level ?? $sc->grade ?? '';
                        return $cGrade === $gradeName || $cName === $gradeName || Str::startsWith($cName, $gradeName);
                    });
                } elseif (isset($classes) && count($classes) > 0) {
                    $streamsInGrade = $classes->filter(function($c) use ($gradeId, $gradeName) {
                        if ($gradeId && isset($c->grade_level_id) && $c->grade_level_id == $gradeId) {
                            return true;
                        }
                        $cName = $c->class_name ?? $c->name ?? '';
                        $cGrade = $c->grade_level ?? $c->grade ?? '';
                        return $cGrade === $gradeName || $cName === $gradeName || Str::startsWith($cName, $gradeName);
                    });
                } else {
                    $streamsInGrade = collect();
                }

                // 2. Fetch enrolled students for this form level
                $formStudents = $students->filter(function($s) use ($gradeName, $gradeId) {
                    if ($gradeId && isset($s->schoolClass->grade_level_id) && $s->schoolClass->grade_level_id == $gradeId) {
                        return true;
                    }
                    $cName = $s->schoolClass->class_name ?? $s->grade ?? '';
                    return $cName === $gradeName || Str::startsWith($cName, $gradeName);
                });

                // Fallback: If no class models exist in database, group students by class name
                if ($streamsInGrade->count() === 0 && $formStudents->count() > 0) {
                    $streamsInGrade = $formStudents->groupBy(function($s) {
                        return $s->schoolClass->class_name ?? $s->grade ?? 'General Stream';
                    });
                }

                // Fetch fee structures configured for this Form level
                $gradeFees = $structures->filter(function($f) use ($gradeName) {
                    return $f->grade === $gradeName || Str::startsWith($f->grade, $gradeName);
                });

                $masterFormFees = $gradeFees->whereNull('student_id')->whereNull('class_id');
                $streamFees = $gradeFees->whereNotNull('class_id');
                $studentOverrides = $gradeFees->whereNotNull('student_id');
                
                $masterTotal = $masterFormFees->sum('amount');

                // Generate stream names string for header badge
                if ($streamsInGrade->count() > 0) {
                    $firstStream = $streamsInGrade->first();
                    if (is_object($firstStream) && isset($firstStream->class_name)) {
                        $streamNamesString = $streamsInGrade->pluck('class_name')->implode(', ');
                    } elseif (is_object($firstStream) && isset($firstStream->name)) {
                        $streamNamesString = $streamsInGrade->pluck('name')->implode(', ');
                    } else {
                        $streamNamesString = $streamsInGrade->keys()->implode(', ');
                    }
                } else {
                    $streamNamesString = '';
                }

                $feeNamesSearch = $gradeFees->pluck('fee_name')->implode(' ');
                $searchString = strtolower($gradeName . ' ' . $feeNamesSearch . ' ' . $streamNamesString);
            @endphp

            <div x-data="{ 
                    isOpen: false,
                    gradeNameStr: '{{ strtolower($gradeName) }}',
                    searchData: '{{ $searchString }}',
                    matchesSearch() {
                        if (!searchQuery.trim()) return true;
                        const q = searchQuery.toLowerCase().trim();
                        return this.searchData.includes(q);
                    }
                 }" 
                 x-show="matchesSearch()"
                 @toggle-all.window="isOpen = $event.detail"
                 x-effect="if (searchQuery.trim().length > 0 && matchesSearch()) isOpen = true"
                 class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-all">
                
                {{-- Form Level Accordion Header Banner --}}
                <div @click="isOpen = !isOpen" 
                     class="px-6 py-4 bg-slate-900 dark:bg-slate-950 text-white flex items-center justify-between cursor-pointer select-none group hover:bg-slate-850 transition-colors">
                    
                    <div class="flex items-center space-x-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-400 font-bold text-sm font-mono shrink-0">
                            #{{ $levelOrder }}
                        </div>
                        <div>
                            <h3 class="text-base font-bold tracking-tight text-white flex items-center">
                                {{ Str::contains($gradeName, 'Form') ? $gradeName : 'Form ' . $gradeName }}
                                <span class="ml-3 text-[11px] font-semibold text-indigo-300 bg-indigo-950/80 px-2.5 py-0.5 rounded-full border border-indigo-800/60 font-mono">
                                    {{ $streamsInGrade->count() }} {{ Str::plural('Stream', $streamsInGrade->count()) }} ({{ $streamNamesString ?: 'None Created' }})
                                </span>
                            </h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                {{ $formStudents->count() }} Total Enrolled Students &bull; 
                                Master Base Fee: <strong class="text-emerald-400 font-mono">${{ number_format($masterTotal, 2) }}</strong> (Auto-Cascades)
                            </p>
                        </div>
                    </div>

                    {{-- Right Header Actions --}}
                    <div class="flex items-center space-x-3" @click.stop>
                        <span class="text-xs font-black text-emerald-400 font-mono hidden sm:inline-block px-3 py-1 rounded-xl bg-slate-800 border border-slate-700">
                            Base: ${{ number_format($masterTotal, 2) }}
                        </span>

                        <button @click="$dispatch('open-fee-modal'); modalScope = 'grade'; selectedGrades = ['{{ $gradeName }}']" 
                                class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                            <i class="fa-solid fa-plus mr-1.5 text-[10px]"></i> Add Fee
                        </button>

                        <button @click="isOpen = !isOpen" class="p-2 rounded-xl text-slate-400 group-hover:text-white transition-transform duration-200" :class="{ 'rotate-180': isOpen }">
                            <i class="fa-solid fa-chevron-down text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Expandable Content for this Form --}}
                <div x-show="isOpen" x-collapse x-data="{ activeSubTab: 'master' }" class="p-6 border-t border-slate-100 dark:border-slate-700/60 space-y-6">

                    {{-- Sub-Tabs inside Form --}}
                    <div class="flex border-b border-slate-200 dark:border-slate-700/60 space-x-6 text-xs font-bold">
                        <button type="button" @click="activeSubTab = 'master'" 
                                :class="activeSubTab === 'master' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 pb-2.5' : 'text-slate-400 pb-2.5 hover:text-slate-700 dark:hover:text-slate-200'">
                            <i class="fa-solid fa-sitemap mr-1.5"></i> Master Form Base Fees ({{ $masterFormFees->count() }})
                        </button>
                        <button type="button" @click="activeSubTab = 'streams'" 
                                :class="activeSubTab === 'streams' ? 'border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 pb-2.5' : 'text-slate-400 pb-2.5 hover:text-slate-700 dark:hover:text-slate-200'">
                            <i class="fa-solid fa-diagram-project mr-1.5"></i> Streams Breakout ({{ $streamsInGrade->count() }})
                        </button>
                        <button type="button" @click="activeSubTab = 'student_overrides'" 
                                :class="activeSubTab === 'student_overrides' ? 'border-b-2 border-amber-500 text-amber-600 dark:text-amber-400 pb-2.5' : 'text-slate-400 pb-2.5 hover:text-slate-700 dark:hover:text-slate-200'">
                            <i class="fa-solid fa-user-gear mr-1.5"></i> Student Overrides ({{ $studentOverrides->count() }})
                        </button>
                    </div>

                    {{-- TAB 1: MASTER FORM BASE FEES (Auto-applies to Form 2A, 2B, 2C...) --}}
                    <div x-show="activeSubTab === 'master'">
                        <div class="mb-4 p-3.5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/40 flex items-center justify-between text-xs text-indigo-900 dark:text-indigo-200">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-network-wired text-indigo-500"></i>
                                <span>Master fees set on <strong>{{ $gradeName }}</strong> automatically cascade to all enrolled students across all streams ({{ $streamNamesString ?: 'All Streams' }}).</span>
                            </div>
                            <span class="font-bold">Total Base: ${{ number_format($masterTotal, 2) }}</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-slate-400 border-b border-slate-200 dark:border-slate-700/60 uppercase font-bold text-[10px]">
                                        <th class="text-left pb-2.5">Fee Item Description</th>
                                        <th class="text-center pb-2.5">Target Scope</th>
                                        <th class="text-center pb-2.5">Charge Timing</th>
                                        <th class="text-right pb-2.5">Amount</th>
                                        <th class="text-center pb-2.5">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                    @forelse($masterFormFees as $fee)
                                    <tr>
                                        <td class="py-3 font-bold text-slate-800 dark:text-slate-200">
                                            {{ $fee->fee_name }}
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="text-[10px] px-2.5 py-0.5 rounded-full font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/40">
                                                {{ $gradeName }} (All Streams)
                                            </span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $fee->charge_timing == 'end_of_term' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300' : 'bg-sky-100 text-sky-800 dark:bg-sky-950/50 dark:text-sky-300' }}">
                                                {{ ucfirst(str_replace('_', ' ', $fee->charge_timing ?? 'during_term')) }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($fee->amount, 2) }}</td>
                                        <td class="py-3 text-center">
                                            <form action="{{ Route::has('fees.structure.destroy') ? route('fees.structure.destroy', $fee->id) : url('/fees/structure/' . $fee->id) }}" method="POST" onsubmit="return confirm('Remove master fee?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors" title="Delete Fee">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-slate-400">
                                            No master Form-wide fees configured for {{ $gradeName }}.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($masterFormFees->count() > 0)
                                <tfoot class="border-t border-slate-200 dark:border-slate-700/60 font-bold">
                                    <tr>
                                        <td colspan="3" class="py-3 text-right text-slate-600 dark:text-slate-400">MASTER FORM BASE TOTAL:</td>
                                        <td class="py-3 text-right font-mono text-emerald-600 dark:text-emerald-400">${{ number_format($masterTotal, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- TAB 2: STREAMS BREAKOUT GRID (Includes Empty Classes with 0 Students) --}}
                    <div x-show="activeSubTab === 'streams'" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            @forelse($streamsInGrade as $key => $streamItem)
                                @php
                                    if (is_object($streamItem) && (isset($streamItem->class_name) || isset($streamItem->name))) {
                                        // It's a SchoolClass model (shows even if 0 students enrolled!)
                                        $sClass = $streamItem;
                                        $sName = $sClass->class_name ?? $sClass->name;
                                        $sClassId = $sClass->id;
                                        
                                        // Count students enrolled in this exact class stream
                                        $sStudentsCount = $students->filter(function($st) use ($sClassId, $sName) {
                                            return (isset($st->class_id) && $st->class_id == $sClassId) 
                                                || (isset($st->school_class_id) && $st->school_class_id == $sClassId)
                                                || ($st->schoolClass->class_name ?? $st->grade ?? '') === $sName;
                                        })->count();
                                    } else {
                                        // Grouped student collection fallback
                                        $sClass = null;
                                        $sName = $key;
                                        $sClassId = null;
                                        $sStudentsCount = count($streamItem);
                                    }

                                    $specificStreamFees = $streamFees->filter(function($f) use ($sClassId, $sName) {
                                        if ($sClassId && isset($f->class_id) && $f->class_id == $sClassId) {
                                            return true;
                                        }
                                        return ($f->schoolClass->class_name ?? $f->class_name) === $sName;
                                    });

                                    $streamAddonTotal = $specificStreamFees->sum('amount');
                                    $effectiveTotal = $masterTotal + $streamAddonTotal;
                                @endphp
                                
                                <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/40 flex flex-col justify-between space-y-4 hover:border-indigo-500 transition-all">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="text-base font-black text-slate-800 dark:text-slate-100 flex items-center">
                                                <i class="fa-solid fa-chalkboard text-emerald-500 mr-2"></i> {{ $sName }}
                                            </h4>
                                            <span class="text-[10px] font-mono text-slate-400 block mt-0.5">
                                                {{ $sStudentsCount }} {{ Str::plural('Student', $sStudentsCount) }} Enrolled
                                                @if($sStudentsCount === 0)
                                                    <span class="ml-1 text-amber-500 font-bold">(Empty Class)</span>
                                                @endif
                                            </span>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-extrabold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                            Effective: ${{ number_format($effectiveTotal, 2) }}
                                        </span>
                                    </div>

                                    <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900/50 p-3 rounded-xl border border-slate-100 dark:border-slate-700/50">
                                        <div class="flex justify-between items-center text-slate-500">
                                            <span>Master {{ $gradeName }} Base:</span>
                                            <span class="font-bold font-mono text-slate-800 dark:text-slate-200">${{ number_format($masterTotal, 2) }}</span>
                                        </div>
                                        
                                        @forelse($specificStreamFees as $sf)
                                            <div class="flex justify-between items-center text-emerald-600 dark:text-emerald-400 font-medium">
                                                <span class="flex items-center"><i class="fa-solid fa-plus text-[9px] mr-1"></i> {{ $sf->fee_name }}:</span>
                                                <span class="font-bold font-mono">${{ number_format($sf->amount, 2) }}</span>
                                            </div>
                                        @empty
                                            <p class="text-[11px] text-slate-400 italic">No specific stream add-ons (inherits Form base fee only).</p>
                                        @endforelse
                                    </div>

                                    <div class="pt-1 flex items-center justify-between text-xs">
                                        <button @click="$dispatch('open-fee-modal'); modalScope = 'stream'" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                                            <i class="fa-solid fa-plus-circle mr-1"></i> Add Stream Fee
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-3 py-8 text-center text-slate-400 bg-slate-50 dark:bg-slate-900/30 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700">
                                    No sub-class streams currently active under {{ $gradeName }}.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- TAB 3: INDIVIDUAL STUDENT OVERRIDES --}}
                    <div x-show="activeSubTab === 'student_overrides'" style="display: none;">
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-slate-400 border-b border-slate-200 dark:border-slate-700/60 uppercase font-bold text-[10px]">
                                        <th class="text-left pb-2.5">Student Name</th>
                                        <th class="text-left pb-2.5">Stream</th>
                                        <th class="text-left pb-2.5">Fee Description</th>
                                        <th class="text-center pb-2.5">Timing</th>
                                        <th class="text-right pb-2.5">Amount</th>
                                        <th class="text-center pb-2.5">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                    @forelse($studentOverrides as $fee)
                                    <tr>
                                        <td class="py-3">
                                            <span class="px-2.5 py-1 rounded-lg font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40 text-[11px]">
                                                {{ $fee->student->surname ?? '' }}, {{ $fee->student->name ?? 'Student' }} ({{ $fee->student->student_number ?? 'N/A' }})
                                            </span>
                                        </td>
                                        <td class="py-3 font-semibold text-slate-500">
                                            {{ $fee->student->schoolClass->class_name ?? $fee->student->grade ?? 'N/A' }}
                                        </td>
                                        <td class="py-3 font-bold text-slate-800 dark:text-slate-200">{{ $fee->fee_name }}</td>
                                        <td class="py-3 text-center">
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-sky-100 text-sky-800 dark:bg-sky-950/50 dark:text-sky-300">
                                                {{ ucfirst(str_replace('_', ' ', $fee->charge_timing ?? 'during_term')) }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-right font-mono font-bold text-amber-600 dark:text-amber-400">${{ number_format($fee->amount, 2) }}</td>
                                        <td class="py-3 text-center">
                                            <form action="{{ Route::has('fees.structure.destroy') ? route('fees.structure.destroy', $fee->id) : url('/fees/structure/' . $fee->id) }}" method="POST" onsubmit="return confirm('Remove student override?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-slate-400">
                                            No individual student fee overrides recorded for {{ $gradeName }}.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- INLINE QUICK ADD FORM --}}
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/60">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-3 flex items-center">
                            <i class="fa-solid fa-plus-circle text-indigo-500 mr-2"></i> Quick Add Fee Item to {{ $gradeName }}
                        </h4>

                        <form action="{{ Route::has('fees.structure.store') ? route('fees.structure.store') : url('/fees/structure/store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                            @csrf
                            <input type="hidden" name="term_id" value="{{ $currentTerm->id ?? '' }}">
                            <input type="hidden" name="grade" value="{{ $gradeName }}">

                            <div class="lg:col-span-3">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Target Scope</label>
                                <select name="student_id" class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Master {{ $gradeName }} (All Streams)</option>
                                    <optgroup label="Single Student Overrides">
                                        @foreach($formStudents as $student)
                                            <option value="{{ $student->id }}">{{ $student->surname }}, {{ $student->name }} ({{ $student->schoolClass->class_name ?? $student->grade }})</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            <div class="lg:col-span-3">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Fee Description</label>
                                <input type="text" name="fee_name" required placeholder="e.g. Tuition, Exam Fee" class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Charge Timing</label>
                                <select name="charge_timing" required class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="during_term">During Term</option>
                                    <option value="end_of_term">End of Term</option>
                                </select>
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Amount ($)</label>
                                <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3 py-2 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div class="lg:col-span-2">
                                <button type="submit" class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                                    <i class="fa-solid fa-plus mr-1"></i> Add Fee
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-8">
                <i class="fa-solid fa-folder-open text-4xl text-slate-300 dark:text-slate-600 mb-3 block"></i>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">No Grade Levels / Forms Found</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Create Form levels first in Class Architecture to configure fee structures.</p>
            </div>
        @endforelse

        {{-- UNIVERSAL FEE CREATION MODAL STUDIO --}}
        <div x-show="showFeeModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                <div @click="showFeeModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle bg-white dark:bg-slate-800 rounded-3xl shadow-2xl z-10 border border-slate-200 dark:border-slate-700">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-sliders text-indigo-500 mr-2"></i> Universal Fee Configuration Studio
                        </h3>
                        <button type="button" @click="showFeeModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i class="fa-solid fa-xmark"></i></button>
                    </div>

                    {{-- Scope Switcher in Modal --}}
                    <div class="my-4 inline-flex p-1 rounded-2xl bg-slate-100 dark:bg-slate-900 text-xs font-bold w-full justify-between">
                        <button type="button" @click="modalScope = 'grade'" 
                                :class="modalScope === 'grade' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500'"
                                class="flex-1 py-2 rounded-xl transition-all">
                            Form Level (Master)
                        </button>
                        <button type="button" @click="modalScope = 'stream'" 
                                :class="modalScope === 'stream' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-500'"
                                class="flex-1 py-2 rounded-xl transition-all">
                            Class Stream
                        </button>
                        <button type="button" @click="modalScope = 'student'" 
                                :class="modalScope === 'student' ? 'bg-white dark:bg-slate-800 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-500'"
                                class="flex-1 py-2 rounded-xl transition-all">
                            Single Student
                        </button>
                    </div>

                    {{-- FORM LEVEL MODAL CREATION --}}
                    <div x-show="modalScope === 'grade'">
                        <form action="{{ Route::has('fees.structure.bulkStore') ? route('fees.structure.bulkStore') : url('/fees/structure/bulk-store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="term_id" value="{{ $currentTerm->id ?? '' }}">

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Target Forms</label>
                                <div class="grid grid-cols-3 gap-2 max-h-36 overflow-y-auto p-2 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                                    @if(isset($gradeLevels) && count($gradeLevels) > 0)
                                        @foreach($gradeLevels as $gl)
                                            @php $glName = is_object($gl) ? $gl->name : $gl; @endphp
                                            <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                <input type="checkbox" name="grades[]" value="{{ $glName }}" x-model="selectedGrades" class="rounded text-indigo-600">
                                                <span>{{ $glName }}</span>
                                            </label>
                                        @endforeach
                                    @else
                                        @foreach($parentGrades as $g)
                                            <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                <input type="checkbox" name="grades[]" value="{{ $g }}" x-model="selectedGrades" class="rounded text-indigo-600">
                                                <span>{{ Str::contains($g, 'Form') ? $g : 'Form ' . $g }}</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Fee Description</label>
                                <input type="text" name="fee_name" required placeholder="e.g. General Tuition Fee" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Charge Timing</label>
                                    <select name="charge_timing" required class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-semibold">
                                        <option value="during_term">During Term</option>
                                        <option value="end_of_term">End of Term</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Amount ($)</label>
                                    <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                                </div>
                            </div>

                            <div class="pt-3 flex items-center justify-end space-x-2 border-t border-slate-100 dark:border-slate-700">
                                <button type="button" @click="showFeeModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold">Cancel</button>
                                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm">Save Master Fee</button>
                            </div>
                        </form>
                    </div>

                    {{-- STREAM LEVEL MODAL CREATION --}}
                    <div x-show="modalScope === 'stream'" style="display: none;">
                        <form action="{{ Route::has('fees.structure.store') ? route('fees.structure.store') : url('/fees/structure/store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="term_id" value="{{ $currentTerm->id ?? '' }}">

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Select Stream / Class</label>
                                <select name="class_id" required class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-semibold">
                                    <option value="">-- Choose Class Stream --</option>
                                    @if(isset($schoolClasses) && count($schoolClasses) > 0)
                                        @foreach($schoolClasses as $sc)
                                            <option value="{{ $sc->id }}">{{ $sc->class_name }} (Form {{ $sc->grade_level ?? $sc->grade }})</option>
                                        @endforeach
                                    @elseif(isset($classes) && count($classes) > 0)
                                        @foreach($classes as $c)
                                            <option value="{{ $c->id }}">{{ $c->name ?? $c->class_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Fee Description</label>
                                <input type="text" name="fee_name" required placeholder="e.g. Science Lab Practical Fee" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Charge Timing</label>
                                    <select name="charge_timing" required class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-semibold">
                                        <option value="during_term">During Term</option>
                                        <option value="end_of_term">End of Term</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Amount ($)</label>
                                    <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                                </div>
                            </div>

                            <div class="pt-3 flex items-center justify-end space-x-2 border-t border-slate-100 dark:border-slate-700">
                                <button type="button" @click="showFeeModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold">Cancel</button>
                                <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm">Save Stream Fee</button>
                            </div>
                        </form>
                    </div>

                    {{-- STUDENT LEVEL MODAL CREATION --}}
                    <div x-show="modalScope === 'student'" style="display: none;">
                        <form action="{{ Route::has('fees.structure.store') ? route('fees.structure.store') : url('/fees/structure/store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="term_id" value="{{ $currentTerm->id ?? '' }}">

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Select Student</label>
                                <select name="student_id" required class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-semibold">
                                    <option value="">-- Choose Student --</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">
                                            {{ $student->surname }}, {{ $student->name }} ({{ $student->student_number }} - {{ $student->schoolClass->class_name ?? $student->grade }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Fee Description</label>
                                <input type="text" name="fee_name" required placeholder="e.g. Bus Fare Transport" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Charge Timing</label>
                                    <select name="charge_timing" required class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 font-semibold">
                                        <option value="during_term">During Term</option>
                                        <option value="end_of_term">End of Term</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Amount ($)</label>
                                    <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                                </div>
                            </div>

                            <div class="pt-3 flex items-center justify-end space-x-2 border-t border-slate-100 dark:border-slate-700">
                                <button type="button" @click="showFeeModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold">Cancel</button>
                                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm">Save Student Fee</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection