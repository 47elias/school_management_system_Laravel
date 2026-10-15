{{-- resources/views/roles/index.blade.php --}}
@extends('layouts.admin.app')

@section('title', 'Roles & Permissions | ' . env('SCHOOL_ACRONYM', 'SIT'))

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Roles & Permissions</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage system access levels, security credentials, and user roles</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <nav class="flex text-xs text-slate-500 dark:text-slate-400">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fa-solid fa-house mr-1"></i> Home
                </a>
                <span class="mx-2">/</span>
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Roles</span>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="{ 
        activeTab: 'manage', 
        activeRole: {{ $roles->first()?->id ?? 0 }},
        showRoleModal: false,
        showPermissionModal: false
    }" class="space-y-6">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Success</h4>
                        <p class="text-xs font-medium mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg"></i>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider">Action Error</h4>
                        <p class="text-xs font-medium mt-0.5">{{ $errors->first() }}</p>
                    </div>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        {{-- Main Content Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700/60 overflow-hidden transition-colors">
            
            {{-- Navigation Tabs --}}
            <div class="border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/80 px-6 pt-3 flex space-x-2">
                <button @click="activeTab = 'manage'" 
                        :class="activeTab === 'manage' 
                            ? 'border-indigo-600 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-bold shadow-sm' 
                            : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-semibold hover:bg-slate-100 dark:hover:bg-slate-700/40'"
                        class="px-4 py-3 text-xs rounded-t-xl border-b-2 flex items-center space-x-2 transition-all">
                    <i class="fa-solid fa-gears text-sm"></i>
                    <span>Manage Roles & Permissions</span>
                </button>

                <button @click="activeTab = 'users'" 
                        :class="activeTab === 'users' 
                            ? 'border-indigo-600 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-bold shadow-sm' 
                            : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-semibold hover:bg-slate-100 dark:hover:bg-slate-700/40'"
                        class="px-4 py-3 text-xs rounded-t-xl border-b-2 flex items-center space-x-2 transition-all">
                    <i class="fa-solid fa-users text-sm"></i>
                    <span>Users by Role</span>
                </button>
            </div>

            <div class="p-6">
                
                {{-- TAB 1: Manage Roles & Permissions --}}
                <div x-show="activeTab === 'manage'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    {{-- ROLES PANEL --}}
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/60 overflow-hidden shadow-sm flex flex-col">
                        <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90 flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                                <i class="fa-solid fa-shield-halved text-rose-500 mr-2.5 text-base"></i> System Roles
                            </h3>
                            <button @click="showRoleModal = true" 
                                    type="button" 
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 rounded-lg shadow-sm transition-colors">
                                <i class="fa-solid fa-plus mr-1.5"></i> NEW ROLE
                            </button>
                        </div>

                        <div class="overflow-x-auto flex-1">
                            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/60">
                                <thead class="bg-slate-50/50 dark:bg-slate-800/50">
                                    <tr>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role Name</th>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Guard</th>
                                        <th scope="col" class="px-5 py-3 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                    @foreach($roles as $role)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-5 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400">#{{ $role->id }}</td>
                                        <td class="px-5 py-3.5 text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wide">{{ $role->name }}</td>
                                        <td class="px-5 py-3.5 text-xs">
                                            <span class="font-mono bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-600">{{ $role->guard_name }}</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-xs text-right">
                                            @if(!in_array($role->name, ['admin', 'teacher', 'receptionist']))
                                                <form action="{{ route('roles.destroy_role', $role->id) }}" method="POST" class="inline-block">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" 
                                                            onclick="return confirm('Are you sure you want to delete this role?')"
                                                            class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors" 
                                                            title="Delete Role">
                                                        <i class="fa-solid fa-trash text-sm"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600">
                                                    System Default
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- PERMISSIONS PANEL (Protected - Non-deletable) --}}
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/60 overflow-hidden shadow-sm flex flex-col">
                        <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90 flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                                <i class="fa-solid fa-key text-amber-500 mr-2.5 text-base"></i> System Permissions
                            </h3>
                            <button @click="showPermissionModal = true" 
                                    type="button" 
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-slate-900 bg-amber-400 hover:bg-amber-500 dark:bg-amber-500 dark:hover:bg-amber-400 rounded-lg shadow-sm transition-colors">
                                <i class="fa-solid fa-plus mr-1.5"></i> NEW PERMISSION
                            </button>
                        </div>

                        <div class="overflow-x-auto flex-1">
                            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/60">
                                <thead class="bg-slate-50/50 dark:bg-slate-800/50">
                                    <tr>
                                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Permission Key</th>
                                        <th scope="col" class="px-5 py-3 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Access Level</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                    @forelse($permissions as $permission)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-5 py-3.5 text-xs font-bold text-slate-700 dark:text-slate-200 font-mono">{{ $permission->name }}</td>
                                        <td class="px-5 py-3.5 text-xs text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                                                <i class="fa-solid fa-lock text-[9px] mr-1"></i> Protected
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="px-5 py-8 text-center text-xs text-slate-500 dark:text-slate-400">
                                            No system permissions configured.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                {{-- TAB 2: Users by Role --}}
                <div x-show="activeTab === 'users'" x-cloak class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    
                    {{-- Vertical Navigation for Roles --}}
                    <div class="lg:col-span-1 space-y-1 bg-slate-50/80 dark:bg-slate-900/50 p-2 rounded-xl border border-slate-200/80 dark:border-slate-700/60">
                        <div class="px-3 py-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Select Role</div>
                        @foreach($roles as $role)
                            <button @click="activeRole = {{ $role->id }}" 
                                    type="button"
                                    :class="activeRole === {{ $role->id }} 
                                        ? 'bg-indigo-600 text-white font-bold shadow-sm dark:bg-indigo-600' 
                                        : 'text-slate-700 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-800 font-medium'"
                                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs rounded-lg transition-colors">
                                <div class="flex items-center space-x-2">
                                    <i class="fa-solid fa-shield-halved text-xs opacity-80"></i>
                                    <span>{{ strtoupper($role->name) }}</span>
                                </div>
                                <span :class="activeRole === {{ $role->id }} ? 'bg-white/20 text-white' : 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400'"
                                      class="px-2 py-0.5 text-[10px] rounded-full font-bold">
                                    {{ $role->users->count() }}
                                </span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Dynamic Users Panel --}}
                    <div class="lg:col-span-3">
                        @foreach($roles as $role)
                            <div x-show="activeRole === {{ $role->id }}" x-cloak class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700/60 overflow-hidden shadow-sm">
                                <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/90">
                                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                        Users assigned to '<span class="text-indigo-600 dark:text-indigo-400">{{ ucfirst($role->name) }}</span>' Role
                                    </h3>
                                </div>

                                <div class="p-0">
                                    @if($role->users->count() > 0)
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/60">
                                                <thead class="bg-slate-50/50 dark:bg-slate-800/50">
                                                    <tr>
                                                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Staff Member</th>
                                                        <th scope="col" class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">EC Number</th>
                                                        <th scope="col" class="px-5 py-3 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                                    @foreach($role->users as $user)
                                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                                        <td class="px-5 py-3.5 whitespace-nowrap">
                                                            <div class="flex items-center">
                                                                <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 font-extrabold flex items-center justify-center text-sm mr-3 border border-indigo-200 dark:border-indigo-800/50 shrink-0">
                                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <p class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $user->name }}</p>
                                                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $user->email }}</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-5 py-3.5 whitespace-nowrap">
                                                            <span class="font-mono text-xs text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/60 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-600">
                                                                {{ $user->ec_number ?? 'N/A' }}
                                                            </span>
                                                        </td>
                                                        <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                                            <a href="{{ route('users.edit', $user->id) }}" 
                                                               class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                                                <i class="fa-solid fa-pen-to-square mr-1.5 text-indigo-500 dark:text-indigo-400"></i> Edit
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="py-12 px-4 text-center">
                                            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700/60 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto mb-3">
                                                <i class="fa-solid fa-user-slash text-xl"></i>
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">No Users Assigned</h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                                There are currently no staff members assigned to the <strong class="text-slate-700 dark:text-slate-300">{{ $role->name }}</strong> role.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>

            </div>
        </div>

        {{-- Add Role Modal --}}
        <div x-show="showRoleModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showRoleModal" 
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     @click="showRoleModal = false" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showRoleModal" 
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-shield-halved text-rose-500 mr-2"></i> Create New System Role
                        </h3>
                        <button @click="showRoleModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form action="{{ route('roles.store_role') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Role Name</label>
                            <input type="text" name="name" placeholder="e.g. bursar, librarian" required
                                   class="w-full px-3 py-2 text-xs rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>

                        <div class="pt-2 flex justify-end space-x-2">
                            <button type="button" @click="showRoleModal = false" 
                                    class="px-4 py-2 text-xs font-semibold rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 text-xs font-bold rounded-lg text-white bg-rose-600 hover:bg-rose-700 transition-colors shadow-sm">
                                Save Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Add Permission Modal --}}
        <div x-show="showPermissionModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showPermissionModal" 
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     @click="showPermissionModal = false" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showPermissionModal" 
                     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center">
                            <i class="fa-solid fa-key text-amber-500 mr-2"></i> Create Permission Key
                        </h3>
                        <button @click="showPermissionModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form action="{{ route('roles.store_permission') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Permission Name</label>
                            <input type="text" name="name" placeholder="e.g. edit grades" required
                                   class="w-full px-3 py-2 text-xs rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Formatted automatically (e.g., <code>edit_grades</code>)</p>
                        </div>

                        <div class="pt-2 flex justify-end space-x-2">
                            <button type="button" @click="showPermissionModal = false" 
                                    class="px-4 py-2 text-xs font-semibold rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 text-xs font-bold rounded-lg text-slate-900 bg-amber-400 hover:bg-amber-500 transition-colors shadow-sm">
                                Save Permission
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection