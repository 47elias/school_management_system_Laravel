{{-- resources/views/users/edit.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Edit Staff | ' . $staff->name)

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Edit Staff Member</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Modify account details, roles, and direct permissions for <strong class="text-slate-700 dark:text-slate-200">{{ $staff->name }}</strong></p>
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
    <div x-data="{ activeTab: 'account' }" class="max-w-4xl mx-auto space-y-6">

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 shadow-sm">
                <div class="flex items-start space-x-3">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg mt-0.5"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Please fix the following issues:</h4>
                        <ul class="mt-1.5 space-y-1 list-disc list-inside text-xs font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('users.update', $staff->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
                
                {{-- Tabs Bar --}}
                <div class="border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 px-6 pt-3 flex space-x-2">
                    <button type="button" 
                            @click="activeTab = 'account'" 
                            :class="activeTab === 'account' 
                                ? 'border-indigo-600 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-bold shadow-sm' 
                                : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-semibold hover:bg-slate-100 dark:hover:bg-slate-700/40'"
                            class="px-4 py-3 text-xs rounded-t-xl border-b-2 flex items-center space-x-2 transition-all">
                        <i class="fa-solid fa-user-gear text-sm"></i>
                        <span>Account Information</span>
                    </button>

                    <button type="button" 
                            @click="activeTab = 'roles'" 
                            :class="activeTab === 'roles' 
                                ? 'border-indigo-600 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-bold shadow-sm' 
                                : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-semibold hover:bg-slate-100 dark:hover:bg-slate-700/40'"
                            class="px-4 py-3 text-xs rounded-t-xl border-b-2 flex items-center space-x-2 transition-all">
                        <i class="fa-solid fa-shield-halved text-sm"></i>
                        <span>Roles & Permissions</span>
                    </button>
                </div>

                <div class="p-6">
                    
                    {{-- TAB 1: Account Information --}}
                    <div x-show="activeTab === 'account'" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Full Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $staff->name) }}" required
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $staff->email) }}" required
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="phone_number" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Phone Number</label>
                                <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $staff->phone_number) }}"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="ec_number" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">EC Number</label>
                                <input type="text" name="ec_number" id="ec_number" value="{{ old('ec_number', $staff->ec_number) }}" required
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="national_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">National ID</label>
                                <input type="text" name="national_id" id="national_id" value="{{ old('national_id', $staff->national_id) }}" required
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="dob" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Date of Birth</label>
                                <input type="date" name="dob" id="dob" value="{{ old('dob', $staff->dob ? \Carbon\Carbon::parse($staff->dob)->format('Y-m-d') : '') }}" required
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/50 text-amber-800 dark:text-amber-200 space-y-1">
                            <h4 class="text-xs font-bold uppercase tracking-wider flex items-center">
                                <i class="fa-solid fa-lock text-amber-600 dark:text-amber-400 mr-2"></i> Security Password Update
                            </h4>
                            <p class="text-xs font-medium">Leave password field <strong>blank</strong> if you do not wish to change it.</p>
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">New Password (Optional)</label>
                            <input type="password" name="password" id="password" placeholder="Enter new password to update"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- TAB 2: Roles & Permissions --}}
                    <div x-show="activeTab === 'roles'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        {{-- Roles Selection --}}
                        <div class="space-y-4">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 border-b border-slate-200 dark:border-slate-700 pb-2 flex items-center">
                                <i class="fa-solid fa-users-gear text-indigo-500 mr-2"></i> Assign System Roles
                            </h4>
                            <div class="space-y-2">
                                @foreach($roles as $role)
                                    <label class="flex items-center space-x-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700/40 transition-colors">
                                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                               {{ $staff->hasRole($role->name) ? 'checked' : '' }}
                                               class="w-4 h-4 text-indigo-600 rounded border-slate-300 dark:border-slate-700 focus:ring-indigo-500">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">{{ ucfirst($role->name) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Direct Permissions --}}
                        <div class="space-y-4">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 border-b border-slate-200 dark:border-slate-700 pb-2 flex items-center">
                                <i class="fa-solid fa-key text-amber-500 mr-2"></i> Direct Permissions (Overrides)
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Permissions granted directly to this staff user, overriding role settings.</p>
                            
                            <div class="space-y-2 max-h-72 overflow-y-auto custom-scrollbar pr-1">
                                @forelse($permissions as $permission)
                                    <label class="flex items-center space-x-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700/40 transition-colors">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                               {{ $staff->hasDirectPermission($permission->name) ? 'checked' : '' }}
                                               class="w-4 h-4 text-indigo-600 rounded border-slate-300 dark:border-slate-700 focus:ring-indigo-500">
                                        <span class="text-xs font-medium text-slate-700 dark:text-slate-200 font-mono">{{ $permission->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-slate-500 dark:text-slate-400">No direct permissions available.</p>
                                @endforelse
                            </div>
                        </div>

                    </div>

                </div>

                {{-- Action Footer --}}
                <div class="px-6 py-4 border-t border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90 flex items-center justify-between">
                    <a href="{{ route('users.index') }}" 
                       class="px-4 py-2.5 text-xs font-bold rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-sm hover:shadow">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Update Staff Member
                    </button>
                </div>

            </div>
        </form>

    </div>
@endsection