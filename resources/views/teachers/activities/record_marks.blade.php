@extends('layouts.admin.app')

@section('title', 'Enter Activity Marks')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div><h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2"><i class="fa-solid fa-pen-to-square text-emerald-600 dark:text-emerald-400"></i><span>{{ $activity->title }}</span></h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $activity->subject->subject_name ?? 'Subject' }} <span class="mx-1">·</span> {{ $activity->subjectAssignment->class_label }} <span class="mx-1">·</span> {{ $activity->formatted_date }}</p></div>
        <a href="{{ route('teacher.activities.index') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700"><i class="fa-solid fa-arrow-left mr-2"></i> Activities</a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))<div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 p-4 text-sm font-semibold text-emerald-800 dark:text-emerald-200"><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 p-4 text-sm text-rose-800 dark:text-rose-200">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach([['Activity type', $activity->type_label, 'fa-tag', 'indigo'], ['Class', $activity->subjectAssignment->class_label, 'fa-school', 'sky'], ['Completion', '0%', 'fa-check-double', 'emerald'], ['Maximum score', $activity->max_score, 'fa-star', 'amber']] as [$label, $value, $icon, $color])
            <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 p-4 shadow-xs"><p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">{{ $label }}</p><div class="mt-2 flex items-center justify-between gap-2"><span class="text-lg font-black text-slate-800 dark:text-slate-100" @if($label === 'Completion') id="completion-perc" @endif>{{ $value }}</span><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $color }}-50 dark:bg-{{ $color }}-950/50 text-{{ $color }}-600 dark:text-{{ $color }}-400"><i class="fa-solid {{ $icon }}"></i></span></div></div>
        @endforeach
    </div>

    <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-2xl border border-indigo-200 dark:border-indigo-900 bg-indigo-50/70 dark:bg-indigo-950/30 p-5">
        <div><h2 class="font-bold text-sm text-slate-800 dark:text-slate-100">Fill marks in Excel</h2><p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Download the class list, enter scores, then upload the completed file.</p></div>
        <div class="flex flex-wrap gap-2"><a href="{{ route('teacher.activities.template', $activity->id) }}" class="rounded-xl border border-indigo-200 dark:border-indigo-800 bg-white dark:bg-slate-800 px-4 py-2.5 text-xs font-bold text-indigo-700 dark:text-indigo-300"><i class="fa-solid fa-download mr-1.5"></i>Download Excel Template</a>
            <form action="{{ route('teacher.activities.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">@csrf<input type="hidden" name="activity_id" value="{{ $activity->id }}"><input required type="file" name="file" accept=".xlsx,.csv,.txt" class="max-w-48 text-xs text-slate-600 dark:text-slate-300"><button class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-700"><i class="fa-solid fa-file-import mr-1.5"></i>Import Marks</button></form>
        </div>
    </section>

    <form action="{{ route('teacher.activities.marks.store') }}" method="POST" id="marksForm" class="overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 shadow-xs">
        @csrf<input type="hidden" name="activity_id" value="{{ $activity->id }}">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 dark:border-slate-700/60 px-5 py-4"><div><h2 class="font-bold text-sm text-slate-800 dark:text-slate-100">Student marks</h2><p class="mt-1 text-xs text-slate-500">Enter each mark out of {{ $activity->max_score }}. Press Save Marks when finished.</p></div><button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-indigo-700"><i class="fa-solid fa-floppy-disk mr-1.5"></i>Save Marks</button></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[700px] text-left"><thead class="bg-slate-50/80 dark:bg-slate-900/50 text-[10px] uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3 text-center">#</th><th class="px-5 py-3">Student</th><th class="px-5 py-3">Stream</th><th class="px-5 py-3 text-center">Score / {{ $activity->max_score }}</th><th class="px-5 py-3">Comment</th></tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
            @forelse($students as $index => $student)
                @php $markRecord = $marks->get($student->id); $score = $markRecord?->score ?? ''; $comment = $markRecord?->comment ?? ''; @endphp
                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30"><td class="px-5 py-3.5 text-center text-xs text-slate-400">{{ $index + 1 }}</td><td class="px-5 py-3.5"><p class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $student->surname }}, {{ $student->name }}</p><p class="mt-0.5 font-mono text-[10px] text-indigo-600 dark:text-indigo-400">{{ $student->student_number ?? 'STU-'.$student->id }}</p></td><td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $student->schoolClass->class_name ?? '—' }}</td><td class="px-5 py-3.5 text-center"><input type="number" name="marks[{{ $student->id }}][score]" class="score-input w-28 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2 text-center text-sm font-black text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ $score }}" min="0" max="{{ $activity->max_score }}" step="0.5"></td><td class="px-5 py-3.5"><input type="text" name="marks[{{ $student->id }}][comment]" class="comment-input w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" value="{{ $comment }}" placeholder="Optional feedback"></td></tr>
            @empty
                <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">No students found in this class.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="flex justify-end border-t border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-900/50 px-5 py-4"><button type="submit" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-xs font-bold text-white hover:bg-indigo-700"><i class="fa-solid fa-floppy-disk mr-1.5"></i>Save All Marks</button></div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const scores = [...document.querySelectorAll('.score-input')];
    const completion = document.getElementById('completion-perc');
    const updateCompletion = () => {
        const filled = scores.filter((input) => input.value !== '').length;
        if (completion) completion.textContent = `${scores.length ? Math.round((filled / scores.length) * 100) : 0}%`;
    };
    scores.forEach((input) => input.addEventListener('input', updateCompletion));
    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
            event.preventDefault();
            document.getElementById('marksForm')?.requestSubmit();
        }
    });
    updateCompletion();
});
</script>
@endpush
