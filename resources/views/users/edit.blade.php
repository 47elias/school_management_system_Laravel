{{-- resources/views/users/edit.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Edit Staff | ' . $staff->name)

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Edit Staff Member</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Modify account details, roles, and permissions for <strong class="text-slate-700 dark:text-slate-200">{{ $staff->name }}</strong></p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('users.index') }}" 
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 shadow-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Staff Directory
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ 
        activeTab: 'account',
        selectedRoles: {{ json_encode(old('roles', $staff->roles->pluck('name')->toArray())) }},
        directPermissions: {{ json_encode(old('permissions', $staff->permissions->pluck('name')->toArray())) }},
        rolesMap: {{ json_encode($roles->mapWithKeys(fn($r) => [$r->name => $r->permissions->pluck('name')->toArray()])) }},
        permissionSearch: '',

        isRoleInherited(permName) {
            return this.selectedRoles.some(roleName => {
                return this.rolesMap[roleName] && this.rolesMap[roleName].includes(permName);
            });
        },

        get totalInheritedCount() {
            let set = new Set();
            this.selectedRoles.forEach(r => {
                if (this.rolesMap[r]) {
                    this.rolesMap[r].forEach(p => set.add(p));
                }
            });
            return set.size;
        }
    }" class="max-w-5xl mx-auto space-y-6">

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
                
                {{-- Navigation Tabs --}}
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
                        <span>Roles & Access Matrix</span>
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

                    {{-- TAB 2: Roles & Permissions Matrix --}}
                    <div x-show="activeTab === 'roles'" x-cloak class="space-y-6">
                        
                        {{-- Summary Metric Bar --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Assigned Roles</span>
                                    <span class="text-lg font-black text-indigo-600 dark:text-indigo-400" x-text="selectedRoles.length"></span>
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-users-gear"></i>
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Role Inherited Perms</span>
                                    <span class="text-lg font-black text-emerald-600 dark:text-emerald-400" x-text="totalInheritedCount"></span>
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Direct Overrides</span>
                                    <span class="text-lg font-black text-amber-600 dark:text-amber-400" x-text="directPermissions.length"></span>
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-key"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Align items at top to prevent stretch --}}
                        <div class="grid grid-cols-1 lg:grid-cols-12 items-start gap-6">
                            
                            {{-- Roles Panel (Left Side - 4 Columns) --}}
                            <div class="lg:col-span-4 bg-slate-50/80 dark:bg-slate-900/40 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 space-y-3">
                                <div class="border-b border-slate-200 dark:border-slate-700/80 pb-2.5">
                                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-100 flex items-center">
                                        <i class="fa-solid fa-users-gear text-indigo-500 mr-2"></i> 1. Select Roles
                                    </h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Role permissions automatically unlock access below.</p>
                                </div>

                                <div class="space-y-2">
                                    @foreach($roles as $role)
                                        @php $permCount = $role->permissions->count(); @endphp
                                        <label :class="selectedRoles.includes('{{ $role->name }}') ? 'bg-indigo-50/80 dark:bg-indigo-950/50 border-indigo-300 dark:border-indigo-700/80 shadow-xs' : 'bg-white dark:bg-slate-800/80 border-slate-200 dark:border-slate-700/60 hover:bg-slate-100 dark:hover:bg-slate-700/40'"
                                               class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all">
                                            <div class="flex items-center space-x-3 min-w-0">
                                                <input type="checkbox" 
                                                       name="roles[]" 
                                                       value="{{ $role->name }}"
                                                       x-model="selectedRoles"
                                                       class="w-4 h-4 text-indigo-600 rounded border-slate-300 dark:border-slate-600 focus:ring-indigo-500 transition-all">
                                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider truncate">
                                                    {{ ucfirst($role->name) }}
                                                </span>
                                            </div>
                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700/80 text-slate-600 dark:text-slate-300 shrink-0">
                                                {{ $permCount }} perms
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Permissions Panel (Right Side - 8 Columns) --}}
                            <div class="lg:col-span-8 bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 space-y-4 shadow-sm">
                                
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-slate-200/80 dark:border-slate-700/60 gap-3">
                                    <div>
                                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-100 flex items-center">
                                            <i class="fa-solid fa-key text-amber-500 mr-2"></i> 2. Permissions Access Matrix
                                        </h4>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            Role permissions are locked (<i class="fa-solid fa-lock text-[9px] text-emerald-500"></i>). Tick open boxes for direct overrides.
                                        </p>
                                    </div>

                                    {{-- Search Box --}}
                                    <div class="relative w-full sm:w-48 shrink-0">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-[11px] text-slate-400"></i>
                                        <input type="text" 
                                               x-model="permissionSearch" 
                                               placeholder="Filter permissions..." 
                                               class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                </div>

                                {{-- Natural Dynamic Height Grid (No max-h constraint or forced empty void) --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @forelse($permissions as $permission)
                                        @php $permName = $permission->name; @endphp
                                        <div x-show="!permissionSearch || '{{ strtolower($permName) }}'.includes(permissionSearch.toLowerCase())"
                                             :class="{
                                                'bg-emerald-50/60 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800/50 opacity-90 cursor-not-allowed': isRoleInherited('{{ $permName }}'),
                                                'bg-amber-50/60 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800/50': !isRoleInherited('{{ $permName }}') && directPermissions.includes('{{ $permName }}'),
                                                'bg-slate-50/50 dark:bg-slate-900/40 border-slate-200/80 dark:border-slate-700/60 hover:bg-slate-100/80 dark:hover:bg-slate-700/40': !isRoleInherited('{{ $permName }}') && !directPermissions.includes('{{ $permName }}')
                                             }"
                                             class="flex items-start p-3 rounded-xl border transition-all">
                                            
                                            {{-- Role-Inherited Checkbox (Read Only / Locked Ticked) --}}
                                            <template x-if="isRoleInherited('{{ $permName }}')">
                                                <input type="checkbox" 
                                                       checked 
                                                       disabled 
                                                       class="mt-0.5 w-4 h-4 text-emerald-600 rounded border-emerald-300 dark:border-emerald-700 cursor-not-allowed">
                                            </template>

                                            {{-- Direct Override Checkbox (Interactive) --}}
                                            <template x-if="!isRoleInherited('{{ $permName }}')">
                                                <input type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permName }}"
                                                       x-model="directPermissions"
                                                       class="mt-0.5 w-4 h-4 text-amber-600 rounded border-slate-300 dark:border-slate-600 focus:ring-amber-500 cursor-pointer">
                                            </template>

                                            <div class="ml-3 min-w-0 flex-1">
                                                <span class="text-xs font-bold font-mono text-slate-800 dark:text-slate-200 block truncate">
                                                    {{ $permName }}
                                                </span>

                                                <div class="mt-1 flex items-center space-x-1.5">
                                                    {{-- Role Inherited Badge --}}
                                                    <template x-if="isRoleInherited('{{ $permName }}')">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                                                            <i class="fa-solid fa-lock text-[8px] mr-1"></i> Role Inherited
                                                        </span>
                                                    </template>

                                                    {{-- Direct Override Badge --}}
                                                    <template x-if="!isRoleInherited('{{ $permName }}') && directPermissions.includes('{{ $permName }}')">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50">
                                                            <i class="fa-solid fa-plus text-[8px] mr-1"></i> Direct Override
                                                        </span>
                                                    </template>

                                                    {{-- Unassigned Badge --}}
                                                    <template x-if="!isRoleInherited('{{ $permName }}') && !directPermissions.includes('{{ $permName }}')">
                                                        <span class="text-[9px] text-slate-400 font-semibold">
                                                            Not Granted
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-span-2 py-8 text-center text-xs text-slate-500 dark:text-slate-400">
                                            No system permissions configured.
                                        </div>
                                    @endforelse
                                </div>

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
                            class="px-6 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition-all shadow-sm hover:shadow flex items-center space-x-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Update Staff Account</span>
                    </button>
                </div>

            </div>
        </form>

    </div>
@endsection