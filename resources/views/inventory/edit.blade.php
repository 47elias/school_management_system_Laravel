@extends('layouts.admin.app')

@section('title', 'Edit Inventory Item | ' . env('SCHOOL_ACRONYM', 'SMS'))

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Edit inventory item</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Update the item details and restock warning level. Use stock movements to change quantity.</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to inventory
        </a>
    </div>
@endsection

@section('content')
    <div class="mx-auto max-w-3xl rounded-3xl border border-slate-200 bg-stone-50 p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        @if(session('error'))
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-200">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-200">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif
        <div class="mb-6 flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300"><i class="fa-solid fa-box"></i></div>
            <div>
                <p class="font-black text-slate-900 dark:text-white">{{ $item->item_name }}</p>
                <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ $item->sku }} · {{ number_format($item->quantity) }} currently in store</p>
            </div>
        </div>

        <form action="{{ route('inventory.update', $item->id) }}" method="POST" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            @csrf
            @method('PUT')
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Item name</label>
                <input name="item_name" required maxlength="255" value="{{ old('item_name', $item->item_name) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">SKU / item code</label>
                <input name="sku" required maxlength="100" value="{{ old('sku', $item->sku) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 font-mono text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Category</label>
                <select name="category" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    @foreach($categories as $category)
                        <option value="{{ $category }}" @selected(old('category', $item->category) === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Alert when quantity reaches</label>
                <input type="number" name="alert_level" min="0" required value="{{ old('alert_level', $item->alert_level) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-300">Unit price ($)</label>
                <input type="number" name="unit_price" min="0" step="0.01" required value="{{ old('unit_price', $item->unit_price) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div class="sm:col-span-2 flex justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                <a href="{{ route('inventory.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-bold text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancel</a>
                <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-indigo-700"><i class="fa-solid fa-floppy-disk mr-2"></i>Save changes</button>
            </div>
        </form>

        <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="mt-5 flex justify-end border-t border-rose-100 pt-4 dark:border-rose-950">
            @csrf
            @method('DELETE')
            <button onclick="return confirm('Remove this inventory item? Items with stock or movement history cannot be removed.')" class="text-xs font-bold text-rose-700 hover:text-rose-900 dark:text-rose-300">Remove unused item</button>
        </form>
    </div>
@endsection
