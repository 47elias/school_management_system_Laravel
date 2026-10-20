@extends('layouts.admin.app')

@section('title', 'Admin Dashboard | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex w-full flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-400">School overview</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ env('SCHOOL_NAME', 'School Portal') }} <span class="mx-1">·</span> {{ now()->format('l, d F Y') }}</p>
        </div>
        @if($currentTerm)
            <div class="inline-flex items-center gap-2 self-start rounded-full border border-indigo-200 bg-indigo-50 px-4 py-2 text-xs font-bold text-indigo-800 dark:border-indigo-800 dark:bg-indigo-950/50 dark:text-indigo-200 sm:self-auto">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                {{ $currentTerm->display_name }}
            </div>
        @else
            <a href="{{ route('terms.index') }}" class="inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-bold text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200 sm:self-auto">
                <i class="fa-solid fa-circle-exclamation"></i> Set the current term
            </a>
        @endif
    </div>
@endsection

@section('content')
    <div class="space-y-7">
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-indigo-950 to-indigo-800 p-6 text-white shadow-lg sm:p-8">
            <div class="relative z-10 max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-200">Administrator workspace</p>
                <h2 class="mt-2 text-2xl font-black sm:text-3xl">Your school, at a glance.</h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-indigo-100/80">Review enrolment, academic activity and the tasks that need attention today.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('students.index') }}" class="rounded-xl bg-white px-4 py-2.5 text-xs font-extrabold text-indigo-950 shadow-sm transition hover:bg-indigo-50"><i class="fa-solid fa-user-plus mr-2"></i>Manage students</a>
                    <a href="{{ route('exams.index') }}" class="rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-white/15"><i class="fa-solid fa-calendar-plus mr-2"></i>Exams and marks</a>
                </div>
            </div>
            <div class="pointer-events-none absolute -right-10 -top-20 h-72 w-72 rounded-full border-[36px] border-white/5"></div>
            <div class="pointer-events-none absolute -bottom-28 right-28 h-64 w-64 rounded-full bg-violet-400/10 blur-2xl"></div>
        </section>

        <section>
            <div class="mb-3 flex items-end justify-between gap-4">
                <div><h2 class="text-sm font-black text-slate-900 dark:text-white">School at a glance</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Current records across the school</p></div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @php($overviewCards = [
                    ['label' => 'Active learners', 'value' => number_format($studentCount), 'note' => number_format($newStudentsCount) . ' enrolled in the last 30 days', 'icon' => 'fa-user-graduate', 'iconClass' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300', 'url' => route('students.index')],
                    ['label' => 'Class streams', 'value' => number_format($classCount), 'note' => 'Classes currently set up', 'icon' => 'fa-chalkboard', 'iconClass' => 'bg-violet-100 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300', 'url' => route('classes.index')],
                    ['label' => 'Exam papers', 'value' => number_format($examCount), 'note' => $currentTerm ? 'Scheduled for ' . $currentTerm->display_name : 'All scheduled exam papers', 'icon' => 'fa-file-lines', 'iconClass' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300', 'url' => route('exams.index')],
                    ['label' => 'Staff accounts', 'value' => number_format($userCount), 'note' => number_format($subjectCount) . ' subjects in the catalogue', 'icon' => 'fa-users-gear', 'iconClass' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300', 'url' => route('users.index')],
                ])
                @foreach($overviewCards as $card)
                    <a href="{{ $card['url'] }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
                        <div class="flex items-start justify-between">
                            <div><p class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p><p class="mt-2 text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ $card['value'] }}</p></div>
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $card['iconClass'] }}"><i class="fa-solid {{ $card['icon'] }}"></i></span>
                        </div>
                        <p class="mt-4 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400"><span>{{ $card['note'] }}</span><i class="fa-solid fa-arrow-up-right-from-square opacity-0 transition group-hover:opacity-100"></i></p>
                    </a>
                @endforeach
            </div>
        </section>

        <section>
            <div class="mb-3"><h2 class="text-sm font-black text-slate-900 dark:text-white">Needs attention</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Live counts from school records</p></div>
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <a href="{{ route('admissions.manage') }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-amber-300 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-amber-700">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300"><i class="fa-solid fa-inbox"></i></span><span><span class="block text-xl font-black text-slate-900 dark:text-white">{{ number_format($pendingAdmissionsCount) }}</span><span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400">Admissions to review</span></span>
                </a>
                <a href="{{ route('activities.index') }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-violet-300 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-violet-700">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300"><i class="fa-solid fa-clipboard-check"></i></span><span><span class="block text-xl font-black text-slate-900 dark:text-white">{{ number_format($activitiesCount) }}</span><span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400">CA activities{{ $currentTerm ? ' this term' : '' }}</span></span>
                </a>
                <a href="{{ route('exams.index') }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-sky-300 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-sky-700">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300"><i class="fa-solid fa-pen-to-square"></i></span><span><span class="block text-xl font-black text-slate-900 dark:text-white">{{ number_format($marksEnteredCount) }}</span><span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400">Exam marks recorded</span></span>
                </a>
                <a href="{{ route('inventory.alerts') }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-rose-300 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-rose-700">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300"><i class="fa-solid fa-box-open"></i></span><span><span class="block text-xl font-black text-slate-900 dark:text-white">{{ number_format($lowStockCount) }}</span><span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400">Items at low stock</span></span>
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <a href="{{ route('fees.index') }}" class="rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 p-5 text-white shadow-sm transition hover:-translate-y-0.5">
                <div class="flex items-center justify-between"><span class="text-xs font-bold text-emerald-50">Fees collected · all records</span><i class="fa-solid fa-arrow-up-right-from-square text-emerald-100/80"></i></div><p class="mt-3 text-3xl font-black">${{ number_format($totalRevenue, 2) }}</p><p class="mt-2 text-xs text-emerald-50/80">Open the payment history</p>
            </a>
            <a href="{{ route('expenses.index') }}" class="rounded-2xl bg-gradient-to-br from-rose-600 to-red-700 p-5 text-white shadow-sm transition hover:-translate-y-0.5">
                <div class="flex items-center justify-between"><span class="text-xs font-bold text-rose-50">Recorded expenses and payroll</span><i class="fa-solid fa-arrow-up-right-from-square text-rose-100/80"></i></div><p class="mt-3 text-3xl font-black">${{ number_format($totalExpenses, 2) }}</p><p class="mt-2 text-xs text-rose-50/80">Expenses plus recorded payslips</p>
            </a>
            <a href="{{ route('fees.report') }}" class="rounded-2xl bg-gradient-to-br {{ $netBalance >= 0 ? 'from-indigo-600 to-blue-700' : 'from-orange-600 to-amber-700' }} p-5 text-white shadow-sm transition hover:-translate-y-0.5">
                <div class="flex items-center justify-between"><span class="text-xs font-bold text-white/80">Recorded net movement</span><i class="fa-solid fa-arrow-up-right-from-square text-white/80"></i></div><p class="mt-3 text-3xl font-black">${{ number_format($netBalance, 2) }}</p><p class="mt-2 text-xs text-white/80">Collected fees less expenses and payroll</p>
            </a>
        </section>

        <section class="grid grid-cols-1 gap-5 xl:grid-cols-5">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800 xl:col-span-3">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700"><div><h2 class="text-sm font-black text-slate-900 dark:text-white">Learners by grade</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Enrolment distribution from student records</p></div><i class="fa-solid fa-chart-column text-indigo-500"></i></div>
                <div class="h-72 p-5"><canvas id="enrollmentChart" aria-label="Learners by grade chart"></canvas></div>
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800 xl:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700"><div><h2 class="text-sm font-black text-slate-900 dark:text-white">Stock issued this week</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Quantity released each day</p></div><a href="{{ route('inventory.logs') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">Stock log</a></div>
                <div class="h-72 p-5"><canvas id="stockChart" aria-label="Weekly stock issued chart"></canvas></div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800 xl:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700"><div><h2 class="text-sm font-black text-slate-900 dark:text-white">Upcoming examinations</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Future papers{{ $currentTerm ? ' for ' . $currentTerm->display_name : '' }}</p></div><a href="{{ route('exams.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">Manage exams <i class="fa-solid fa-arrow-right ml-1"></i></a></div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900/40"><tr><th class="px-5 py-3 text-left text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Paper</th><th class="px-5 py-3 text-left text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Subject / class</th><th class="px-5 py-3 text-left text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Date</th><th class="px-5 py-3 text-left text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Status</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($upcomingExams as $exam)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/20"><td class="px-5 py-3.5"><p class="text-xs font-bold text-slate-900 dark:text-white">{{ $exam->exam_name }}</p><p class="mt-0.5 text-[10px] text-slate-400">Paper #{{ $exam->id }}</p></td><td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $exam->subject->subject_name ?? 'Subject not set' }}<span class="mt-0.5 block text-[10px] text-slate-400">{{ $exam->schoolClass->class_name ?? $exam->target_name }}</span></td><td class="whitespace-nowrap px-5 py-3.5 text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $exam->exam_date?->format('D, d M Y') ?? 'Date not set' }}</td><td class="px-5 py-3.5"><span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold {{ $exam->status === 'published' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300' }}">{{ ucfirst($exam->status ?? 'pending') }}</span></td></tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center"><span class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-700"><i class="fa-solid fa-calendar-check"></i></span><p class="mt-3 text-xs font-bold text-slate-700 dark:text-slate-200">No upcoming exams scheduled</p><p class="mt-1 text-[11px] text-slate-500">Scheduled papers will appear here.</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700"><div><h2 class="text-sm font-black text-slate-900 dark:text-white">Most issued supplies</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Based on recorded stock movements</p></div><i class="fa-solid fa-boxes-stacked text-orange-500"></i></div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($topIssuedItems as $log)
                        <li class="flex items-center justify-between gap-3 px-5 py-3.5"><span class="min-w-0"><span class="block truncate text-xs font-bold text-slate-800 dark:text-slate-100">{{ $log->item->item_name ?? 'Removed item' }}</span><span class="mt-0.5 block text-[10px] text-slate-400">Total quantity issued</span></span><span class="rounded-lg bg-orange-50 px-2.5 py-1 text-xs font-black text-orange-700 dark:bg-orange-950/40 dark:text-orange-300">{{ number_format($log->total_issued) }}</span></li>
                    @empty
                        <li class="px-5 py-10 text-center text-xs text-slate-500">No stock issues recorded yet.</li>
                    @endforelse
                </ul>
                <div class="border-t border-slate-100 px-5 py-3 dark:border-slate-700"><a href="{{ route('inventory.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400">Open inventory <i class="fa-solid fa-arrow-right ml-1"></i></a></div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dark = document.documentElement.classList.contains('dark');
            const tickColor = dark ? '#94a3b8' : '#64748b';
            const gridColor = dark ? '#334155' : '#e2e8f0';
            const grades = @json($gradesData->pluck('grade')->values());
            const gradeTotals = @json($gradesData->pluck('total')->values());
            const stockDays = @json($days->values());
            const stockTotals = @json($stockOutData->values());

            const chartDefaults = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { color: tickColor } }, y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, precision: 0 } } } };
            const enrollmentCanvas = document.getElementById('enrollmentChart');
            if (enrollmentCanvas) new Chart(enrollmentCanvas, {
                type: 'bar',
                data: { labels: grades.length ? grades : ['No enrolment data'], datasets: [{ data: grades.length ? gradeTotals : [0], backgroundColor: '#6366f1', hoverBackgroundColor: '#4f46e5', borderRadius: 7, maxBarThickness: 42 }] },
                options: { ...chartDefaults, plugins: { ...chartDefaults.plugins, tooltip: { callbacks: { label: context => `${context.parsed.y.toLocaleString()} learners` } } } }
            });
            const stockCanvas = document.getElementById('stockChart');
            if (stockCanvas) new Chart(stockCanvas, {
                type: 'line',
                data: { labels: stockDays, datasets: [{ data: stockTotals, borderColor: '#f97316', backgroundColor: 'rgba(249, 115, 22, .12)', fill: true, tension: .35, pointRadius: 3, pointBackgroundColor: '#f97316' }] },
                options: { ...chartDefaults, plugins: { ...chartDefaults.plugins, tooltip: { callbacks: { label: context => `${context.parsed.y.toLocaleString()} units issued` } } } }
            });
        });
    </script>
@endpush
