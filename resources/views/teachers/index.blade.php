<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Staff List | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @include('components.adminlte')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">

    <style>
        .dataTables_wrapper .dataTables_filter {
            float: right;
            text-align: right;
            padding-right: 10px;
        }
        .dataTables_wrapper .dataTables_length {
            padding-left: 10px;
            padding-top: 5px;
        }
        .dataTables_wrapper .dataTables_info {
            padding-left: 10px;
            padding-bottom: 10px;
        }
        .dataTables_wrapper .dataTables_paginate {
            float: right;
            padding-right: 10px;
            padding-bottom: 10px;
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
                Staff Management
                <small>View & Search All Staff</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Staff</li>
            </ol>
        </section>

        <section class="content">
            {{-- Notifications --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-check"></i> Success!</h4>
                    {{ session('success') }}
                </div>
            @endif

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title text-bold">Active Staff Directory</h3>
                    <div class="box-tools">
                        <a href="{{ route('teachers.create') }}" class="btn btn-sm btn-success btn-flat">
                            <i class="fa fa-user-plus"></i> Add New Staff
                        </a>
                    </div>
                </div>

                <div class="box-body no-padding" style="padding-top: 15px !important;">
                    <table id="staffTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>EC Number</th>
                                <th>Role</th>
                                <th>National ID</th> {{-- Updated Column --}}
                                <th>DOB</th> {{-- Added Column --}}
                                <th>Contact</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teachers as $staff)
                            <tr>
                                <td>
                                    <span class="text-bold">{{ $staff->name }}</span><br>
                                    <small class="text-muted">{{ $staff->email }}</small>
                                </td>
                                <td><code>{{ $staff->ec_number }}</code></td>
                                <td>
                                    @if($staff->role == 'admin')
                                        <span class="label label-danger">Administrator</span>
                                    @elseif($staff->role == 'receptionist')
                                        <span class="label label-warning">Receptionist</span>
                                    @else
                                        <span class="label label-info">Teacher</span>
                                    @endif
                                </td>
                                <td>{{ $staff->national_id }}</td> {{-- Updated Field --}}
                                <td>{{ $staff->dob ? $staff->dob->format('d M Y') : 'N/A' }}</td> {{-- Added Field --}}
                                <td>{{ $staff->phone_number ?? 'N/A' }}</td>
                                <td class="text-right">
                                    <div class="btn-group">
                                        <a href="{{ route('teachers.edit', $staff->id) }}" class="btn btn-default btn-sm" title="Edit">
                                            <i class="fa fa-edit text-blue"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    @include('layouts.footer')
</div>

@include('components.scripts')

<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
        $('#staffTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "language": {
                "search": "Quick Search:",
                "lengthMenu": "Show _MENU_ staff per page"
            }
        });
    });
</script>
</body>
</html>
