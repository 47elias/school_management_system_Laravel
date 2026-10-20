{{-- resources/views/activities/analytics.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'CA Analytics | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">CA Analytics</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Continuous Assessment results {{ $selectedClass ? 'for ' . $selectedClass->class_name : ($selectedGrade ? 'for ' . $selectedGrade->name . ' across all streams' : 'across the school') }}.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ Route::has('activities.index') ? route('activities.index') : url('/activities') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-list-check mr-2"></i> View Activities List
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Term Filter Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-4 transition-colors">
            <form method="GET" class="flex flex-wrap items-center gap-4">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center">
                    <i class="fa-solid fa-filter mr-2 text-indigo-500"></i> Target Term:
                </label>
                <select name="term_id" onchange="this.form.submit()" 
                        class="px-3.5 py-2 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($terms as $t)
                        <option value="{{ $t->id }}" {{ $selectedTerm && $selectedTerm->id == $t->id ? 'selected' : '' }}>
                            {{ $t->term_name }} ({{ $t->academic_year }})
                        </option>
                    @endforeach
                </select>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Grade / Form</label>
                <select name="grade_level_id" onchange="this.form.submit()" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                    <option value="">All levels</option>@foreach($gradeLevels as $grade)<option value="{{ $grade->id }}" @selected(request('grade_level_id') == $grade->id)>{{ $grade->name }}</option>@endforeach
                </select>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Stream</label>
                <select name="class_id" onchange="this.form.submit()" class="px-3.5 py-2 text-xs font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                    <option value="">All streams</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->class_name }}</option>@endforeach
                </select>
            </form>
        </div>

        {{-- KPI Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- School Average --}}
            <div class="bg-gradient-to-br from-sky-500 to-indigo-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">{{ request('class_id') ? 'Stream' : (request('grade_level_id') ? 'Form' : 'School') }} CA Average</span>
                    <h3 class="text-3xl font-black mt-2 tracking-tight">{{ $overall['avg_pct'] }}%</h3>
                </div>
                <i class="fa-solid fa-chart-line absolute right-4 bottom-2 text-6xl opacity-15"></i>
            </div>

            {{-- Activities Logged --}}
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Activities Logged</span>
                    <h3 class="text-3xl font-black mt-2 tracking-tight">{{ $overall['activity_count'] }}</h3>
                </div>
                <i class="fa-solid fa-list-check absolute right-4 bottom-2 text-6xl opacity-15"></i>
            </div>

            {{-- Scores Recorded --}}
            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">Scores Recorded</span>
                    <h3 class="text-3xl font-black mt-2 tracking-tight">{{ $overall['marks_count'] }}</h3>
                </div>
                <i class="fa-solid fa-pen-to-square absolute right-4 bottom-2 text-6xl opacity-15"></i>
            </div>

            {{-- At-Risk Scores --}}
            <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-80 block">At-Risk Scores (&lt; 40%)</span>
                    <h3 class="text-3xl font-black mt-2 tracking-tight">{{ $overall['at_risk_count'] }}</h3>
                </div>
                <i class="fa-solid fa-triangle-exclamation absolute right-4 bottom-2 text-6xl opacity-15"></i>
            </div>

        </div>

        {{-- Best / Worst Class Highlights --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-6 text-white shadow-sm">
                <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-emerald-100">
                    <i class="fa-solid fa-trophy text-amber-300"></i>
                    <span>Best Performing Class</span>
                </div>
                @if($bestClass)
                    <h2 class="text-2xl font-black mt-2">{{ $bestClass['class_name'] }} — {{ $bestClass['avg_pct'] }}%</h2>
                    <p class="text-xs text-emerald-100/80 mt-1 font-medium">{{ $bestClass['marks_count'] }} scores recorded this term</p>
                @else
                    <h2 class="text-xl font-bold mt-2">No data yet</h2>
                @endif
            </div>

            <div class="bg-gradient-to-br from-rose-600 to-pink-700 rounded-2xl p-6 text-white shadow-sm">
                <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-rose-100">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                    <span>Lowest Performing Class</span>
                </div>
                @if($worstClass)
                    <h2 class="text-2xl font-black mt-2">{{ $worstClass['class_name'] }} — {{ $worstClass['avg_pct'] }}%</h2>
                    <p class="text-xs text-rose-100/80 mt-1 font-medium">{{ $worstClass['marks_count'] }} scores recorded this term</p>
                @else
                    <h2 class="text-xl font-bold mt-2">Not enough classes to compare</h2>
                @endif
            </div>
        </div>

        {{-- AI Analysis Panel --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-wand-magic-sparkles text-purple-500 mr-2.5"></i> AI Analysis & Recommendations
                </h3>
                <button id="generateInsightsBtn" 
                        class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-sm transition-all">
                    <i class="fa-solid fa-rotate mr-1.5"></i> Generate AI Insights
                </button>
            </div>
            <div class="p-6">
                <div id="aiInsightsBox" class="p-5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/60 text-xs text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line min-h-[80px]">
                    <span class="text-slate-400 flex items-center">
                        <i class="fa-solid fa-circle-info mr-2 text-indigo-500"></i> Click "Generate AI Insights" to get an automated analysis of performance trends, risk areas, and strategic recommendations.
                    </span>
                </div>
            </div>
        </div>

        {{-- Charts Row 1: Class Ranking & Subject Breakdown --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-4 flex items-center">
                    <i class="fa-solid fa-chart-simple text-indigo-500 mr-2"></i> CA Average by Class
                </h3>
                <div class="relative h-64 w-full">
                    <canvas id="classChart"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-4 flex items-center">
                    <i class="fa-solid fa-chart-column text-amber-500 mr-2"></i> CA Average by Subject
                </h3>
                <div class="relative h-64 w-full">
                    <canvas id="subjectChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Charts Row 2: Trend & Distribution --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-4 flex items-center">
                    <i class="fa-solid fa-chart-line text-emerald-500 mr-2"></i> Weekly CA Average Trend
                </h3>
                <div class="relative h-64 w-full">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <div class="lg:col-span-4 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-4 flex items-center">
                    <i class="fa-solid fa-chart-pie text-purple-500 mr-2"></i> Grade Distribution
                </h3>
                <div class="relative h-64 w-full">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Row 3: Activity Types & Top/Bottom Students --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-4 flex items-center">
                    <i class="fa-solid fa-tags text-sky-500 mr-2"></i> Average by Activity Type
                </h3>
                <div class="relative h-60 w-full">
                    <canvas id="typeChart"></canvas>
                </div>
            </div>

            <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 flex items-center">
                        <i class="fa-solid fa-star text-amber-500 mr-2"></i> Top & Bottom Performers (min. 3 scores)
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 dark:divide-slate-700/60 text-xs">
                    {{-- Top 5 --}}
                    <div class="p-4">
                        <h4 class="font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-3 flex items-center text-[11px]">
                            <i class="fa-solid fa-arrow-up mr-1.5"></i> Top 5 Performers
                        </h4>
                        <table class="w-full divide-y divide-slate-100 dark:divide-slate-700/60">
                            <tbody>
                                @forelse($topStudents as $s)
                                    <tr>
                                        <td class="py-2">
                                            <strong class="text-slate-800 dark:text-slate-200 block">{{ $s['student_name'] }}</strong>
                                            <span class="text-[10px] text-slate-400">{{ $s['class_name'] }}</span>
                                        </td>
                                        <td class="py-2 text-right font-mono font-extrabold text-emerald-600 dark:text-emerald-400">
                                            {{ $s['avg_pct'] }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="py-4 text-center text-slate-400">No data available</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Bottom 5 --}}
                    <div class="p-4">
                        <h4 class="font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-3 flex items-center text-[11px]">
                            <i class="fa-solid fa-arrow-down mr-1.5"></i> Bottom 5 Performers
                        </h4>
                        <table class="w-full divide-y divide-slate-100 dark:divide-slate-700/60">
                            <tbody>
                                @forelse($bottomStudents as $s)
                                    <tr>
                                        <td class="py-2">
                                            <strong class="text-slate-800 dark:text-slate-200 block">{{ $s['student_name'] }}</strong>
                                            <span class="text-[10px] text-slate-400">{{ $s['class_name'] }}</span>
                                        </td>
                                        <td class="py-2 text-right font-mono font-extrabold text-rose-600 dark:text-rose-400">
                                            {{ $s['avg_pct'] }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="py-4 text-center text-slate-400">No data available</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const classLabels = {!! json_encode($byClass->pluck('class_name')) !!};
            const classData = {!! json_encode($byClass->pluck('avg_pct')) !!};
            const subjectLabels = {!! json_encode($bySubject->pluck('subject_name')) !!};
            const subjectData = {!! json_encode($bySubject->pluck('avg_pct')) !!};
            const trendLabels = {!! json_encode($trend->pluck('week_start')) !!};
            const trendData = {!! json_encode($trend->pluck('avg_pct')) !!};
            const typeLabels = {!! json_encode($byType->pluck('type')) !!};
            const typeData = {!! json_encode($byType->pluck('avg_pct')) !!};
            const distLabels = {!! json_encode(array_keys($distribution)) !!};
            const distData = {!! json_encode(array_values($distribution)) !!};

            const tooltipDefaults = {
                backgroundColor: '#1e293b', padding: 12,
                titleFont: { size: 12, weight: 'bold' }, bodyFont: { size: 12 }, cornerRadius: 8
            };

            function barColor(values) {
                if (!values.length) return ['#3b82f6'];
                const max = Math.max(...values), min = Math.min(...values);
                return values.map(v => {
                    if (v === max && max !== min) return '#10b981';
                    if (v === min && max !== min) return '#f43f5e';
                    return '#3b82f6';
                });
            }

            // Class ranking chart
            new Chart(document.getElementById('classChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: classLabels.length ? classLabels : ['No Data'],
                    datasets: [{
                        label: 'CA Average %',
                        data: classData.length ? classData : [0],
                        backgroundColor: barColor(classData),
                        borderRadius: 6, barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, max: 100 } },
                    plugins: { legend: { display: false }, tooltip: tooltipDefaults }
                }
            });

            // Subject breakdown chart
            new Chart(document.getElementById('subjectChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: subjectLabels.length ? subjectLabels : ['No Data'],
                    datasets: [{
                        label: 'CA Average %',
                        data: subjectData.length ? subjectData : [0],
                        backgroundColor: barColor(subjectData),
                        borderRadius: 6, barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, max: 100 } },
                    plugins: { legend: { display: false }, tooltip: tooltipDefaults }
                }
            });

            // Weekly trend line chart
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            const trendGrad = trendCtx.createLinearGradient(0, 0, 0, 300);
            trendGrad.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
            trendGrad.addColorStop(1, 'rgba(16, 185, 129, 0)');
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendLabels.length ? trendLabels : ['No Data'],
                    datasets: [{
                        label: 'CA Average %',
                        data: trendData.length ? trendData : [0],
                        borderColor: '#10b981', backgroundColor: trendGrad,
                        fill: true, tension: 0.35, pointRadius: 4, pointBackgroundColor: '#10b981'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, max: 100 } },
                    plugins: { legend: { display: false }, tooltip: tooltipDefaults }
                }
            });

            // Grade distribution doughnut
            new Chart(document.getElementById('distributionChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: distLabels,
                    datasets: [{
                        data: distData,
                        backgroundColor: ['#10b981', '#06b6d4', '#3b82f6', '#f59e0b', '#f97316', '#ef4444']
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }, tooltip: tooltipDefaults }
                }
            });

            // Activity type horizontal bar
            new Chart(document.getElementById('typeChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: typeLabels.length ? typeLabels : ['No Data'],
                    datasets: [{
                        label: 'CA Average %',
                        data: typeData.length ? typeData : [0],
                        backgroundColor: '#8b5cf6',
                        borderRadius: 6, barPercentage: 0.6
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    scales: { x: { beginAtZero: true, max: 100 } },
                    plugins: { legend: { display: false }, tooltip: tooltipDefaults }
                }
            });

            // AI Insights Handler
            const btn = document.getElementById('generateInsightsBtn');
            const box = document.getElementById('aiInsightsBox');
            const termId = {{ $selectedTerm->id ?? 'null' }};
            const gradeLevelId = {{ request('grade_level_id') ? (int) request('grade_level_id') : 'null' }};
            const classId = {{ request('class_id') ? (int) request('class_id') : 'null' }};

            btn.addEventListener('click', function () {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Analyzing...';
                box.innerHTML = '<div class="text-center py-6 text-slate-400"><i class="fa-solid fa-circle-notch fa-spin text-2xl mb-2"></i><br>Processing analytics metrics...</div>';

                fetch("{{ Route::has('activities.analytics.ai_insights') ? route('activities.analytics.ai_insights') : url('/activities/analytics/ai-insights') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ term_id: termId, grade_level_id: gradeLevelId, class_id: classId })
                })
                .then(r => r.json())
                .then(data => {
                    box.innerText = data.insights;
                })
                .catch(() => {
                    box.innerHTML = '<span class="text-rose-500 font-bold"><i class="fa-solid fa-circle-exclamation mr-1"></i> Failed to generate AI analysis. Please try again.</span>';
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-rotate mr-1.5"></i> Regenerate';
                });
            });
        });
    </script>
@endpush
