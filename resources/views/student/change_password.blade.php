@extends('layouts.student')

@section('content')
<div class="max-w-xl mx-auto space-y-4">
    <!-- TITLE -->
    <div class="pb-2 border-b border-slate-300 dark:border-slate-800">
        <h1 class="text-base font-black text-slate-900 dark:text-slate-100 tracking-tight">Security Settings</h1>
        <p class="text-xs font-bold text-slate-700 dark:text-slate-400">Update account password</p>
    </div>

    @if(session('success'))
        <div class="p-3.5 rounded-lg bg-emerald-200 border border-emerald-400 text-emerald-950 text-xs font-black flex items-center space-x-2">
            <i class="fa-solid fa-circle-check text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- FORM CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-300 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-300 dark:border-slate-800 flex items-center space-x-2 bg-slate-100 dark:bg-slate-950">
            <i class="fa-solid fa-shield-halved text-slate-900"></i>
            <h3 class="text-xs font-black text-slate-900 dark:text-slate-100">Change Password</h3>
        </div>

        <form action="{{ route('student.update_password') }}" method="POST" class="p-4 space-y-3.5">
            @csrf
            
            <p class="text-xs font-bold text-slate-900 dark:text-slate-300">Ensure your account uses a strong, unique password to safeguard your academic records.</p>

            <div>
                <label for="current_password" class="block text-xs font-black text-slate-900 dark:text-slate-100 mb-1">Current Password</label>
                <input type="password" 
                       name="current_password" 
                       id="current_password" 
                       placeholder="Enter old password"
                       class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-xs font-bold text-slate-900 dark:text-slate-100 placeholder-slate-700 focus:outline-none">
                @error('current_password')
                    <p class="mt-1 text-[10px] font-black text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div class="border-t border-slate-300 dark:border-slate-800 my-2"></div>

            <div>
                <label for="new_password" class="block text-xs font-black text-slate-900 dark:text-slate-100 mb-1">New Password</label>
                <input type="password" 
                       name="new_password" 
                       id="new_password" 
                       placeholder="Minimum 6 characters"
                       class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-xs font-bold text-slate-900 dark:text-slate-100 placeholder-slate-700 focus:outline-none">
                @error('new_password')
                    <p class="mt-1 text-[10px] font-black text-rose-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="new_password_confirmation" class="block text-xs font-black text-slate-900 dark:text-slate-100 mb-1">Confirm New Password</label>
                <input type="password" 
                       name="new_password_confirmation" 
                       id="new_password_confirmation" 
                       placeholder="Repeat new password"
                       class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-xs font-bold text-slate-900 dark:text-slate-100 placeholder-slate-700 focus:outline-none">
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-black transition-all flex items-center space-x-1.5">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span>Update Password</span>
                </button>
            </div>
        </form>
    </div>

    <!-- SECURITY TIP -->
    <div class="p-3.5 rounded-lg bg-amber-100 border border-amber-300 text-slate-900 text-xs flex items-start space-x-3">
        <i class="fa-solid fa-shield text-amber-700 text-sm mt-0.5 shrink-0"></i>
        <div>
            <h4 class="font-black text-slate-900">Security Requirement</h4>
            <p class="text-xs font-bold text-slate-900 mt-0.5">Never share your credentials with anyone. School administrators will never ask for your account password via email or phone.</p>
        </div>
    </div>
</div>
@endsection