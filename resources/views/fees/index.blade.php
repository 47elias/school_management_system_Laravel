<!DOCTYPE html>
<html>
<head>
    <title>Payment History | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    @include('components.scripts')
    <style>
        .remarks-text {
            font-size: 12px;
            color: #666;
            max-width: 200px;
            word-wrap: break-word;
            display: block;
        }
        .table > tbody > tr > td {
            vertical-align: middle;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Financial Records <small>Payment History</small></h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Payment History</li>
                </ol>
            </section>

            <section class="content">
                {{-- Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> Recent Transactions</h3>
                        <div class="box-tools">
                            <a href="{{ route('fees.create') }}" class="btn btn-success btn-sm btn-flat">
                                <i class="fa fa-plus"></i> Collect New Fee
                            </a>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr class="bg-gray">
                                        <th style="width: 120px;">Date</th>
                                        <th>Student</th>
                                        <th>Term</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Amount</th>
                                        <th>Remarks/Notes</th>
                                        <th style="width: 100px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                        <td>
                                            <strong>{{ $payment->student->name }} {{ $payment->student->surname }}</strong><br>
                                            <small class="text-muted">{{ $payment->student->student_number }}</small>
                                        </td>
                                        <td>{{ $payment->term->term_name }}</td>
                                        <td>
                                            <span class="label label-default">{{ $payment->payment_method }}</span>
                                        </td>
                                        <td><code>{{ $payment->reference_no ?? 'N/A' }}</code></td>
                                        <td>
                                            <span class="text-green" style="font-weight: bold;">
                                                ${{ number_format($payment->amount_paid, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="remarks-text">
                                                {{ $payment->remarks ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            {{-- Delete Form --}}
                                            <form action="{{ route('fees.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to delete this payment record? This will automatically update the student\'s balance.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs btn-flat" title="Delete Payment">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No payment records found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="box-footer clearfix">
                        <div class="pull-left">
                            Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} entries
                        </div>
                        <div class="pull-right">
                            {{ $payments->links() }}
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @include('layouts.footer')
</body>
</html>
