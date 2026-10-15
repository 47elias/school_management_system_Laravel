{{-- resources/views/inventory/alerts.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Low Stock Alerts | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Critical Stock Alerts</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Items falling below safe operational alert thresholds</p>
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

        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 flex items-center space-x-3 shadow-sm">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-xl"></i>
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider">Action Required</h4>
                <p class="text-xs font-medium mt-0.5">Please review the inventory items below and restock critical supplies to prevent operational shortages.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center text-rose-600 dark:text-rose-400">
                    <i class="fa-solid fa-boxes-stacked mr-2.5"></i> Low Stock Catalog
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Item Name</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">SKU</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Current Qty</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Alert Level</th>
                            <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($lowStockItems ?? [] as $item)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800 dark:text-slate-100">
                                {{ $item->item_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200 dark:border-slate-600">
                                    {{ $item->sku }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-extrabold text-rose-600 dark:text-rose-400 text-sm">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-bold text-slate-500 dark:text-slate-400">
                                {{ $item->alert_level }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->quantity == 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900">
                                        Out of Stock
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50">
                                        Critically Low
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ Route::has('inventory.index') ? route('inventory.index') : url('/inventory') }}" 
                                   class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                                    <i class="fa-solid fa-plus-circle mr-1.5"></i> Restock Now
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-emerald-600 dark:text-emerald-400">
                                <i class="fa-solid fa-circle-check text-3xl mb-2 block"></i>
                                <span class="font-bold text-sm block">All stock levels are healthy!</span>
                                <span class="text-xs text-slate-400">No items are currently below their alert thresholds.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection