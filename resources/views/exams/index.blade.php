{{-- resources/views/exams/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Exams Management | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Examination Hub</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Term Context: {{ $selectedTerm->term_name }}</p>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ activeModal: null }" class="space-y-6">

        {{-- Term Filter Bar --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-4 transition-colors">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-4">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center">
                    <i class="fa-solid fa-filter mr-2 text-indigo-500"></i> Switch View to Term:
                </label>
                <select name="term_id" onchange="this.form.submit()" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none">
                    @foreach($terms as $t)
                        <option value="{{ $t->id }}" {{ ($selectedTerm->id == $t->id) ? 'selected' : '' }}>
                            {{ $t->term_name }} ({{ $t->academic_year ?? $t->academicYear?->year_name }})
                        </option>
                    @endforeach
                </select>

                @if(isset($activeTerm) && $selectedTerm->id == $activeTerm->id)
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center">
                        <i class="fa-solid fa-circle-check mr-1.5 text-xs"></i> CURRENT ACTIVE TERM
                    </span>
                @endif
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- Create Exam Form (Admin) --}}
            @if(Auth::user()->role == 'admin')
            <div class="lg:col-span-4">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-calendar-plus text-indigo-500 mr-2.5"></i> Schedule Exam
                        </h3>
                    </div>

                    <form action="{{ Route::has('exams.store') ? route('exams.store') : url('/exams') }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        <input type="hidden" name="term_id" value="{{ $selectedTerm->id }}">

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Academic Term (Locked)</label>
                            <input type="text" value="{{ $selectedTerm->term_name }} ({{ $selectedTerm->academic_year }})" disabled
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-100 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Exam / Paper Title <span class="text-rose-500">*</span></label>
                            <input type="text" name="exam_name" placeholder="e.g. Paper 1" required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Subject <span class="text-rose-500">*</span></label>
                            <select name="subject_id" required class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Choose Subject...</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Exam Date <span class="text-rose-500">*</span></label>
                                <input type="date" name="exam_date" value="{{ date('Y-m-d') }}" required
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Max Marks <span class="text-rose-500">*</span></label>
                                <input type="number" name="max_marks" value="100" required
                                       class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                                <i class="fa-solid fa-check mr-2"></i> Confirm Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- Scheduled Subjects List --}}
            <div class="{{ Auth::user()->role == 'admin' ? 'lg:col-span-8' : 'lg:col-span-12' }}">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-book text-emerald-500 mr-2.5"></i> Scheduled Subjects
                        </h3>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                            {{ $exams->groupBy('subject_id')->count() }} Subjects Active
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Subject Name</th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Papers Scheduled</th>
                                    <th scope="col" class="px-6 py-3.5 text-right font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($exams->groupBy('subject_id') as $subjectId => $subjectExams)
                                    @php $subjectName = $subjectExams->first()->subject->subject_name ?? 'Unknown Subject'; @endphp
                                    <tr @click="activeModal = 'subject-{{ $subjectId }}'" 
                                        class="hover:bg-indigo-50/40 dark:hover:bg-slate-700/30 cursor-pointer transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                                                    <i class="fa-solid fa-graduation-cap"></i>
                                                </div>
                                                <div>
                                                    <strong class="text-sm font-bold text-slate-800 dark:text-slate-100 block">{{ $subjectName }}</strong>
                                                    <span class="text-[11px] text-slate-400">Click to view papers & classes</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 rounded-full font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                                {{ $subjectExams->count() }} Paper(s)
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button type="button" class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-bold border border-slate-200 dark:border-slate-600 transition-colors">
                                                View Classes & Papers <i class="fa-solid fa-arrow-right ml-1.5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                            <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                                                <i class="fa-solid fa-folder-open text-xl"></i>
                                            </div>
                                            <h4 class="font-bold text-slate-700 dark:text-slate-300">No Exams Scheduled</h4>
                                            <p class="text-xs text-slate-400 mt-1">Use the form to schedule an exam or paper for this term.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODALS FOR EACH SUBJECT --}}
        @foreach($exams->groupBy('subject_id') as $subjectId => $subjectExams)
            @php $subjectName = $subjectExams->first()->subject->subject_name ?? 'Unknown Subject'; @endphp
            <div x-show="activeModal === 'subject-{{ $subjectId }}'" 
                 class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="flex items-center justify-center min-h-screen px-4 text-center">
                    <div @click="activeModal = null" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                    <div class="inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-800 rounded-2xl shadow-xl z-10 border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center">
                                    <i class="fa-solid fa-book text-indigo-500 mr-2"></i> {{ $subjectName }}
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Manage classes and enter marks for scheduled papers.</p>
                            </div>
                            <button @click="activeModal = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div class="py-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-bold text-slate-500">Paper Title / Date</th>
                                        <th class="px-4 py-3 text-left font-bold text-slate-500">Max Marks</th>
                                        <th class="px-4 py-3 text-left font-bold text-slate-500">Select Class to Enter Marks</th>
                                        <th class="px-4 py-3 text-center font-bold text-slate-500">Manage</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                    @foreach($subjectExams as $exam)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <strong class="text-slate-800 dark:text-slate-100 block">{{ $exam->exam_name }}</strong>
                                            <span class="text-[11px] text-slate-400">
                                                <i class="fa-solid fa-calendar mr-1"></i> {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M, Y') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-mono font-bold bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded text-slate-700 dark:text-slate-300">
                                                {{ $exam->max_marks ?? 100 }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <a href="{{ Route::has('exams.verify') ? route('exams.verify', $exam->id) : url('/exams/' . $exam->id . '/verify') }}" 
                                                   class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 border border-slate-200 dark:border-slate-600 flex items-center">
                                                    <i class="fa-solid fa-qrcode mr-1"></i> Scan
                                                </a>
                                                
                                                @foreach($grades as $grade)
                                                    <a href="{{ Route::has('exams.create') ? route('exams.create', ['exam_id' => $exam->id, 'grade' => $grade]) : url('/exams/create?exam_id=' . $exam->id . '&grade=' . $grade) }}" 
                                                       class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-800/50 flex items-center">
                                                        <i class="fa-solid fa-users mr-1"></i> {{ $grade }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if(Auth::user()->role == 'admin')
                                            <form action="{{ Route::has('exams.destroy') ? route('exams.destroy', $exam->id) : url('/exams/' . $exam->id) }}" method="POST" onsubmit="return confirm('Delete this exam paper? Associated marks will be deleted.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                            <button type="button" @click="activeModal = null" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold">
                                Close View
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
@endsection