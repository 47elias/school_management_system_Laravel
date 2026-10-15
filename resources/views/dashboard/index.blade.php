{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Dashboard | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">System Dashboard</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ env('SCHOOL_NAME', 'School Portal') }}</p>
    </div>
@endsection

@section('content')
    <!-- Financial Overview Section -->
    <div class="mb-8">
        <h2 class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Financial Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Revenue Card -->
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-sm border border-emerald-400/20 p-6 text-white group hover:-translate-y-1 transition-transform duration-300">
                <div class="relative z-10">
                    <h3 class="text-3xl font-bold mb-1">${{ number_format($totalRevenue ?? 0, 2) }}</h3>
                    <p class="text-emerald-50 text-sm font-medium">Total Revenue (Payments)</p>
                </div>
                <div class="absolute -right-4 -bottom-4 text-emerald-900/15 dark:text-emerald-950/30 group-hover:text-emerald-900/25 transition-colors transform group-hover:scale-110 duration-300">
                    <i class="fa-solid fa-money-bill-wave text-8xl"></i>
                </div>
                <a href="{{ route('fees.index') }}" class="relative z-10 mt-6 inline-flex items-center text-sm font-semibold text-white/90 hover:text-white transition-colors">
                    View Income <i class="fa-solid fa-circle-arrow-right ml-2 text-xs"></i>
                </a>
            </div>

            <!-- Expenses Card -->
            <div class="relative overflow-hidden bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl shadow-sm border border-rose-400/20 p-6 text-white group hover:-translate-y-1 transition-transform duration-300">
                <div class="relative z-10">
                    <h3 class="text-3xl font-bold mb-1">${{ number_format($totalExpenses ?? 0, 2) }}</h3>
                    <p class="text-rose-50 text-sm font-medium">Total Expenses & Payroll</p>
                </div>
                <div class="absolute -right-4 -bottom-4 text-rose-900/15 dark:text-rose-950/30 group-hover:text-rose-900/25 transition-colors transform group-hover:scale-110 duration-300">
                    <i class="fa-solid fa-cart-shopping text-8xl"></i>
                </div>
                <a href="{{ route('expenses.index') }}" class="relative z-10 mt-6 inline-flex items-center text-sm font-semibold text-white/90 hover:text-white transition-colors">
                    View Outgoings <i class="fa-solid fa-circle-arrow-right ml-2 text-xs"></i>
                </a>
            </div>

            <!-- Net Balance Card -->
            @php $netBalance = $netBalance ?? 0; @endphp
            <div class="relative overflow-hidden bg-gradient-to-br {{ $netBalance >= 0 ? 'from-blue-500 to-indigo-600' : 'from-amber-500 to-orange-600' }} rounded-2xl shadow-sm border border-white/10 p-6 text-white group hover:-translate-y-1 transition-transform duration-300">
                <div class="relative z-10">
                    <h3 class="text-3xl font-bold mb-1">${{ number_format($netBalance, 2) }}</h3>
                    <p class="text-white/80 text-sm font-medium">Net Balance</p>
                </div>
                <div class="absolute -right-4 -bottom-4 text-black/15 dark:text-black/30 group-hover:text-black/25 transition-colors transform group-hover:scale-110 duration-300">
                    <i class="fa-solid fa-landmark text-8xl"></i>
                </div>
                <a href="{{ route('fees.report') }}" class="relative z-10 mt-6 inline-flex items-center text-sm font-semibold text-white/90 hover:text-white transition-colors">
                    Full Balance Report <i class="fa-solid fa-circle-arrow-right ml-2 text-xs"></i>
                </a>
            </div>

        </div>
    </div>

    <!-- Academic & Operations Section -->
    <div class="mb-8">
        <h2 class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Academic & Operations</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-5 flex items-center transition-colors">
                <div class="bg-blue-100 dark:bg-blue-900/40 p-3.5 rounded-xl text-blue-600 dark:text-blue-400 mr-4">
                    <i class="fa-solid fa-users text-xl w-6 h-6 flex items-center justify-center"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Registered Students</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($studentCount ?? 0) }}</h3>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-5 flex items-center transition-colors">
                <div class="bg-emerald-100 dark:bg-emerald-900/40 p-3.5 rounded-xl text-emerald-600 dark:text-emerald-400 mr-4">
                    <i class="fa-solid fa-book-bookmark text-xl w-6 h-6 flex items-center justify-center"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Active Subjects</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $subjectCount ?? 0 }}</h3>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-5 flex items-center transition-colors">
                <div class="bg-orange-100 dark:bg-orange-900/40 p-3.5 rounded-xl text-orange-600 dark:text-orange-400 mr-4">
                    <i class="fa-solid fa-triangle-exclamation text-xl w-6 h-6 flex items-center justify-center"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Low Stock Items</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">
                        {{ \App\Models\InventoryItem::whereColumn('quantity', '<=', 'alert_level')->count() ?? 0 }}
                    </h3>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-5 flex items-center transition-colors">
                <div class="bg-slate-100 dark:bg-slate-700 p-3.5 rounded-xl text-slate-600 dark:text-slate-300 mr-4">
                    <i class="fa-solid fa-user-shield text-xl w-6 h-6 flex items-center justify-center"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Staff Accounts</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ $userCount ?? 0 }}</h3>
                </div>
            </div>

        </div>
    </div>

    <!-- Charts & Lists Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Chart Container -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-chart-column text-indigo-500 dark:text-indigo-400 mr-2.5"></i> Enrollment by Grade
                </h3>
            </div>
            <div class="p-6 h-[320px] w-full">
                <canvas id="enrollmentChart"></canvas>
            </div>
        </div>

        <!-- Inventory List Container -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden flex flex-col transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-fire text-orange-500 dark:text-orange-400 mr-2.5"></i> Top Issued Supplies
                </h3>
            </div>
            <div class="flex-1 overflow-y-auto p-0">
                <ul class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($topIssuedItems ?? [] as $log)
                    <li class="px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors flex justify-between items-center">
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $log->item->item_name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Released this month</p>
                        </div>
                        <span class="bg-orange-100 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300 dark:border dark:border-orange-800/50 text-xs font-bold px-3 py-1 rounded-full">
                            {{ $log->total_issued }}
                        </span>
                    </li>
                    @empty
                    <li class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                        No recent supply logs available
                    </li>
                    @endforelse
                </ul>
            </div>
            <div class="p-4 border-t border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/80 text-center">
                <a href="{{ route('inventory.index') }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                    View Inventory Report
                </a>
            </div>
        </div>

    </div>

    <!-- Data Table Row -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden mb-8 transition-colors">
        <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                <i class="fa-solid fa-calendar-days text-sky-500 dark:text-sky-400 mr-2.5"></i> Upcoming Examinations
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60">
                <thead class="bg-slate-50 dark:bg-slate-800/80">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ref ID</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Exam Name</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Subject</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date & Day</th>
                        <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($recentExams ?? [] as $exam)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 px-2.5 py-1 rounded-md">#{{ $exam->id }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800 dark:text-slate-100">
                            {{ $exam->exam_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                            {{ $exam->subject->subject_name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                            <i class="fa-solid fa-clock text-slate-400 dark:text-slate-500 mr-2"></i> 
                            {{ \Carbon\Carbon::parse($exam->exam_date)->format('D, d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 dark:border dark:border-emerald-800/50">
                                CONFIRMED
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            No upcoming examinations scheduled.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('enrollmentChart');
            if(canvas) {
                const ctxEnroll = canvas.getContext('2d');

                const isDark = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? '#334155' : '#f1f5f9';
                const tickColor = isDark ? '#94a3b8' : '#64748b';

                // Modern Gradient for Chart
                const chartGrad = ctxEnroll.createLinearGradient(0, 0, 0, 400);
                chartGrad.addColorStop(0, '#6366f1'); // Indigo 500
                chartGrad.addColorStop(1, '#a855f7'); // Purple 500

                const labels = {!! isset($gradesData) ? json_encode($gradesData->pluck('grade')) : "['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4']" !!};
                const data = {!! isset($gradesData) ? json_encode($gradesData->pluck('total')) : "[120, 150, 180, 110]" !!};

                new Chart(ctxEnroll, {
                    type: 'bar',
                    data: {
                        labels: labels.length ? labels : ['No Data'],
                        datasets: [{
                            label: 'Students',
                            data: data.length ? data : [0],
                            backgroundColor: chartGrad,
                            hoverBackgroundColor: '#4f46e5',
                            borderRadius: 6,
                            borderSkipped: false,
                            barPercentage: 0.5,
                            categoryPercentage: 0.8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleColor: '#f8fafc',
                                bodyColor: '#f8fafc',
                                borderColor: '#334155',
                                borderWidth: 1,
                                padding: 12,
                                titleFont: { size: 14, family: "'Inter', sans-serif" },
                                bodyFont: { size: 13, family: "'Inter', sans-serif" },
                                cornerRadius: 8,
                                displayColors: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { 
                                    color: gridColor,
                                    drawTicks: false
                                },
                                ticks: { 
                                    color: tickColor, 
                                    font: { size: 12, family: "'Inter', sans-serif" },
                                    padding: 10
                                }
                            },
                            x: {
                                border: { display: false },
                                grid: { display: false },
                                ticks: { 
                                    color: tickColor, 
                                    font: { size: 12, weight: '600', family: "'Inter', sans-serif" },
                                    padding: 10
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush