{{-- resources/views/inventory/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'School Inventory | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">School Inventory</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Track school supplies, record stock received or issued, and spot items that need restocking.</p>
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
    <div x-data="{ activeModal: null }" x-init="const item = new URLSearchParams(window.location.search).get('restock'); if (item) activeModal = 'adjust-' + item" class="space-y-6">

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
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 text-sm font-medium">{{ session('error') }}</div>
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

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 dark:border-indigo-900 dark:bg-indigo-950/40">
                <p class="text-xs font-bold uppercase tracking-wide text-indigo-700 dark:text-indigo-300">Item types</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($summary['item_types']) }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Different supplies in the catalogue</p>
            </div>
            <div class="rounded-2xl border border-teal-200 bg-teal-50 p-5 dark:border-teal-900 dark:bg-teal-950/40">
                <p class="text-xs font-bold uppercase tracking-wide text-teal-700 dark:text-teal-300">Units in store</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($summary['units']) }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Current quantity across all items</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/40">
                <p class="text-xs font-bold uppercase tracking-wide text-amber-800 dark:text-amber-300">Needs attention</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($summary['low_stock']) }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">At or below the restock level</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-stone-50 p-5 dark:border-slate-700 dark:bg-slate-800">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Stock value</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">${{ number_format($summary['stock_value'], 2) }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Quantity multiplied by unit price</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('inventory.index') }}" class="rounded-full border px-3 py-1.5 text-xs font-bold {{ !request('category') ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">All categories</a>
            @foreach($categories as $category)
                <a href="{{ route('inventory.index', array_filter(['category' => $category, 'search' => request('search'), 'status' => request('status')])) }}" class="rounded-full border px-3 py-1.5 text-xs font-bold {{ request('category') === $category ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 bg-white text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                    {{ $category }} <span class="ml-1 opacity-70">{{ $categoryCounts[$category] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 rounded-2xl border border-slate-200 bg-stone-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search item name or SKU" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <select name="category" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">Every category</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">Any stock level</option>
                <option value="low" @selected(request('status') === 'low')>Needs restocking</option>
                <option value="available" @selected(request('status') === 'available')>In stock</option>
            </select>
            <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700"><i class="fa-solid fa-magnifying-glass mr-2"></i>Find items</button>
        </form>

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
                                SKU / Item Code <span class="font-medium normal-case text-slate-400">(optional)</span>
                            </label>
                            <input type="text" name="sku" value="{{ old('sku') }}" placeholder="Leave blank to generate an item code"
                                   class="w-full px-3.5 py-2.5 text-xs font-mono rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Category <span class="text-rose-500">*</span>
                            </label>
                            <select name="category" required class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach($categories as $cat)
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

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Opening Quantity</label>
                            <input type="number" name="opening_quantity" min="0" value="{{ old('opening_quantity', 0) }}" class="w-full px-3.5 py-2.5 text-xs font-mono font-bold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
                            <p class="mt-1 text-[11px] text-slate-500">If entered, this is recorded as the opening stock movement.</p>
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
                                    <th scope="col" class="px-6 py-3.5 text-center font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
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
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('inventory.edit', $item->id) }}" class="rounded-lg bg-indigo-50 px-3 py-1.5 font-bold text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:text-indigo-300">Edit</a>
                                            <button type="button" @click="activeModal = 'adjust-{{ $item->id }}'" class="rounded-lg bg-amber-50 px-3 py-1.5 font-bold text-amber-700 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300"><i class="fa-solid fa-rotate mr-1"></i>Stock</button>
                                        </div>
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
                    @if($items->hasPages())
                        <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-700">{{ $items->links() }}</div>
                    @endif
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
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Issued to / Received from</label>
                                <input type="text" name="person_involved" placeholder="Teacher, department, or supplier" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100">
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
