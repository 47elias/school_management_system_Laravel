<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Edit Staff | {{ $staff->name }}</title>
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Edit Staff Member
                    <small>Modify account details for {{ $staff->name }}</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('teachers.index') }}"><i class="fa fa-users"></i> Staff List</a></li>
                    <li class="active">Edit Staff</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">

                        {{-- Validation Errors --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-ban"></i> Error!</h4>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Form wraps the entire tabbed interface so both tabs save together --}}
                        <form action="{{ route('teachers.update', $staff->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="nav-tabs-custom" style="border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden;">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#tab_account" data-toggle="tab"><i class="fa fa-user"></i> Account Information</a></li>
                                    <li><a href="#tab_roles" data-toggle="tab"><i class="fa fa-shield"></i> Roles & Permissions</a></li>
                                </ul>
                                
                                <div class="tab-content">
                                    
                                    {{-- TAB 1: Account Information --}}
                                    <div class="tab-pane active" id="tab_account" style="padding: 15px;">
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label for="name">Full Name</label>
                                                <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $staff->name) }}" required>
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label for="email">Email Address</label>
                                                <input type="email" name="email" class="form-control" id="email" value="{{ old('email', $staff->email) }}" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label for="phone_number">Phone Number</label>
                                                <input type="text" name="phone_number" class="form-control" id="phone_number" value="{{ old('phone_number', $staff->phone_number) }}">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label for="ec_number">EC Number</label>
                                                <input type="text" name="ec_number" class="form-control" id="ec_number" value="{{ old('ec_number', $staff->ec_number) }}" required>
                                            </div>
                                        </div>

                                        <hr>
                                        <h4 class="text-muted">Personal Details</h4>

                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label for="national_id">National ID</label>
                                                <input type="text" name="national_id" class="form-control" id="national_id" value="{{ old('national_id', $staff->national_id) }}" required>
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label for="dob">Date of Birth</label>
                                                <input type="date" name="dob" class="form-control" id="dob" value="{{ old('dob', $staff->dob ? \Carbon\Carbon::parse($staff->dob)->format('Y-m-d') : '') }}" required>
                                            </div>
                                        </div>

                                        <hr>
                                        <div class="callout callout-warning" style="margin-bottom: 15px;">
                                            <h4><i class="icon fa fa-lock"></i> Security</h4>
                                            <p>Leave the password field <strong>blank</strong> if you do not want to change it.</p>
                                        </div>

                                        <div class="form-group">
                                            <label for="password">New Password (Optional)</label>
                                            <input type="password" name="password" class="form-control" id="password" placeholder="Enter new password">
                                        </div>
                                    </div>

                                    {{-- TAB 2: Roles & Permissions (Spatie) --}}
                                    <div class="tab-pane" id="tab_roles" style="padding: 15px;">
                                        <div class="row">
                                            
                                            {{-- Roles Selection --}}
                                            <div class="col-md-6">
                                                <h4 class="text-bold" style="border-bottom: 1px solid #eee; padding-bottom: 10px;">Assign Roles</h4>
                                                @if(isset($roles) && $roles->count() > 0)
                                                    @foreach($roles as $role)
                                                        <div class="checkbox" style="margin-bottom: 10px;">
                                                            <label style="font-size: 15px;">
                                                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                                                    {{ $staff->hasRole($role->name) ? 'checked' : '' }}>
                                                                {{ ucfirst($role->name) }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p class="text-muted">No roles found. Ensure <code>$roles</code> is passed from the controller.</p>
                                                @endif
                                            </div>

                                            {{-- Direct Permissions Selection --}}
                                            <div class="col-md-6">
                                                <h4 class="text-bold" style="border-bottom: 1px solid #eee; padding-bottom: 10px;">Direct Permissions (Overrides)</h4>
                                                <p class="text-muted small">Permissions granted directly to the user, bypassing role restrictions.</p>
                                                
                                                @if(isset($permissions) && $permissions->count() > 0)
                                                    @foreach($permissions as $permission)
                                                        <div class="checkbox" style="margin-bottom: 10px;">
                                                            <label style="font-size: 14px;">
                                                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                                    {{ $staff->hasDirectPermission($permission->name) ? 'checked' : '' }}>
                                                                {{ ucfirst(str_replace('_', ' ', $permission->name)) }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p class="text-muted">No direct permissions available. Ensure <code>$permissions</code> is passed.</p>
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                    
                                </div>
                                
                                {{-- Global Footer --}}
                                <div class="box-footer" style="background: #f8fafc; border-top: 1px solid #f4f4f4;">
                                    <button type="submit" class="btn btn-primary text-bold" style="border-radius: 6px; padding: 8px 20px;">
                                        <i class="fa fa-save"></i> Update Staff Member
                                    </button>
                                    <a href="{{ route('teachers.index') }}" class="btn btn-default pull-right text-bold" style="border-radius: 6px; padding: 8px 20px;">Cancel</a>
                                </div>
                                
                            </div>
                        </form>

                    </div>
                </div>
            </section>
        </div>

        @include('layouts.footer')
    </div>
    @include('components.scripts')
</body>
</html>