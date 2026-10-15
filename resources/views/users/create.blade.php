{{-- resources/views/users/create.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Add New Staff | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Staff Registration</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Create a new system user account and assign default credentials</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('users.index') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Staff Directory
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 shadow-sm">
                <div class="flex items-start space-x-3">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg mt-0.5"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Please correct the following errors:</h4>
                        <ul class="mt-1.5 space-y-1 list-disc list-inside text-xs font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Registration Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                    <i class="fa-solid fa-user-plus text-indigo-500 mr-2.5"></i> Staff Registration Details
                </h3>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Full Name --}}
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Enter Full Name"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="user@school.ac.zw"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Staff Role --}}
                    <div>
                        <label for="role" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Staff Role / Access Level <span class="text-rose-500">*</span>
                        </label>
                        <select name="role" id="role" required
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select Access Level</option>
                            <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="receptionist" {{ old('role') == 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                    </div>

                    {{-- Phone Number --}}
                    <div>
                        <label for="phone_number" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Phone Number
                        </label>
                        <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" placeholder="+263..."
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- National ID --}}
                    <div>
                        <label for="national_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            National ID Number <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="national_id" id="national_id" value="{{ old('national_id') }}" required placeholder="63-XXXXXXX-X-XX"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- DOB --}}
                    <div>
                        <label for="dob" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Date of Birth <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="dob" id="dob" value="{{ old('dob') }}" required
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- EC Number --}}
                    <div>
                        <label for="ec_number" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            EC Number <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="ec_number" id="ec_number" value="{{ old('ec_number') }}" required placeholder="EC-9988"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Initial Login Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password" id="password" required placeholder="Min. 8 characters"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 flex items-center space-x-3 text-xs text-slate-500 dark:text-slate-400">
                    <i class="fa-solid fa-circle-info text-indigo-500 text-base shrink-0"></i>
                    <span>Passwords must be at least 8 characters long and are encrypted automatically.</span>
                </div>

                {{-- Action Footer --}}
                <div class="pt-4 border-t border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                    <a href="{{ route('users.index') }}" 
                       class="px-4 py-2.5 text-xs font-bold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-sm hover:shadow">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Save Staff Member
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection