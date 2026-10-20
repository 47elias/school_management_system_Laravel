{{-- resources/views/inventory/logs.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Inventory Logs | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Inventory Logs</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Complete audit trail of stock movements and supply adjustments</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ Route::has('inventory.index') ? route('inventory.index') : url('/inventory') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Inventory
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        <form method="GET" action="{{ route('inventory.logs') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-stone-50 p-4 sm:grid-cols-3 dark:border-slate-700 dark:bg-slate-800/70">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search item, SKU, person, or note" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <select name="type" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">Stock in and out</option>
                <option value="in" @selected(request('type') === 'in')>Stock received</option>
                <option value="out" @selected(request('type') === 'out')>Stock issued</option>
            </select>
            <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Filter movements</button>
        </form>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-clock-rotate-left text-indigo-500 mr-2.5"></i> Recent Stock Movements
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date & Time</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Item Name</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Transaction Type</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Person / Department</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Remarks / Context</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($logs ?? [] as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-500 dark:text-slate-400">
                                {{ $log->created_at ? $log->created_at->format('d M Y, h:i A') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800 dark:text-slate-100">
                                {{ $log->item->item_name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(($log->type ?? 'in') == 'in')
                                    <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-bold">
                                        <i class="fa-solid fa-arrow-up mr-1.5 text-xs"></i> Stock In
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-bold">
                                        <i class="fa-solid fa-arrow-down mr-1.5 text-xs"></i> Stock Out
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-extrabold text-slate-800 dark:text-slate-100 text-sm">
                                {{ $log->quantity }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $log->person_involved ?: '—' }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                {{ $log->remarks ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-clock-rotate-left text-2xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                                No stock movements recorded yet.
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
