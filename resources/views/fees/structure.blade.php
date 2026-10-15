{{-- resources/views/fees/structure.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Fee Structure | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Fee Configuration</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage class structures & bulk generate term invoices</p>
        </div>

        @if(isset($currentTerm))
        <div class="mt-4 sm:mt-0">
            <form action="{{ Route::has('fees.structure.process_invoices') ? route('fees.structure.process_invoices') : url('/fees/structure/process-invoices') }}" 
                  method="POST" onsubmit="return confirm('Are you sure you want to charge all students for the {{ $currentTerm->term_name }} term? This will issue invoices to accounts.');">
                @csrf
                <input type="hidden" name="term_id" value="{{ $currentTerm->id }}">
                <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm transition-all">
                    <i class="fa-solid fa-bolt mr-2"></i> CHARGE ALL STUDENTS
                </button>
            </form>
        </div>
        @endif
    </div>
@endsection

@section('content')
    <div x-data="{ selectedGrades: [] }" class="space-y-6">

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Term Filter Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-4 transition-colors">
            <form method="GET" action="{{ Route::has('fees.structure') ? route('fees.structure') : url('/fees/structure') }}" class="flex flex-wrap items-center gap-4">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center">
                    <i class="fa-solid fa-calendar mr-2 text-indigo-500"></i> Target Term:
                </label>
                <select name="term_id" onchange="this.form.submit()" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none">
                    @foreach($terms as $t)
                        <option value="{{ $t->id }}" {{ (isset($currentTerm) && $currentTerm->id == $t->id) ? 'selected' : '' }}>
                            {{ $t->term_name }} ({{ $t->academic_year }})
                        </option>
                    @endforeach
                </select>

                @if(isset($currentTerm) && $currentTerm->is_current)
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        CURRENT ACTIVE TERM
                    </span>
                @endif
            </form>
        </div>

        {{-- Bulk Add Section --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-copy text-emerald-500 mr-2.5"></i> Bulk Apply Fee Structure
                </h3>
            </div>

            <form action="{{ Route::has('fees.structure.bulkStore') ? route('fees.structure.bulkStore') : url('/fees/structure/bulk-store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="term_id" value="{{ $currentTerm->id ?? '' }}">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-6 space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Select Classes / Grades</label>
                            <div class="space-x-2 text-[11px]">
                                <button type="button" @click="selectedGrades = @json($grades)" class="text-indigo-600 dark:text-indigo-400 font-bold">Select All</button>
                                <button type="button" @click="selectedGrades = []" class="text-slate-400 font-bold">Clear</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach($grades as $g)
                                <label class="flex items-center space-x-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" name="grades[]" value="{{ $g }}" x-model="selectedGrades" class="rounded text-indigo-600">
                                    <span>Grade {{ $g }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="lg:col-span-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Fee Description</label>
                            <input type="text" name="fee_name" placeholder="e.g. General Tuition" required
                                   class="w-full px-3.5 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Timing</label>
                                <select name="charge_timing" required class="w-full px-3.5 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                                    <option value="during_term">During Term</option>
                                    <option value="end_of_term">End of Term</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Amount ($)</label>
                                <input type="number" step="0.01" name="amount" placeholder="0.00" required
                                       class="w-full px-3.5 py-2 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                            Apply Structure
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Grades Loop --}}
        @foreach($grades as $grade)
            @php
                $gradeFees = $structures->where('grade', $grade);
                $standardFees = $gradeFees->whereNull('student_id');
                $specialFees = $gradeFees->whereNotNull('student_id');
                $standardTotal = $standardFees->sum('amount');
                $gradeStudents = $students->where('grade', $grade);
            @endphp

            <div x-data="{ open: false }" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                <div @click="open = !open" class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/80 flex items-center justify-between cursor-pointer border-b border-slate-200/80 dark:border-slate-700/60">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fa-solid fa-graduation-cap text-indigo-500 mr-2.5"></i> Grade {{ $grade }}
                    </h3>
                    <div class="flex items-center space-x-3">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                            Base Class Fee: ${{ number_format($standardTotal, 2) }}
                        </span>
                        <i class="fa-solid" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </div>
                </div>

                <div x-show="open" class="divide-y divide-slate-100 dark:divide-slate-700/60 space-y-0" style="display: none;">
                    <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-slate-100 dark:divide-slate-700/60">
                        {{-- Standard Fees --}}
                        <div class="p-6">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3 flex items-center">
                                <i class="fa-solid fa-users text-emerald-500 mr-2"></i> Standard Class Structure
                            </h4>
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-slate-400 border-b border-slate-100 dark:border-slate-700/60">
                                        <th class="text-left pb-2">Description</th>
                                        <th class="text-right pb-2">Amount</th>
                                        <th class="text-center pb-2">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                    @forelse($standardFees as $fee)
                                    <tr>
                                        <td class="py-2.5 font-bold text-slate-800 dark:text-slate-200">
                                            {{ $fee->fee_name }}
                                            <span class="text-[9px] px-1.5 py-0.5 rounded font-semibold ml-1.5 {{ $fee->charge_timing == 'end_of_term' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                                {{ ucfirst(str_replace('_', ' ', $fee->charge_timing ?? 'during_term')) }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($fee->amount, 2) }}</td>
                                        <td class="py-2.5 text-center">
                                            <form action="{{ Route::has('fees.structure.destroy') ? route('fees.structure.destroy', $fee->id) : url('/fees/structure/' . $fee->id) }}" method="POST" onsubmit="return confirm('Remove standard fee?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="py-4 text-center text-slate-400">No standard fees configured.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Special Fees --}}
                        <div class="p-6">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3 flex items-center">
                                <i class="fa-solid fa-user text-amber-500 mr-2"></i> Individual Student Overrides
                            </h4>
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-slate-400 border-b border-slate-100 dark:border-slate-700/60">
                                        <th class="text-left pb-2">Student</th>
                                        <th class="text-left pb-2">Description</th>
                                        <th class="text-right pb-2">Amount</th>
                                        <th class="text-center pb-2">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                    @forelse($specialFees as $fee)
                                    <tr>
                                        <td class="py-2.5"><span class="px-2 py-0.5 rounded font-bold bg-amber-100 text-amber-800 text-[10px]">{{ $fee->student->name }} {{ $fee->student->surname }}</span></td>
                                        <td class="py-2.5 font-bold text-slate-800 dark:text-slate-200">{{ $fee->fee_name }}</td>
                                        <td class="py-2.5 text-right font-mono font-bold text-amber-600">${{ number_format($fee->amount, 2) }}</td>
                                        <td class="py-2.5 text-center">
                                            <form action="{{ Route::has('fees.structure.destroy') ? route('fees.structure.destroy', $fee->id) : url('/fees/structure/' . $fee->id) }}" method="POST" onsubmit="return confirm('Remove special fee?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="py-4 text-center text-slate-400">No individual overrides.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Add Fee Form --}}
                    <div class="p-6 bg-slate-50 dark:bg-slate-900/50">
                        <form action="{{ Route::has('fees.structure.store') ? route('fees.structure.store') : url('/fees/structure/store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                            @csrf
                            <input type="hidden" name="term_id" value="{{ $currentTerm->id ?? '' }}">
                            <input type="hidden" name="grade" value="{{ $grade }}">

                            <div class="lg:col-span-3">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Target</label>
                                <select name="student_id" class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                                    <option value="">-- Entire Grade {{ $grade }} --</option>
                                    @foreach($gradeStudents as $student)
                                        <option value="{{ $student->id }}">{{ $student->surname }}, {{ $student->name }} ({{ $student->student_number }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="lg:col-span-3">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Description</label>
                                <input type="text" name="fee_name" required placeholder="e.g. Tuition, Bus" class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Timing</label>
                                <select name="charge_timing" required class="w-full px-3 py-2 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                                    <option value="during_term">During Term</option>
                                    <option value="end_of_term">End of Term</option>
                                </select>
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Amount ($)</label>
                                <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full px-3 py-2 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                            </div>

                            <div class="lg:col-span-2">
                                <button type="submit" class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">
                                    <i class="fa-solid fa-plus mr-1"></i> Add Fee
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
@endsection