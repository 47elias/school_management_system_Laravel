<!DOCTYPE html>
<html>
<head>
    <title>Inventory Logs | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Stock History <small>Audit Trail</small></h1>
            </section>

            <section class="content">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Recent Stock Movements</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                    <td>{{ $log->item->item_name }}</td>
                                    <td>
                                        @if($log->type == 'in')
                                            <span class="text-green"><i class="fa fa-arrow-up"></i> Stock In</span>
                                        @else
                                            <span class="text-red"><i class="fa fa-arrow-down"></i> Stock Out</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $log->quantity }}</strong></td>
                                    <td>{{ $log->remarks ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $logs->links() }}
                    </div>
                </div>
            </section>
        </div>
    </div>
    @include('components.scripts')
</body>
</html>
