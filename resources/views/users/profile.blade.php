{{-- resources/views/teachers/profile.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'My Profile | ' . auth()->user()->name)

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">My Account Profile</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage personal contact info and security settings</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <nav class="flex text-xs text-slate-500 dark:text-slate-400">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fa-solid fa-house mr-1"></i> Home
                </a>
                <span class="mx-2">/</span>
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Profile</span>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: User Profile Card --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 transition-colors flex flex-col items-center text-center">
                
                <div class="relative mb-4">
                    <img src="{{ asset('adminlte/dist/img/avatar5.png') }}" 
                         alt="Profile Avatar" 
                         class="w-24 h-24 rounded-full border-4 border-indigo-100 dark:border-indigo-950 object-cover shadow">
                    <span class="absolute bottom-1 right-1 w-4 h-4 bg-emerald-500 border-2 border-white dark:border-slate-800 rounded-full" title="Active"></span>
                </div>

                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $user->name }}</h3>
                <span class="mt-1 inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 uppercase tracking-wider border border-indigo-200 dark:border-indigo-800/50">
                    {{ ucfirst($user->role ?? 'Staff') }}
                </span>

                <div class="w-full mt-6 pt-6 border-t border-slate-100 dark:border-slate-700/60 space-y-3 text-left">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center">
                            <i class="fa-solid fa-id-card text-indigo-500 mr-2 w-4"></i> National ID
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 font-mono">{{ $user->national_id }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center">
                            <i class="fa-solid fa-barcode text-indigo-500 mr-2 w-4"></i> EC Number
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 font-mono">{{ $user->ec_number }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400 flex items-center">
                            <i class="fa-solid fa-calendar-check text-indigo-500 mr-2 w-4"></i> Account Created
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- Right: Update Settings Form --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Success Alert --}}
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                        <span class="text-xs font-semibold">{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            @endif

            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fa-solid fa-sliders text-indigo-500 mr-2.5"></i> Update Account Settings
                    </h3>
                </div>

                <form method="POST" action="{{ route('teacher.profile.update') }}" class="p-6 space-y-6">
                    @csrf

                    {{-- Section: Personal Details --}}
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mr-3">Identity Details</span>
                            <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Full Name</label>
                                <div class="relative">
                                    <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text" value="{{ $user->name }}" readonly
                                           class="w-full pl-9 pr-3.5 py-2.5 text-xs rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 cursor-not-allowed">
                                </div>
                                <input type="hidden" name="name" value="{{ $user->name }}">
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Official legal names are locked by Administration.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Email Address</label>
                                <div class="relative">
                                    <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-indigo-500 text-xs"></i>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                           class="w-full pl-9 pr-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                @error('email') <p class="text-rose-500 text-xs font-semibold mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Phone Number</label>
                                <div class="relative">
                                    <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-indigo-500 text-xs"></i>
                                    <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" placeholder="+263..."
                                           class="w-full pl-9 pr-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                @error('phone_number') <p class="text-rose-500 text-xs font-semibold mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Section: Security --}}
                    <div class="space-y-4 pt-2">
                        <div class="flex items-center">
                            <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 mr-3">Security Credentials</span>
                            <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">New Password</label>
                                <div class="relative">
                                    <i class="fa-solid fa-key absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="password" name="password" placeholder="••••••••"
                                           class="w-full pl-9 pr-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Leave blank to keep your existing password.</p>
                                @error('password') <p class="text-rose-500 text-xs font-semibold mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Verify Password</label>
                                <div class="relative">
                                    <i class="fa-solid fa-shield-check absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="password" name="password_confirmation" placeholder="••••••••"
                                           class="w-full pl-9 pr-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-4 border-t border-slate-200/80 dark:border-slate-700/60 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-sm hover:shadow">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Save Changes
                        </button>
                    </div>

                </form>
            </div>

            {{-- Support Info Card --}}
            <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/50 flex items-start space-x-3">
                <i class="fa-solid fa-circle-info text-indigo-600 dark:text-indigo-400 text-lg mt-0.5 shrink-0"></i>
                <div class="text-xs">
                    <h4 class="font-bold text-indigo-950 dark:text-indigo-200">Need help updating protected details?</h4>
                    <p class="text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                        Data such as <strong>Full Name, National ID, and EC Number</strong> are legally verified records. To request corrections, contact the school administration or system IT support.
                    </p>
                </div>
            </div>

        </div>

    </div>
@endsection