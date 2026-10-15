{{-- resources/views/newsletters/show.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'View Newsletter | ' . ($newsletter->title ?? 'Details'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">View Newsletter</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Review broadcast details & content</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ Route::has('newsletters.index') ? route('newsletters.index') : url('/newsletters') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> BACK TO DIRECTORY
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 p-6 space-y-6 transition-colors">
            <div class="border-b border-slate-200/80 dark:border-slate-700/60 pb-4">
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-newspaper text-purple-500 mr-3"></i> {{ $newsletter->title }}
                </h3>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Subject Line</span>
                    <span class="text-xs font-semibold text-slate-800 dark:text-slate-100">{{ $newsletter->subject }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Target Audience</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold uppercase bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800/50">
                        {{ $newsletter->target_audience }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Broadcast Date</span>
                    <span class="text-xs font-semibold text-slate-800 dark:text-slate-100 font-mono">
                        {{ $newsletter->created_at ? $newsletter->created_at->format('F d, Y \a\t h:i A') : 'N/A' }}
                    </span>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3 flex items-center">
                    <i class="fa-solid fa-envelope-open-text text-indigo-500 mr-2"></i> Message Content
                </h4>
                <div class="p-5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60 text-slate-800 dark:text-slate-200 text-xs sm:text-sm leading-relaxed whitespace-pre-wrap">
                    {!! nl2br(e($newsletter->content)) !!}
                </div>
            </div>
        </div>

    </div>
@endsection