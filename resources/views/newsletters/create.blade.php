{{-- resources/views/newsletters/create.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Compose Newsletter | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Compose Newsletter</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Draft and broadcast new institutional editions</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ Route::has('newsletters.index') ? route('newsletters.index') : url('/newsletters') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Directory
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

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

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                    <p class="text-xs font-medium">{{ session('error') }}</p>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-paper-plane text-purple-500 mr-2.5"></i> Newsletter Content Details
                </h3>
            </div>

            <form action="{{ Route::has('newsletters.store') ? route('newsletters.store') : url('/newsletters') }}" method="POST" class="p-6 space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                            Internal Title <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" placeholder="e.g. Term 2 Weekly Update #1" required
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                            Target Audience <span class="text-rose-500">*</span>
                        </label>
                        <select name="target_audience" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="all">All Users (Parents, Students, Staff)</option>
                            <option value="parents">Parents Only</option>
                            <option value="teachers">Staff & Teachers Only</option>
                            <option value="students">Students Only</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                        Email Subject Line <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="subject" placeholder="e.g. Important Updates Regarding Upcoming Examinations" required
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                        Newsletter Body Content <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="content" rows="10" placeholder="Write your full newsletter message here..." required
                              class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500 leading-relaxed"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 flex items-center space-x-3">
                    <button type="submit" 
                            class="inline-flex items-center px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition-all shadow-sm">
                        <i class="fa-solid fa-paper-plane mr-2"></i> BROADCAST NEWSLETTER
                    </button>
                    <a href="{{ Route::has('newsletters.index') ? route('newsletters.index') : url('/newsletters') }}" 
                       class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

    </div>
@endsection