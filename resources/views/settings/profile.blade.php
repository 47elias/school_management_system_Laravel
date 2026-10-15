{{-- resources/views/admin/edit_profile.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Edit Profile | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Admin Settings</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Update personal administrative profile details</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">

        {{-- Success Notification --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        {{-- Validation Errors Notification --}}
        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Validation Errors</h4>
                    </div>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
                <ul class="list-disc list-inside text-xs font-medium space-y-1 pl-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Profile Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-user-pen text-sky-500 mr-2.5"></i> My Profile Details
                </h3>
            </div>

            <form method="POST" action="{{ Route::has('admin.update_profile') ? route('admin.update_profile') : url('/admin/profile') }}" class="p-6 space-y-5">
                @csrf

                {{-- Full Name --}}
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                        Full Name <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative rounded-xl">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <input type="text" name="name" id="name" required value="{{ old('name', $admin->name ?? auth()->user()->name ?? '') }}" placeholder="Enter full name"
                               class="w-full pl-10 pr-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                {{-- Email Address --}}
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                        Email Address <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative rounded-xl">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" name="email" id="email" required value="{{ old('email', $admin->email ?? auth()->user()->email ?? '') }}" placeholder="Enter email address"
                               class="w-full pl-10 pr-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-3">
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center px-5 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold transition-all shadow-sm">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Save Profile Changes
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection