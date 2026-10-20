{{-- resources/views/teacher/marks/manage.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Manage Marks | ' . ($assignment->subject->subject_name ?? $assignment->subject->name ?? 'Subject'))

@push('styles')
<style>
    /* Printing Layout Styles */
    @media print {
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }
        body {
            background: white !important;
            color: black !important;
            font-family: Arial, sans-serif !important;
        }
        .no-print, header, nav, sidebar, footer, .sidebar, .navbar, .main-header, .main-footer {
            display: none !important;
        }
        .print-only {
            display: block !important;
        }
        .print-container {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
            background: white !important;
        }
        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        th, td {
            border: 1px solid #000 !important;
            padding: 8px 10px !important;
            color: #000 !important;
            background: transparent !important;
            font-size: 11pt !important;
        }
        th {
            background-color: #f2f2f2 !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
        }
        input.score-input, input.remarks-input {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            outline: none !important;
            padding: 0 !important;
            font-size: 11pt !important;
            color: black !important;
        }
        .print-header {
            display: block !important;
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 12px;
        }
        .print-signatures {
            display: flex !important;
            justify-content: space-between !important;
            margin-top: 50px !important;
            page-break-inside: avoid;
        }
        .sig-box {
            border-top: 1.5px solid #000;
            width: 30%;
            text-align: center;
            padding-top: 6px;
            font-size: 10pt;
            font-weight: bold;
        }
    }

    .print-only {
        display: none;
    }
</style>
@endpush

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-indigo-600 dark:text-indigo-400"></i>
                <span>Manage Student Marks</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Class: <strong class="text-slate-700 dark:text-slate-200">{{ $assignment->schoolClass->class_name ?? $assignment->schoolClass->name ?? 'Class' }}</strong> &bull; 
                Subject: <strong class="text-slate-700 dark:text-slate-200">{{ $assignment->subject->subject_name ?? $assignment->subject->name ?? 'Subject' }}</strong>
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ route('teacher.subjects') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 shadow-xs">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Subjects
            </a>
            <button type="button" onclick="window.print()" 
                    class="inline-flex items-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-black dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-print mr-2 text-amber-400"></i> Print Marksheet
            </button>
        </div>
    </div>
@endsection

@section('content')
<div x-data="{
    passMark: {{ $assignment->subject->pass_mark ?? 50 }},
    scores: {
        @foreach($students as $student)
            @php
                $existingMark = $marks->where('student_id', $student->id)->first();
                $scoreVal = $existingMark ? $existingMark->score : '';
            @endphp
            '{{ $student->id }}': '{{ $scoreVal }}',
        @endforeach
    },
    getStatus(score) {
        if (score === '' || score === null || isNaN(score)) return 'PENDING';
        return parseFloat(score) >= this.passMark ? 'PASSED' : 'FAILED';
    }
}" class="space-y-6">

    {{-- PRINT HEADER (ONLY VISIBLE WHEN PRINTING) --}}
    <div class="print-only print-header">
        <div style="float: right; text-align: right;">
            <p style="margin: 0;"><strong>Academic Year:</strong> {{ $assignment->academic_year ?? date('Y') }}</p>
            <p style="margin: 0;"><strong>Date:</strong> {{ date('d M Y') }}</p>
        </div>
        <h2 style="margin: 0; font-size: 18pt; font-weight: 800; text-transform: uppercase;">
            {{ config('app.name', 'Portal') }} - Official Marksheet
        </h2>
        <p style="margin: 4px 0 0 0;">
            Subject: <strong>{{ $assignment->subject->subject_name ?? $assignment->subject->name ?? 'N/A' }}</strong> | 
            Class: <strong>{{ $assignment->schoolClass->class_name ?? $assignment->schoolClass->name ?? 'N/A' }}</strong>
        </p>
        <p style="margin: 2px 0 0 0;">
            Instructor: <strong>{{ Auth::user()->name }}</strong>
        </p>
    </div>

    {{-- System Flash Notifications --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-xs no-print">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider">Success</h4>
                    <p class="text-xs font-medium mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 shadow-xs no-print">
            <div class="flex items-start space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg mt-0.5"></i>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider">Please check form errors:</h4>
                    <ul class="mt-1 space-y-0.5 list-disc list-inside text-xs font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Quick Stat Widgets --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 no-print">
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Pass Mark</span>
                <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                    {{ $assignment->subject->pass_mark ?? 50 }}%
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-bullseye"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">Class Size</span>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                    {{ $students->count() }} Students
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">Max Score</span>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                    100 Marks
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-trophy"></i>
            </div>
        </div>
    </div>

    {{-- Main Marks Entry Card --}}
    <form action="{{ route('teacher.marks.store') }}" method="POST">
        @csrf
        <input type="hidden" name="subject_assignment_id" value="{{ $assignment->id }}">

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-xs overflow-hidden transition-colors">
            
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/90 flex flex-col sm:flex-row sm:items-center justify-between gap-3 no-print">
                <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100 flex items-center space-x-2">
                    <i class="fa-solid fa-list-check text-indigo-500"></i>
                    <span>Student Assessment Roster</span>
                </h3>

                <button type="submit" 
                        class="inline-flex items-center px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-xs">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Save All Marks
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200/80 dark:border-slate-700/60">
                        <tr>
                            <th class="py-3.5 px-4 text-center text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-12">#</th>
                            <th class="py-3.5 px-4 text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Student Details</th>
                            <th class="py-3.5 px-4 text-center text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-40">Score / 100</th>
                            <th class="py-3.5 px-4 text-center text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32 no-print">Status</th>
                            <th class="py-3.5 px-4 text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Comment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                        @forelse($students as $index => $student)
                            @php
                                $existingMark = $marks->where('student_id', $student->id)->first();
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="py-3.5 px-4 text-center align-middle font-bold text-slate-400 dark:text-slate-500 text-xs">
                                    {{ $index + 1 }}
                                </td>

                                <td class="py-3.5 px-4 align-middle">
                                    <div class="font-bold text-slate-800 dark:text-slate-100 uppercase text-xs">
                                        {{ $student->name }} {{ $student->surname }}
                                    </div>
                                    <div class="text-[11px] font-mono font-semibold text-indigo-600 dark:text-indigo-400 mt-0.5">
                                        {{ $student->student_number ?? $student->ec_number ?? $student->student_id ?? 'STU-'.$student->id }}
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 align-middle text-center">
                                    <input type="number"
                                           name="marks[{{ $student->id }}][score]"
                                           x-model="scores['{{ $student->id }}']"
                                           min="0" 
                                           max="100"
                                           placeholder="0-100"
                                           class="score-input w-24 text-center font-black text-sm py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                                </td>

                                <td class="py-3.5 px-4 align-middle text-center no-print">
                                    <template x-if="getStatus(scores['{{ $student->id }}']) === 'PASSED'">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                                            <i class="fa-solid fa-check text-[9px] mr-1"></i> Pass
                                        </span>
                                    </template>

                                    <template x-if="getStatus(scores['{{ $student->id }}']) === 'FAILED'">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50">
                                            <i class="fa-solid fa-xmark text-[9px] mr-1"></i> Below pass mark
                                        </span>
                                    </template>

                                    <template x-if="getStatus(scores['{{ $student->id }}']) === 'PENDING'">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                                            Not entered
                                        </span>
                                    </template>
                                </td>

                                <td class="py-3.5 px-4 align-middle">
                                    <input type="text" 
                                           name="marks[{{ $student->id }}][remarks]"
                                           value="{{ $existingMark->remarks ?? '' }}" 
                                           placeholder="Enter feedback or performance remarks..."
                                           class="remarks-input w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500 italic">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
                                    No Student Records Found For This Subject
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Controls --}}
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200/80 dark:border-slate-700/60 flex flex-col sm:flex-row items-center justify-between gap-4 no-print">
                <button type="button" onclick="window.print()" 
                        class="px-4 py-2.5 text-xs font-bold rounded-xl text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-colors shadow-xs">
                    <i class="fa-solid fa-print mr-2 text-slate-500"></i> Print Marksheet Layout
                </button>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('teacher.subjects') }}" 
                       class="px-4 py-2.5 text-xs font-bold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors">
                        Cancel
                    </a>

                    <button type="submit" 
                            class="px-8 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-xs flex items-center space-x-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save All Marks</span>
                    </button>
                </div>
            </div>

        </div>
    </form>

    {{-- PRINT SIGNATURES BLOCK (ONLY VISIBLE ON PRINTED PAPER) --}}
    <div class="print-only print-signatures">
        <div class="sig-box">Teacher's Signature</div>
        <div class="sig-box">Verified By (HOD)</div>
        <div class="sig-box">Principal's Approval</div>
    </div>

</div>
@endsection
