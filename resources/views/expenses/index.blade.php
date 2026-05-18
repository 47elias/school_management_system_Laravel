<!DOCTYPE html>
<html>
<head>
    <title>Expense Report | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    @include('components.scripts')

    <style>
        /* Modernized AdminLTE feel without Tailwind */
        .info-card {
            background: #fff;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #dd4b39;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .info-card-label { font-size: 11px; font-weight: bold; color: #777; text-transform: uppercase; }
        .info-card-val { font-size: 24px; font-weight: bold; color: #333; display: block; }

        .action-bar {
            background: #222d32;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        @media print {
            .no-print, .main-footer, .main-header, .sidebar-toggle, .btn-xs { display: none !important; }
            .content-wrapper { background: white !important; margin-left: 0 !important; padding: 0 !important; }
            .box { border: none !important; }
            .table-bordered > thead > tr > th { background-color: #001f3f !important; color: white !important; -webkit-print-color-adjust: exact; }
            .print-header { display: block !important; text-align: center; }
        }
        .print-header { display: none; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            {{-- Print Header --}}
            <div class="print-header">
                <h2 style="margin-bottom: 5px;">{{ env('SCHOOL_NAME') }}</h2>
                <h4 style="margin-top: 0; color: #555;">Official Expenditure Report</h4>
                <p>Generated: {{ date('D, d M Y H:i') }}</p>
                <hr style="border-top: 2px solid #333;">
            </div>

            <section class="content-header no-print">
                <h1>Expense Management <small>Control & Track School Spending</small></h1>
            </section>

            <section class="content">
                {{-- Financial Summary & Actions --}}
                <div class="row no-print">
                    <div class="col-md-4">
                        <div class="info-card">
                            <span class="info-card-label">Total Expenses (This Page)</span>
                            <span class="info-card-val">${{ number_format($expenses->sum('amount'), 2) }}</span>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="action-bar">
                            <span style="color: white; font-weight: bold; text-transform: uppercase; font-size: 12px;">Quick Actions</span>
                            <div>
                                <button onclick="window.print()" class="btn btn-sm btn-primary" style="font-weight: bold; margin-right: 5px;">
                                    <i class="fa fa-print"></i> PRINT REPORT
                                </button>
                                <a href="{{ route('expenses.create') }}" class="btn btn-sm btn-success" style="font-weight: bold;">
                                    <i class="fa fa-plus"></i> ADD NEW EXPENSE
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border no-print">
                        <h3 class="box-title">Expenditure Logs</h3>
                    </div>

                    <div class="box-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" style="margin-bottom: 0;">
                                <thead>
                                    <tr class="bg-navy" style="background-color: #001f3f !important; color: white;">
                                        <th style="width: 15%">Date</th>
                                        <th>Description</th>
                                        <th>Category</th>
                                        <th>Ref No.</th>
                                        <th style="width: 15%">Amount</th>
                                        <th class="no-print text-center" style="width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenses as $expense)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                        <td>
                                            <strong style="display: block;">{{ $expense->description }}</strong>
                                            @if($expense->notes)
                                                <small class="text-muted">{{ $expense->notes }}</small>
                                            @endif
                                        </td>
                                        <td><span class="label label-default">{{ $expense->category }}</span></td>
                                        <td><code style="color: #333; background: #f4f4f4;">{{ $expense->reference_no ?? 'N/A' }}</code></td>
                                        <td>
                                            <strong class="text-red">${{ number_format($expense->amount, 2) }}</strong>
                                        </td>
                                        <td class="no-print text-center">
                                            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Delete record?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center" style="padding: 40px; color: #999;">
                                            <i class="fa fa-folder-open-o fa-3x" style="display: block; margin-bottom: 10px;"></i>
                                            No expense records found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="box-footer no-print">
                        <div class="pull-right">
                            {{ $expenses->links() }}
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @include('layouts.footer')
</body>
</html>
