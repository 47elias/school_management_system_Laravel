{{-- resources/views/exams/student_report.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORT_CARD_{{ preg_replace('/[^A-Za-z0-9_]/', '_', $student->name) }}_{{ $term->term_name }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        serif: ['Times New Roman', 'serif'],
                    }
                }
            }
        }
    </script>

    <style>
        @page { size: A4 portrait; margin: 0; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .report-card { box-shadow: none !important; border: none !important; padding: 10mm !important; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-black antialiased min-h-screen py-8 px-4">

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Report Card Outer Page --}}
        <div class="report-card bg-white p-8 sm:p-12 rounded-3xl shadow-lg border border-slate-200 min-h-[297mm] flex flex-col justify-between">
            <div>
                {{-- Header --}}
                <div class="text-center pb-6 border-b-2 border-slate-900 mb-6">
                    @if(env('SCHOOL_LOGO_PATH'))
                        <img src="{{ asset(env('SCHOOL_LOGO_PATH')) }}" alt="Logo" class="h-16 mx-auto mb-3 object-contain">
                    @endif
                    <h1 class="text-3xl font-black uppercase tracking-tight font-serif">{{ env('SCHOOL_NAME', 'ACADEMIC INSTITUTION') }}</h1>
                    <div class="inline-block mt-2 px-4 py-1 text-xs font-bold uppercase tracking-wider bg-slate-100 border border-slate-300 rounded-md">
                        Official Terminal Academic Performance Report
                    </div>
                    <p class="text-xs text-slate-600 mt-2">{{ env('SCHOOL_ADDRESS') }} | Tel: {{ env('SCHOOL_PHONE') }}</p>
                </div>

                {{-- Student & Term Details Grid --}}
                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200 text-xs mb-6">
                    <div>
                        <span class="text-slate-500 uppercase font-bold text-[10px] block">Student Name:</span>
                        <strong class="text-sm text-slate-900 block font-bold">{{ $student->name }} {{ $student->surname }}</strong>
                        <span class="text-slate-600 font-mono">Reg No: {{ $student->student_number }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-500 uppercase font-bold text-[10px] block">Academic Term & Stream:</span>
                        <strong class="text-sm text-slate-900 block font-bold">{{ $term->term_name }} ({{ $term->academic_year }})</strong>
                        <span class="text-slate-600 font-bold">Class: {{ $student->schoolClass->class_name ?? $student->grade }}</span>
                    </div>
                </div>

                {{-- Subject Performance Table --}}
                <table class="w-full border-2 border-slate-900 text-xs font-serif divide-y divide-slate-900 mb-6">
                    <thead class="bg-slate-900 text-white font-sans uppercase">
                        <tr>
                            <th class="p-3 text-left font-bold border-r border-slate-900">Subject</th>
                            <th class="p-3 text-left font-bold border-r border-slate-900">Evaluated Papers Breakdown</th>
                            <th class="p-3 text-center font-bold border-r border-slate-900">Average %</th>
                            <th class="p-3 text-center font-bold border-r border-slate-900">Grade</th>
                            <th class="p-3 text-center font-bold">Remark</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900 font-sans">
                        @forelse($finalGrades as $grade)
                        <tr>
                            <td class="p-3 font-bold border-r border-slate-900">
                                {{ $grade->subject_name }}
                            </td>
                            <td class="p-3 border-r border-slate-900">
                                <div class="flex flex-wrap gap-1.5 text-[11px]">
                                    @foreach($grade->papers as $p)
                                        <span class="bg-slate-100 px-2 py-0.5 rounded border border-slate-300 font-mono">
                                            <strong>{{ $p['exam_name'] }}:</strong> {{ $p['score'] }}/{{ $p['max_score'] }} ({{ $p['percentage'] }}%)
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-3 text-center font-mono font-black border-r border-slate-900 text-sm">
                                {{ $grade->average_score }}%
                            </td>
                            <td class="p-3 text-center font-mono font-bold border-r border-slate-900">
                                {{ $grade->grade }}
                            </td>
                            <td class="p-3 text-center text-[11px] italic">
                                {{ $grade->remark }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-slate-500 italic">No academic marks recorded for this term.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Overall Summary --}}
                <div class="flex items-center justify-between p-4 bg-slate-900 text-white rounded-2xl font-sans mb-8">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Overall Term Mean Score</span>
                        <span class="text-2xl font-black font-mono">{{ $overallAverage }}%</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Overall Grade</span>
                        <span class="text-2xl font-black font-mono">{{ $overallGrade['grade'] ?? 'N/A' }} ({{ $overallGrade['remark'] ?? '' }})</span>
                    </div>
                </div>
            </div>

            {{-- Signatures & Stamp --}}
            <div class="pt-6 border-t-2 border-slate-900 font-sans text-xs">
                <div class="grid grid-cols-3 gap-6 items-end">
                    <div>
                        <p class="font-bold uppercase text-[10px] text-slate-600">Class Teacher Signature:</p>
                        <div class="border-b-2 border-slate-900 w-full mt-8 mb-1"></div>
                        <p class="text-[10px] text-slate-500">Date: ____/____/20__</p>
                    </div>

                    <div class="text-center">
                        <div class="w-24 h-24 border-2 border-dashed border-slate-900 rounded-full mx-auto flex items-center justify-center text-[9px] font-bold uppercase text-center leading-tight">
                            Official<br>School<br>Stamp
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="font-bold uppercase text-[10px] text-slate-600">Headmaster / Principal:</p>
                        <div class="border-b-2 border-slate-900 w-full mt-8 mb-1"></div>
                        <p class="text-[10px] text-slate-500">Authorized Signature</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Controls --}}
        <div class="no-print text-center flex items-center justify-center space-x-4">
            <button onclick="window.print()" class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase shadow-md transition-all">
                <i class="fa-solid fa-print mr-2"></i> Print Report Card
            </button>
        </div>

    </div>

</body>
</html>