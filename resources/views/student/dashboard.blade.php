@extends('layouts.student')

@section('content')
@php
    $activeTerm = $currentTerm ?? null;
    $activeTermBalance = ($totalOwed ?? 0) - ($totalPaid ?? 0);
    $totalDue = $calculatedBalance ?? 0;
    $carriedForward = $carriedForward ?? 0;
    $paymentPercentage = $paymentPercentage ?? 0;
@endphp

<div class="space-y-4">
    <!-- PAGE TITLE -->
    <div class="flex items-center justify-between pb-2 border-b border-slate-300 dark:border-slate-800">
        <div>
            <h1 class="text-base font-black text-slate-900 dark:text-slate-100 tracking-tight">Academic Dashboard</h1>
            <p class="text-xs font-bold text-slate-700 dark:text-slate-400">Welcome to your academic and financial portal</p>
        </div>
        <span class="px-3 py-1 text-xs font-black rounded-md bg-slate-900 text-white dark:bg-slate-800 border border-slate-900">
            {{ env('SCHOOL_ACRONYM', 'SMS') }} v{{ env('PORTAL_VERSION', '2.6') }}
        </span>
    </div>

    <!-- STATS ROW -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Grade -->
        <div class="bg-white dark:bg-slate-900 rounded-lg p-4 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-slate-900 text-white flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400">Grade Level</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100 truncate">{{ $student->grade }}</p>
                <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300 truncate">{{ env('SCHOOL_NAME') }}</p>
            </div>
        </div>

        <!-- Balance -->
        <div class="bg-white dark:bg-slate-900 rounded-lg p-4 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg {{ $totalDue > 0 ? 'bg-rose-700 text-white' : 'bg-emerald-700 text-white' }} flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400">Total Due Balance</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($totalDue, 2) }}</p>
                <p class="text-[11px] font-black {{ $totalDue > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }}">{{ number_format($paymentPercentage, 0) }}% Cleared</p>
            </div>
        </div>

        <!-- Active Term -->
        <div class="bg-white dark:bg-slate-900 rounded-lg p-4 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-amber-600 text-white flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400">Active Term</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100 truncate">{{ $activeTerm->term_name ?? 'N/A' }}</p>
                <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Year {{ $activeTerm->academic_year ?? date('Y') }}</p>
            </div>
        </div>

        <!-- Academic Status -->
        <div class="bg-white dark:bg-slate-900 rounded-lg p-4 border border-slate-300 dark:border-slate-800 shadow-xs flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-700 text-white flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400">Academic Status</p>
                <a href="{{ route('student.results') }}" class="text-xs font-black text-slate-900 dark:text-emerald-400 hover:underline flex items-center space-x-1 mt-0.5">
                    <span>View Exam Results</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <!-- LEFT COLUMN: PROFILE CARD -->
        <div class="space-y-3.5">
            <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-300 dark:border-slate-800 shadow-xs p-4 text-center">
                <div class="relative inline-block mx-auto mb-2">
                    <img src="{{ asset($avatar ?? '') }}" 
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=0f172a&color=fff'"
                         class="w-16 h-16 rounded-xl object-cover ring-2 ring-slate-900 bg-slate-200 dark:bg-slate-800 mx-auto"
                         alt="Profile">
                    <span class="px-2 py-0.5 text-[9px] font-black rounded bg-emerald-700 text-white absolute -bottom-1 left-1/2 -translate-x-1/2">ACTIVE</span>
                </div>

                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 mt-1">{{ $student->name }} {{ $student->surname }}</h3>
                <p class="text-xs font-bold text-slate-700 dark:text-slate-400 mt-0.5"><i class="fa-solid fa-id-card text-slate-900 mr-1"></i>{{ $student->student_number }}</p>

                <div class="mt-3 py-2 border-y border-slate-300 dark:border-slate-800 space-y-2 text-left text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-700 font-bold">Email:</span>
                        <span class="font-black text-slate-900 dark:text-slate-100 truncate max-w-[140px]">{{ $student->email ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-700 font-bold">Gender:</span>
                        <span class="font-black text-slate-900 dark:text-slate-100">{{ $student->gender }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-700 font-bold">Contact:</span>
                        <span class="font-black text-slate-900 dark:text-slate-100">{{ $student->phone ?? 'N/A' }}</span>
                    </div>
                </div>

                <a href="{{ route('student.fees') }}" class="mt-3 inline-flex items-center justify-center space-x-1.5 w-full py-2 px-3 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-black transition-all shadow-xs">
                    <i class="fa-solid fa-file-invoice text-xs"></i>
                    <span>Financial Statement</span>
                </a>
            </div>

            <!-- Campus Info -->
            <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-300 dark:border-slate-800 shadow-xs p-4 space-y-2.5">
                <h4 class="text-xs font-black text-slate-900 dark:text-slate-100 flex items-center space-x-2 border-b border-slate-300 dark:border-slate-800 pb-2">
                    <i class="fa-solid fa-school text-slate-900"></i>
                    <span>Campus Particulars</span>
                </h4>
                <div class="text-xs space-y-2 text-slate-900 dark:text-slate-200">
                    <div>
                        <span class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400 block">Address</span>
                        <p class="font-bold mt-0.5 text-slate-900 dark:text-slate-100">{{ env('SCHOOL_ADDRESS', 'N/A') }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400 block">Phone</span>
                        <p class="font-bold mt-0.5 text-slate-900 dark:text-slate-100">{{ env('SCHOOL_PHONE', 'N/A') }}</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase text-slate-700 dark:text-slate-400 block">Website</span>
                        <a href="http://{{ env('SCHOOL_WEBSITE') }}" target="_blank" class="text-slate-900 dark:text-emerald-400 hover:underline font-bold mt-0.5 block truncate">{{ env('SCHOOL_WEBSITE', 'N/A') }}</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: TAB NAVIGATION -->
        <div class="lg:col-span-2 space-y-3.5" x-data="{ activeTab: 'activity' }">
            <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
                <!-- Tab Headers -->
                <div class="flex border-b border-slate-300 dark:border-slate-800 bg-slate-100 dark:bg-slate-950 px-2">
                    <button @click="activeTab = 'activity'" 
                            :class="activeTab === 'activity' ? 'border-slate-900 text-slate-900 dark:text-white font-black bg-white dark:bg-slate-900' : 'border-transparent text-slate-700 hover:text-slate-900 font-bold'"
                            class="py-3 px-4 text-xs border-b-2 transition-all flex items-center space-x-2 focus:outline-none">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Overview</span>
                    </button>
                    <button @click="activeTab = 'personal'" 
                            :class="activeTab === 'personal' ? 'border-slate-900 text-slate-900 dark:text-white font-black bg-white dark:bg-slate-900' : 'border-transparent text-slate-700 hover:text-slate-900 font-bold'"
                            class="py-3 px-4 text-xs border-b-2 transition-all flex items-center space-x-2 focus:outline-none">
                        <i class="fa-solid fa-user-gear"></i>
                        <span>Full Particulars</span>
                    </button>
                    <button @click="activeTab = 'guardian'" 
                            :class="activeTab === 'guardian' ? 'border-slate-900 text-slate-900 dark:text-white font-black bg-white dark:bg-slate-900' : 'border-transparent text-slate-700 hover:text-slate-900 font-bold'"
                            class="py-3 px-4 text-xs border-b-2 transition-all flex items-center space-x-2 focus:outline-none">
                        <i class="fa-solid fa-users"></i>
                        <span>Guardian Contact</span>
                    </button>
                </div>

                <!-- Tab Body -->
                <div class="p-4">
                    <!-- OVERVIEW -->
                    <div x-show="activeTab === 'activity'" class="space-y-3">
                        <div class="p-4 rounded-lg border border-slate-300 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-900 dark:text-slate-100 flex items-center space-x-1.5">
                                    <i class="fa-solid fa-wallet text-slate-900"></i>
                                    <span>Financial Status Notice</span>
                                </span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-400">{{ date('d M Y') }}</span>
                            </div>
                            <p class="text-xs text-slate-900 dark:text-slate-200 leading-relaxed font-semibold">
                                Total due balance for the active term (including prior arrears) is 
                                <strong class="{{ $totalDue > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }} font-black">{{ env('CURRENCY_SYMBOL', '$') }}{{ number_format($totalDue, 2) }}</strong>.
                            </p>
                            <a href="{{ route('student.fees') }}" class="inline-block text-xs font-black text-slate-900 dark:text-emerald-400 hover:underline pt-1">
                                Open Financial Ledger &rarr;
                            </a>
                        </div>

                        <div class="p-3 rounded-lg border border-slate-300 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-xs font-bold text-slate-900 dark:text-slate-300 flex items-center space-x-2">
                            <i class="fa-solid fa-envelope text-slate-900"></i>
                            <span>Administrative Support Contact: <strong class="text-slate-900 dark:text-white font-black">{{ env('SCHOOL_EMAIL') }}</strong></span>
                        </div>
                    </div>

                    <!-- PERSONAL -->
                    <div x-show="activeTab === 'personal'" x-cloak class="space-y-2">
                        <h4 class="text-xs font-black text-slate-900 dark:text-slate-100 pb-2 border-b border-slate-300 dark:border-slate-800">Registration Records</h4>
                        <div class="divide-y divide-slate-200 dark:divide-slate-800 text-xs">
                            <div class="py-2 flex justify-between"><span class="text-slate-700 font-bold">Student Number</span><span class="font-black text-slate-900 dark:text-slate-100">{{ $student->student_number }}</span></div>
                            <div class="py-2 flex justify-between"><span class="text-slate-700 font-bold">Full Name</span><span class="font-black text-slate-900 dark:text-slate-100">{{ $student->name }} {{ $student->surname }}</span></div>
                            <div class="py-2 flex justify-between"><span class="text-slate-700 font-bold">Grade Level</span><span class="font-black text-slate-900 dark:text-slate-100">{{ $student->grade }}</span></div>
                            <div class="py-2 flex justify-between"><span class="text-slate-700 font-bold">Primary Phone</span><span class="font-black text-slate-900 dark:text-slate-100">{{ $student->phone ?? 'N/A' }}</span></div>
                            <div class="py-2 flex justify-between"><span class="text-slate-700 font-bold">Email Address</span><span class="font-black text-slate-900 dark:text-slate-100">{{ $student->email ?? 'N/A' }}</span></div>
                            <div class="py-2 flex justify-between"><span class="text-slate-700 font-bold">Residential Address</span><span class="font-black text-slate-900 dark:text-slate-100">{{ $student->address ?? 'N/A' }}</span></div>
                        </div>
                    </div>

                    <!-- GUARDIAN -->
                    <div x-show="activeTab === 'guardian'" x-cloak class="py-6 text-center space-y-2">
                        <div class="w-12 h-12 rounded-full bg-slate-900 text-white flex items-center justify-center text-xl mx-auto">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <h4 class="text-xs font-black text-slate-900 dark:text-slate-100">Guardian / Emergency Contact</h4>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-400 max-w-sm mx-auto">Official primary contact for emergency notifications and account queries:</p>
                        <p class="text-base font-black text-slate-900 dark:text-emerald-400 tracking-wider pt-1">{{ $student->parent_contact ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- WELCOME CALLOUT -->
            <div class="p-4 rounded-lg bg-slate-900 text-white text-xs space-y-1">
                <h4 class="font-black text-white text-xs">Welcome, {{ $student->name }}!</h4>
                <p class="text-xs text-slate-200 leading-relaxed font-semibold">Welcome to the official <strong>{{ env('SCHOOL_NAME') }}</strong> Student Portal. Manage your records, review fees, and view published exam results.</p>
            </div>
        </div>
    </div>
</div>
@endsection
