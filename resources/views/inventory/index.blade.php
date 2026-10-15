{{-- resources/views/inventory/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'School Inventory | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">School Inventory</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage item catalog, stock levels, and supply adjustments</p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap items-center gap-3">
            <a href="{{ Route::has('inventory.logs') ? route('inventory.logs') : url('/inventory/logs') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-clock-rotate-left mr-2"></i> Stock Logs
            </a>
            <a href="{{ Route::has('inventory.alerts') ? route('inventory.alerts') : url('/inventory/alerts') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 text-xs font-bold transition-all border border-rose-200 dark:border-rose-800/50">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i> Low Stock Alerts
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ activeModal: null }" class="space-y-6">

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

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Please fix the following validation errors:</h4>
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- 1. ADD NEW ITEM FORM --}}
            <div class="lg:col-span-4">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-box-archive text-indigo-500 mr-2.5"></i> Add New Item Type
                        </h3>
                    </div>

                    <form action="{{ Route::has('inventory.store') ? route('inventory.store') : url('/inventory') }}" method="POST" class="p-6 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Item Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="item_name" value="{{ old('item_name') }}" placeholder="e.g. Chalk, Uniform, Textbooks" required
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                SKU / Item Code <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="sku" value="{{ old('sku') }}" placeholder="e.g. STAT-001" required
                                   class="w-full px-3.5 py-2.5 text-xs font-mono rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Category <span class="text-rose-500">*</span>
                            </label>
                            <select name="category" class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach(['Stationery', 'Uniforms', 'Furniture', 'Electronics', 'Other'] as $cat)
                                    <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Alert Level
                                </label>
                                <input type="number" name="alert_level" value="{{ old('alert_level', 5) }}" min="0" required
                                       class="w-full px-3.5 py-2.5 text-xs font-mono font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       title="Notify when stock falls below this threshold">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Unit Price ($)
                                </label>
                                <input type="number" step="0.01" name="unit_price" value="{{ old('unit_price', '0.00') }}" min="0"
                                       class="w-full px-3.5 py-2.5 text-xs font-mono font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center justify-center">
                                <i class="fa-solid fa-plus-circle mr-2"></i> Register Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 2. CURRENT STOCK DIRECTORY --}}
            <div class="lg:col-span-8">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                    <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-boxes-stacked text-indigo-500 mr-2.5"></i> Current Stock Status
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700/60 text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/80">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">SKU</th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Item Name</th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Category</th>
                                    <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Qty</th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($items ?? [] as $item)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono text-xs px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200 dark:border-slate-600">
                                            {{ $item->sku }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-800 dark:text-slate-100">
                                        {{ $item->item_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300 font-medium">
                                        {{ $item->category }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-extrabold text-slate-800 dark:text-slate-100 text-sm">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->quantity <= $item->alert_level)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50">
                                                Low Stock
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                                Available
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button type="button" @click="activeModal = 'adjust-{{ $item->id }}'" 
                                                class="inline-flex items-center px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-400 text-xs font-bold transition-colors border border-amber-200 dark:border-amber-800/50">
                                            <i class="fa-solid fa-rotate mr-1.5"></i> Adjust
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-boxes-stacked text-2xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                                        No inventory items found. Add your first item using the form.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- 3. ADJUST STOCK MODALS --}}
        @foreach($items ?? [] as $item)
            <div x-show="activeModal === 'adjust-{{ $item->id }}'" 
                 class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                    <div @click="activeModal = null" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                    <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-800 rounded-3xl shadow-2xl z-10 border border-slate-200 dark:border-slate-700">
                        <form action="{{ Route::has('inventory.updateStock') ? route('inventory.updateStock') : url('/inventory/adjust-stock') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $item->id }}">

                            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                                    <i class="fa-solid fa-rotate text-amber-500 mr-2"></i> Adjust Stock
                                </h3>
                                <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    <i class="fa-solid fa-xmark text-base"></i>
                                </button>
                            </div>

                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60 text-xs">
                                <span class="text-slate-400 uppercase font-bold text-[10px] block">Target Item</span>
                                <span class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $item->item_name }}</span>
                                <span class="font-mono text-slate-400 ml-2">({{ $item->sku }})</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Transaction Type <span class="text-rose-500">*</span>
                                </label>
                                <select name="type" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                                    <option value="in">Restock / Purchase (+)</option>
                                    <option value="out">Issue / Sale / Damage (-)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Quantity <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" name="quantity" min="1" required placeholder="Enter quantity"
                                       class="w-full px-3.5 py-2.5 text-xs font-mono font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Remarks / Reason
                                </label>
                                <input type="text" name="remarks" placeholder="e.g. Received from Supplier X"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>

                            <div class="pt-3 flex items-center justify-end space-x-2 border-t border-slate-100 dark:border-slate-700/60">
                                <button type="button" @click="activeModal = null" 
                                        class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm">
                                    Update Stock
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
@endsection