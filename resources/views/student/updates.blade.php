@extends('layouts.student')

@section('content')
<div class="space-y-4">
    <!-- PAGE TITLE -->
    <div class="pb-2 border-b border-slate-300 dark:border-slate-800">
        <h1 class="text-base font-black text-slate-900 dark:text-slate-100 tracking-tight">Noticeboard & Updates</h1>
        <p class="text-xs font-bold text-slate-700 dark:text-slate-400">Official urgent notices and school newsletters</p>
    </div>

    <!-- TWO COLUMNS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
        <!-- ANNOUNCEMENTS -->
        <div class="space-y-3">
            <h3 class="text-xs font-black text-slate-900 dark:text-slate-100 flex items-center space-x-2">
                <i class="fa-solid fa-bullhorn text-slate-900"></i>
                <span>Urgent Notices</span>
            </h3>

            @forelse($announcements as $announcement)
                <div class="bg-white dark:bg-slate-900 rounded-lg p-4 border-l-4 {{ $announcement->priority == 'high' ? 'border-l-rose-700' : 'border-l-slate-900' }} border-y border-r border-slate-300 dark:border-slate-800 shadow-xs space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-400">
                        <span><i class="fa-solid fa-clock mr-1"></i>{{ $announcement->created_at->diffForHumans() }}</span>
                        <span class="px-2 py-0.5 rounded font-black uppercase text-[9px] {{ $announcement->priority == 'high' ? 'bg-rose-700 text-white' : 'bg-slate-900 text-white' }}">
                            {{ $announcement->priority }}
                        </span>
                    </div>
                    <h4 class="text-xs font-black text-slate-900 dark:text-slate-100">{{ $announcement->title }}</h4>
                    <p class="text-xs font-semibold text-slate-900 dark:text-slate-200 leading-relaxed whitespace-pre-line">{!! nl2br(e($announcement->content)) !!}</p>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-900 rounded-lg p-8 border border-slate-300 dark:border-slate-800 text-center text-slate-900 dark:text-slate-300 font-bold space-y-1">
                    <i class="fa-solid fa-bell-slash text-2xl text-slate-400 block mb-1"></i>
                    <h4 class="text-xs font-black">No Urgent Notices</h4>
                    <p class="text-xs text-slate-700">You're all caught up!</p>
                </div>
            @endforelse
        </div>

        <!-- NEWSLETTERS -->
        <div class="space-y-3">
            <h3 class="text-xs font-black text-slate-900 dark:text-slate-100 flex items-center space-x-2">
                <i class="fa-solid fa-newspaper text-slate-900"></i>
                <span>Newsletters</span>
            </h3>

            @forelse($newsletters as $newsletter)
                <div class="bg-white dark:bg-slate-900 rounded-lg p-4 border-t-4 border-t-slate-900 border-x border-b border-slate-300 dark:border-slate-800 shadow-xs space-y-2">
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-400">
                        <i class="fa-solid fa-calendar mr-1"></i>Published: {{ $newsletter->created_at->format('M d, Y') }}
                    </div>
                    <h4 class="text-xs font-black text-slate-900 dark:text-slate-100">{{ $newsletter->title }}</h4>
                    
                    <div class="p-2.5 rounded bg-slate-100 dark:bg-slate-950 border border-slate-300 dark:border-slate-800">
                        <span class="text-[9px] font-black uppercase text-slate-700 dark:text-slate-400 block">Subject</span>
                        <strong class="text-xs font-black text-slate-900 dark:text-slate-100">{{ $newsletter->subject }}</strong>
                    </div>

                    <p class="text-xs font-semibold text-slate-900 dark:text-slate-200 leading-relaxed whitespace-pre-line">{!! nl2br(e($newsletter->content)) !!}</p>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-900 rounded-lg p-8 border border-slate-300 dark:border-slate-800 text-center text-slate-900 dark:text-slate-300 font-bold space-y-1">
                    <i class="fa-solid fa-envelope-open text-2xl text-slate-400 block mb-1"></i>
                    <h4 class="text-xs font-black">No Newsletters Found</h4>
                    <p class="text-xs text-slate-700">School newsletters will appear here when published.</p>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection