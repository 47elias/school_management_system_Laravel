<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Statement | {{env('SCHOOL_ACRONYM')}}</title>
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.teacher_sidebar')

        <div class="content-wrapper">
            <section class="content-header">
                <h1>Financial Statement <small>{{ $student->name }}</small></h1>
            </section>

            <section class="content">
                <div class="row">
                    {{-- Summary Card --}}
                    <div class="col-md-3">
                        <div class="box box-primary">
                            <div class="box-body box-profile">
                                <img class="profile-user-img img-responsive img-circle" src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}" alt="User profile picture">
                                <h3 class="profile-username text-center">{{ $student->name }}</h3>
                                <p class="text-muted text-center">ID: #{{ $student->student_id ?? $student->id }}</p>
                                <ul class="list-group list-group-unbordered">
                                    <li class="list-group-item">
                                        <b>Total Paid</b> <a class="pull-right text-green">$0.00</a>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Balance</b> <a class="pull-right text-red">$0.00</a>
                                    </li>
                                </ul>
                                <a href="{{ route('teacher.students') }}" class="btn btn-primary btn-block"><b>Back to List</b></a>
                            </div>
                        </div>
                    </div>

                    {{-- Transaction Table --}}
                    <div class="col-md-9">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Payment History</h3>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover">
                                    <tr class="bg-gray">
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-center">No payment records found for this term.</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        @include('layouts.footer')
    </div>
    @include('components.scripts')
</body>
</html>
