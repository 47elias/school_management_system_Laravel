<!DOCTYPE html>
<html>
<head>
    <title>Manage Terms</title>
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Academic Terms</h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-4">
                        <div class="box box-primary">
                            <div class="box-header with-border"><h3 class="box-title">New Term</h3></div>
                            <form method="POST" action="{{ route('terms.store') }}">
                                @csrf
                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Term Name</label>
                                        <select name="term_name" class="form-control">
                                            <option value="Term 1">Term 1</option>
                                            <option value="Term 2">Term 2</option>
                                            <option value="Term 3">Term 3</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Academic Year</label>
                                        <input type="text" name="academic_year" class="form-control" value="{{ date('Y') }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Start Date</label>
                                        <input type="date" name="start_date" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>End Date</label>
                                        <input type="date" name="end_date" class="form-control">
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary btn-block">Set Term</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="box box-info">
                            <div class="box-header with-border"><h3 class="box-title">History</h3></div>
                            <div class="box-body no-padding">
                                <table class="table table-striped">
                                    <tr>
                                        <th>Term</th>
                                        <th>Year</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    @foreach($terms as $term)
                                    <tr>
                                        <td>{{ $term->term_name }}</td>
                                        <td>{{ $term->academic_year }}</td>
                                        <td>{{ $term->start_date }} to {{ $term->end_date }}</td>
                                        <td>
                                            @if($term->is_current)
                                                <span class="label label-success">ACTIVE</span>
                                            @else
                                                <span class="label label-default">INACTIVE</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$term->is_current)
                                            <a href="{{ route('terms.activate', $term->id) }}" class="btn btn-xs btn-warning">Activate</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @include('layouts.footer')
    @include('components.scripts')
</body>
</html>
