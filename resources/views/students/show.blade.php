{{-- resources/views/students/show.blade.php --}}
@extends('layouts.admin.app')

@section('title', $student->surname . ', ' . $student->name . ' | Profile')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Student Profile</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Detailed Record & Academic History</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ Route::has('students.index') ? route('students.index') : url('/students/manage') }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Directory
            </a>
            <a href="{{ Route::has('students.edit') ? route('students.edit', $student->id) : url('/students/' . $student->id . '/edit') }}" 
               class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow">
                <i class="fa-solid fa-pen-to-square mr-2"></i> Edit Profile
            </a>
        </div>
    </div>
@endsection

@section('content')
    @php
        $avatarBg = ($student->gender == 'Male') ? '3b82f6' : 'ec4899';
        $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($student->name . ' ' . $student->surname) . "&background={$avatarBg}&color=fff&size=256";
    @endphp

    <div class="space-y-6">

        {{-- Hero Header Card --}}
        <div class="bg-slate-900 rounded-2xl shadow-lg border border-slate-800 p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10 text-slate-100 pointer-events-none">
                <i class="fa-solid fa-graduation-cap text-[180px]"></i>
            </div>

            <div class="flex flex-col md:flex-row items-center md:items-start justify-between gap-6 relative z-10">
                {{-- Avatar & Core Info --}}
                <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6 text-center sm:text-left">
                    <div class="relative">
                        <img src="{{ $student->photo_path ? asset('storage/' . $student->photo_path) : $avatarUrl }}" 
                             x-on:error="$el.src = '{{ asset('img/default-avatar.png') }}'" 
                             alt="{{ $student->name }}" 
                             class="w-24 h-24 rounded-2xl object-cover border-2 border-indigo-500/40 shadow-lg bg-slate-800">
                        <span class="absolute -bottom-2 -right-2 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-indigo-500 text-white shadow-sm">
                            {{ $student->gender }}
                        </span>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <h2 class="text-2xl font-extrabold text-white tracking-tight">{{ $student->surname }}, {{ $student->name }}</h2>
                            @if($student->status == 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                    <i class="fa-solid fa-circle text-[6px] mr-1.5"></i> ACTIVE
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                    <i class="fa-solid fa-circle text-[6px] mr-1.5"></i> INACTIVE
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 text-xs text-slate-400 font-medium">
                            <span class="inline-flex items-center">
                                <i class="fa-solid fa-id-badge text-indigo-400 mr-1.5"></i> {{ $student->student_number }}
                            </span>
                            <span class="inline-flex items-center">
                                <i class="fa-solid fa-layer-group text-indigo-400 mr-1.5"></i> {{ $student->grade }}
                            </span>
                            @if($student->term)
                                <span class="inline-flex items-center">
                                    <i class="fa-solid fa-calendar-days text-indigo-400 mr-1.5"></i> {{ $student->term->term_name }} ({{ $student->term->academic_year }})
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Links --}}
                <div class="flex items-center space-x-2">
                    @if(Route::has('students.financials'))
                        <a href="{{ route('students.financials', $student->id) }}" 
                           class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 border border-slate-700 transition-colors">
                            <i class="fa-solid fa-receipt text-emerald-400 mr-2"></i> Financials
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Details Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Column 1 & 2: Personal & Academic Overview --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Personal Information --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center mb-4 border-b border-slate-100 dark:border-slate-700/80 pb-3">
                        <i class="fa-solid fa-user text-indigo-500 mr-2.5"></i> Personal Information
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">National ID</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-1 block font-mono">
                                {{ $student->national_id ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Date of Birth</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-1 block">
                                {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M, Y') : 'N/A' }}
                                @if($student->date_of_birth)
                                    <span class="text-slate-400 font-normal">({{ \Carbon\Carbon::parse($student->date_of_birth)->age }} yrs)</span>
                                @endif
                            </span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Phone Number</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-1 block">
                                {{ $student->phone ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Email Address</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-1 block truncate">
                                {{ $student->email ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Guardian & Emergency Contact --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center mb-4 border-b border-slate-100 dark:border-slate-700/80 pb-3">
                        <i class="fa-solid fa-users text-indigo-500 mr-2.5"></i> Guardian & Emergency Contacts
                    </h3>

                    <div class="space-y-4">
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Parent / Guardian Contact</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-1 block">
                                {{ $student->parent_contact ?? $student->emergency_contact ?? 'No contact information available' }}
                            </span>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Residential Address</span>
                            <p class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1 leading-relaxed">
                                {{ $student->address ?? 'No physical address recorded.' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Column 3: Academic Metadata & Quick Stats --}}
            <div class="space-y-6">

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center mb-4 border-b border-slate-100 dark:border-slate-700/80 pb-3">
                        <i class="fa-solid fa-graduation-cap text-indigo-500 mr-2.5"></i> Academic Details
                    </h3>

                    <ul class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                        <li class="py-3 flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Class / Level</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 px-2.5 py-1 rounded-lg border border-indigo-200 dark:border-indigo-800/50">
                                {{ $student->grade }}
                            </span>
                        </li>

                        <li class="py-3 flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Assigned Class Room</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">
                                {{ $student->schoolClass->class_name ?? $student->grade }}
                            </span>
                        </li>

                        <li class="py-3 flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Current Term</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">
                                {{ $student->term ? $student->term->term_name . ' (' . $student->term->academic_year . ')' : 'N/A' }}
                            </span>
                        </li>

                        <li class="py-3 flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Enrollment Date</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">
                                {{ $student->enrollment_date ? \Carbon\Carbon::parse($student->enrollment_date)->format('d M, Y') : 'N/A' }}
                            </span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>

    </div>
@endsection