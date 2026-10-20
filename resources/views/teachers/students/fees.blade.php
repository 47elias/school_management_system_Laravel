@extends('layouts.admin.app')

@section('title', 'Student Statement')

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2"><i class="fa-solid fa-file-invoice-dollar text-indigo-600 dark:text-indigo-400"></i><span>Student Statement</span></h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Financial overview for {{ $student->name }} {{ $student->surname }}</p>
        </div>
        <a href="{{ route('teacher.my_class') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700"><i class="fa-solid fa-arrow-left mr-2"></i> Back to Roster</a>
    </div>
@endsection

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <section class="rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 p-6 shadow-xs">
        <div class="flex items-center gap-4">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($student->name.' '.$student->surname) }}&background=4f46e5&color=fff&size=128" class="h-16 w-16 rounded-2xl" alt="">
            <div><h2 class="font-bold text-slate-800 dark:text-slate-100">{{ $student->name }} {{ $student->surname }}</h2><p class="mt-1 font-mono text-xs text-slate-500">ID {{ $student->student_number ?? $student->id }}</p></div>
        </div>
        <div class="mt-6 space-y-3 border-t border-slate-100 dark:border-slate-700 pt-5 text-xs">
            <div class="flex justify-between gap-3"><span class="text-slate-500">Class</span><span class="font-semibold text-slate-800 dark:text-slate-200">{{ $student->schoolClass->class_name ?? '—' }}</span></div>
            <div class="flex justify-between gap-3"><span class="text-slate-500">Current balance</span><span class="font-black text-amber-600 dark:text-amber-400">${{ number_format((float) ($student->balance ?? 0), 2) }}</span></div>
        </div>
    </section>

    <section class="xl:col-span-2 overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 shadow-xs">
        <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/60"><h2 class="font-bold text-sm text-slate-800 dark:text-slate-100">Payment History</h2><p class="mt-1 text-xs text-slate-500">Transactions are not available in this teacher statement view.</p></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[520px] text-left">
            <thead class="bg-slate-50/80 dark:bg-slate-900/50 text-[10px] uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Description</th><th class="px-5 py-3">Amount</th><th class="px-5 py-3">Status</th></tr></thead>
            <tbody><tr><td colspan="4" class="px-5 py-14 text-center"><span class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400"><i class="fa-solid fa-receipt"></i></span><p class="font-bold text-sm text-slate-700 dark:text-slate-200">No payment records to display</p><p class="mt-1 text-xs text-slate-500">Contact the finance office for a full transaction statement.</p></td></tr></tbody>
        </table></div>
    </section>
</div>
@endsection
