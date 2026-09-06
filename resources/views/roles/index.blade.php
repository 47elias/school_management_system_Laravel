<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Roles & Permissions | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('components.adminlte')
    <style>
        /* Custom styles for the improved user list */
        .user-avatar-circle {
            width: 38px; 
            height: 38px; 
            border-radius: 50%; 
            background: #e0e7ff; 
            color: #4f46e5; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: 800; 
            font-size: 16px;
            margin-right: 12px;
            border: 1px solid #c7d2fe;
        }
        .role-nav-pills > li > a {
            border-radius: 6px;
            margin-bottom: 4px;
            color: #475569;
            font-weight: 600;
        }
        .role-nav-pills > li.active > a, 
        .role-nav-pills > li.active > a:hover {
            background-color: #ef4444 !important;
            color: #fff !important;
        }
        .role-nav-pills > li.active > a .badge {
            background-color: #fff;
            color: #ef4444;
        }
        .empty-state {
            padding: 50px 20px;
            text-align: center;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Roles & Permissions
                <small>System Access Management</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Roles</li>
            </ol>
        </section>

        <section class="content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" style="border-radius: 6px;">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-check"></i> Success!</h4>
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible" style="border-radius: 6px;">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-ban"></i> Error!</h4>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Tabbed Interface Starts Here --}}
            <div class="nav-tabs-custom" style="border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden;">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_manage" data-toggle="tab"><i class="fa fa-cogs"></i> Manage Roles & Permissions</a></li>
                    <li><a href="#tab_users" data-toggle="tab"><i class="fa fa-users"></i> Users by Role</a></li>
                </ul>
                
                <div class="tab-content" style="padding: 20px;">
                    
                    {{-- TAB 1: Manage Roles & Permissions --}}
                    <div class="tab-pane active" id="tab_manage">
                        <div class="row">
                            {{-- ROLES PANEL --}}
                            <div class="col-md-6">
                                <div class="box box-danger" style="border-radius: 8px; border-top: 4px solid #ef4444;">
                                    <div class="box-header with-border">
                                        <h3 class="box-title text-bold"><i class="fa fa-users text-danger"></i> System Roles</h3>
                                        <button class="btn btn-sm btn-danger pull-right text-bold" data-toggle="modal" data-target="#addRoleModal" style="border-radius: 4px;">
                                            <i class="fa fa-plus"></i> NEW ROLE
                                        </button>
                                    </div>
                                    <div class="box-body no-padding">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Role Name</th>
                                                    <th>Guard</th>
                                                    <th class="text-right">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($roles as $role)
                                                <tr>
                                                    <td>{{ $role->id }}</td>
                                                    <td class="text-bold text-uppercase">{{ $role->name }}</td>
                                                    <td><code>{{ $role->guard_name }}</code></td>
                                                    <td class="text-right">
                                                        @if(!in_array($role->name, ['admin', 'teacher', 'receptionist']))
                                                        <form action="{{ route('roles.destroy_role', $role->id) }}" method="POST" style="display:inline;">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-default text-red" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                                                        </form>
                                                        @else
                                                        <span class="label label-default">System Default</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- PERMISSIONS PANEL --}}
                            <div class="col-md-6">
                                <div class="box box-warning" style="border-radius: 8px; border-top: 4px solid #f59e0b;">
                                    <div class="box-header with-border">
                                        <h3 class="box-title text-bold"><i class="fa fa-key text-warning"></i> System Permissions</h3>
                                        <button class="btn btn-sm btn-warning pull-right text-bold" data-toggle="modal" data-target="#addPermissionModal" style="border-radius: 4px;">
                                            <i class="fa fa-plus"></i> NEW PERMISSION
                                        </button>
                                    </div>
                                    <div class="box-body no-padding">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Permission Key</th>
                                                    <th class="text-right">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($permissions as $permission)
                                                <tr>
                                                    <td class="text-bold">{{ $permission->name }}</td>
                                                    <td class="text-right">
                                                        <form action="{{ route('roles.destroy_permission', $permission->id) }}" method="POST" style="display:inline;">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-default text-red" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">No custom permissions found.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: Users by Role (Improved Split Layout) --}}
                    <div class="tab-pane" id="tab_users">
                        <div class="row">
                            {{-- Vertical Navigation for Roles --}}
                            <div class="col-md-3">
                                <ul class="nav nav-pills nav-stacked role-nav-pills" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; background: #f8fafc;">
                                    @foreach($roles as $index => $role)
                                        <li class="{{ $index === 0 ? 'active' : '' }}">
                                            <a href="#role_pane_{{ $role->id }}" data-toggle="tab">
                                                <i class="fa fa-shield"></i> {{ strtoupper($role->name) }}
                                                <span class="badge pull-right bg-blue">{{ $role->users->count() }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- Dynamic Content Area for Selected Role --}}
                            <div class="col-md-9">
                                <div class="tab-content" style="padding: 0; background: transparent;">
                                    @foreach($roles as $index => $role)
                                        <div class="tab-pane {{ $index === 0 ? 'active' : '' }}" id="role_pane_{{ $role->id }}">
                                            <div class="box box-solid" style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: none;">
                                                <div class="box-header with-border" style="background: #f8fafc; border-radius: 8px 8px 0 0;">
                                                    <h3 class="box-title text-bold" style="color: #334155;">Users with '{{ ucfirst($role->name) }}' Role</h3>
                                                </div>
                                                <div class="box-body no-padding">
                                                    @if($role->users->count() > 0)
                                                        <table class="table table-striped table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Staff Member</th>
                                                                    <th>EC Number</th>
                                                                    <th class="text-right">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($role->users as $user)
                                                                <tr>
                                                                    <td>
                                                                        <div style="display: flex; align-items: center;">
                                                                            <div class="user-avatar-circle">
                                                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                                                            </div>
                                                                            <div>
                                                                                <span class="text-bold" style="display: block; color: #1e293b;">{{ $user->name }}</span>
                                                                                <small class="text-muted">{{ $user->email }}</small>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td style="vertical-align: middle;">
                                                                        <code>{{ $user->ec_number ?? 'N/A' }}</code>
                                                                    </td>
                                                                    <td class="text-right" style="vertical-align: middle;">
                                                                        <a href="{{ route('teachers.edit', $user->id) }}" class="btn btn-sm btn-default" style="border-radius: 4px;">
                                                                            <i class="fa fa-edit text-blue"></i> Edit
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @else
                                                        <div class="empty-state">
                                                            <i class="fa fa-user-times"></i>
                                                            <h4 class="text-bold">No Users Assigned</h4>
                                                            <p>There are no staff members currently assigned to the <strong>{{ $role->name }}</strong> role.</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            {{-- Tabbed Interface Ends Here --}}

        </section>
    </div>

    {{-- Add Role Modal --}}
    <div class="modal fade" id="addRoleModal">
        <div class="modal-dialog modal-sm">
            <form action="{{ route('roles.store_role') }}" method="POST">
                @csrf
                <div class="modal-content" style="border-radius: 8px;">
                    <div class="modal-header" style="background: #ef4444; color: white; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:white; opacity:1;"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title text-bold">Create New Role</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Role Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. bursar, librarian" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger btn-block text-bold">SAVE ROLE</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Permission Modal --}}
    <div class="modal fade" id="addPermissionModal">
        <div class="modal-dialog modal-sm">
            <form action="{{ route('roles.store_permission') }}" method="POST">
                @csrf
                <div class="modal-content" style="border-radius: 8px;">
                    <div class="modal-header" style="background: #f59e0b; color: white; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:white; opacity:1;"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title text-bold">Create Permission</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Permission Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. edit grades" required>
                            <p class="help-block small">Will be formatted automatically (e.g. <code>edit_grades</code>)</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning btn-block text-bold">SAVE PERMISSION</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('layouts.footer')
</div>
@include('components.scripts')
</body>
</html>