@extends('layouts.admin.app')

@section('title', 'My Teacher Profile')

@section('header')
<div class="flex w-full flex-wrap items-center justify-between gap-4">
    <div><h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">My Profile</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">View your staff details and manage your account password.</p></div>
    <a href="{{ route('teacher.dashboard') }}" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200"><i class="fa-solid fa-arrow-left mr-1.5"></i>Back to dashboard</a>
</div>
@endsection

@section('content')
@php $initials = collect(preg_split('/\s+/', trim($teacher->name)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode(''); @endphp
<div class="mx-auto grid max-w-5xl grid-cols-1 gap-5 lg:grid-cols-3">
    <section class="overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
        <div class="h-24 bg-gradient-to-r from-indigo-950 via-indigo-700 to-violet-600"></div>
        <div class="px-6 pb-6">
            <div class="-mt-10 flex h-20 w-20 items-center justify-center rounded-2xl border-4 border-white dark:border-slate-800 bg-gradient-to-br from-indigo-600 to-violet-600 text-2xl font-black text-white shadow-lg">{{ $initials ?: 'T' }}</div>
            <h2 class="mt-4 text-xl font-black text-slate-900 dark:text-white">{{ $teacher->name }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ ucfirst($teacher->role ?? 'Teacher') }}</p>
            <div class="mt-5 space-y-3 border-t border-slate-100 dark:border-slate-700 pt-4 text-xs">
                <p class="flex justify-between gap-3"><span class="text-slate-500">Staff number</span><strong class="text-right text-slate-800 dark:text-slate-100">{{ $teacher->employee_id ?: ($teacher->ec_number ?: 'Not added') }}</strong></p>
                <p class="flex justify-between gap-3"><span class="text-slate-500">Email</span><strong class="break-all text-right text-slate-800 dark:text-slate-100">{{ $teacher->email }}</strong></p>
                <p class="flex justify-between gap-3"><span class="text-slate-500">Phone</span><strong class="text-right text-slate-800 dark:text-slate-100">{{ $teacher->phone_number ?: 'Not added' }}</strong></p>
                <p class="flex justify-between gap-3"><span class="text-slate-500">Account created</span><strong class="text-right text-slate-800 dark:text-slate-100">{{ $teacher->created_at?->format('d M Y') ?? 'N/A' }}</strong></p>
            </div>
        </div>
    </section>

    <section class="lg:col-span-2 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">
        <div class="border-b border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40 px-6 py-5"><h2 class="font-black text-slate-900 dark:text-white">Account security</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Your name, email and phone number are maintained by the school administrator.</p></div>
        <div class="p-6">
            @if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 p-3 text-sm font-semibold text-emerald-800 dark:text-emerald-200"><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</div>@endif
            @if($errors->any())<div class="mb-5 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 p-3 text-sm text-rose-800 dark:text-rose-200"><ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('teacher.profile.update') }}" method="POST" class="space-y-5">
                @csrf
                <div class="flex gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200">
                    <i class="fa-solid fa-lock mt-0.5"></i>
                    <p>Your name, email address and phone number are read-only. Contact the school administrator if any of these details need to be corrected.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/30 p-4 sm:p-5">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Change password <span class="font-normal text-slate-400">(optional)</span></h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Leave both password boxes empty to keep your current password.</p>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-300">New password<input type="password" name="password" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-3 text-sm text-slate-900 dark:text-white"></label>
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Confirm password<input type="password" name="password_confirmation" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-3 text-sm text-slate-900 dark:text-white"></label>
                    </div>
                </div>
                <div class="flex justify-end"><button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 text-xs font-extrabold text-white shadow-sm hover:bg-indigo-700"><i class="fa-solid fa-key mr-1.5"></i>Update password</button></div>
            </form>
        </div>
    </section>
</div>
@endsection
