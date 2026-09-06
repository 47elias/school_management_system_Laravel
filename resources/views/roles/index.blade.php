<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Roles & Permissions | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('components.adminlte')
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

            <div class="row">
                {{-- ROLES PANEL --}}
                <div class="col-md-6">
                    <div class="box box-danger" style="border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 4px solid #ef4444;">
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
                    <div class="box box-warning" style="border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 4px solid #f59e0b;">
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