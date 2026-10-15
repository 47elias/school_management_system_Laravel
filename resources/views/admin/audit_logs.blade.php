{{-- resources/views/audit_logs/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'System Audit Logs | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">System Audit Logs</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Track user activities, security events, and system changes</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-clock-rotate-left text-indigo-500 mr-2.5"></i> Activity Trail
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date & Time</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action Performed By</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Module / Record</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Event</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Description</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($logs ?? [] as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block">
                                    {{ $log->created_at ? $log->created_at->format('d M, Y') : 'N/A' }}
                                </strong>
                                <span class="text-[11px] font-mono text-slate-400">
                                    {{ $log->created_at ? $log->created_at->format('H:i:s') : '00:00:00' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(isset($log->causer) && $log->causer)
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50 flex items-center justify-center font-bold text-xs uppercase">
                                            {{ substr($log->causer->name ?? 'A', 0, 1) }}
                                        </div>
                                        <div>
                                            <strong class="text-xs font-bold text-slate-800 dark:text-slate-100 block">{{ $log->causer->name ?? 'User' }}</strong>
                                            <span class="text-[10px] text-slate-400">{{ class_basename($log->causer_type ?? 'User') }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center text-xs font-medium text-slate-400 italic">
                                        <i class="fa-solid fa-server mr-1.5"></i> System / Auto
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 font-bold border border-slate-200 dark:border-slate-600">
                                    {{ class_basename($log->subject_type ?? 'Record') }} #{{ $log->subject_id ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(($log->event ?? '') === 'created')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                        Created
                                    </span>
                                @elseif(($log->event ?? '') === 'updated')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/50">
                                        Updated
                                    </span>
                                @elseif(($log->event ?? '') === 'deleted')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50">
                                        Deleted
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-600">
                                        {{ $log->event ?? 'Event' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-700 dark:text-slate-300 font-medium">
                                {{ $log->description ?? 'No description provided' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-clock-rotate-left text-3xl text-slate-300 dark:text-slate-600 mb-3 block"></i>
                                <span class="font-bold text-sm block text-slate-700 dark:text-slate-300">No Activity Found</span>
                                <span class="text-xs text-slate-400">There are currently no recorded audit logs in the system.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($logs) && method_exists($logs, 'hasPages') && $logs->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700/60">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection