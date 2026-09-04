<!DOCTYPE html>
<html>
<head>
    <title>Payment History | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    @include('components.scripts')
    <style>
        :root {
            --grad-blue: linear-gradient(45deg, #0073b7, #00c0ef);
            --grad-green: linear-gradient(45deg, #00a65a, #2ecc71);
            --grad-red: linear-gradient(45deg, #dd4b39, #ed5565);
        }

        body { background-color: #f0f3f7 !important; font-family: 'Source Sans Pro', sans-serif; }

        .box { 
            border-radius: 12px; 
            border: none; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        /* Updated header layout to align title and actions */
        .box-header { 
            padding: 18px 15px; 
            border-bottom: 1px solid #f8fafc; 
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .box-header .box-title { font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }
        
        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .search-form {
            display: flex;
            gap: 5px;
        }
        .search-input {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 4px 12px;
            font-size: 13px;
            width: 220px;
        }

        .table-vibrant thead th {
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 11px;
            padding: 12px 10px;
            border-bottom: 2px solid #e2e8f0 !important;
        }

        .table > tbody > tr > td {
            vertical-align: middle;
            padding: 12px 10px;
        }

        .remarks-text {
            font-size: 12px;
            color: #64748b;
            max-width: 220px;
            word-wrap: break-word;
            display: block;
            line-height: 1.4;
        }
        
        .method-badge {
            background-color: #e2e8f0;
            color: #475569;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    
    {{-- DYNAMIC SIDEBAR: Loads the correct sidebar based on user role --}}
    @if(auth()->check() && auth()->user()->role === 'admin')
        @include('layouts.sidebar') {{-- Assuming this is your admin sidebar file --}}
    @elseif(auth()->check() && auth()->user()->role === 'receptionist')
        @include('layouts.receptionist_sidebar') {{-- Assuming this is your receptionist sidebar file --}}
    @else
        @include('layouts.sidebar') {{-- Fallback for any other user --}}
    @endif

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header" style="padding: 25px 25px 15px 25px;">
                <h1>
                    <span class="text-bold">Financial Records</span> 
                    <small>Payment History</small>
                </h1>
                <ol class="breadcrumb" style="top: 25px;">
                    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Payment History</li>
                </ol>
            </section>

            <section class="content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(0,a6,5a,0.1);">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="box box-primary" style="border-top: 3px solid #3c8dbc;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list text-blue"></i> Recent Transactions</h3>
                        
                        <div class="header-actions">
                            {{-- Search Bar --}}
                            <form action="{{ route('fees.index') }}" method="GET" class="search-form">
                                <input type="text" name="search" class="search-input" placeholder="Search student or ref no..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-default btn-sm" style="border-radius: 6px;"><i class="fa fa-search"></i></button>
                                @if(request('search'))
                                    <a href="{{ route('fees.index') }}" class="btn btn-danger btn-sm" style="border-radius: 6px;" title="Clear Search"><i class="fa fa-times"></i></a>
                                @endif
                            </form>

                            <a href="{{ route('fees.create') }}" class="btn btn-success btn-sm btn-flat" style="border-radius: 6px; font-weight: 600;">
                                <i class="fa fa-plus" style="margin-right: 4px;"></i> Collect New Fee
                            </a>
                        </div>
                    </div>

                    <div class="box-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-vibrant table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 120px;">Date</th>
                                        <th>Student</th>
                                        <th>Term</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th class="text-right">Amount</th>
                                        <th>Remarks / Notes</th>
                                        <th style="width: 100px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                    <tr>
                                        <td class="text-bold" style="color: #475569;">
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                                        </td>
                                        <td>
                                            <strong style="color: #1e293b;">{{ $payment->student->name }} {{ $payment->student->surname }}</strong><br>
                                            <small class="text-muted" style="font-weight: 600;">{{ $payment->student->student_number }}</small>
                                        </td>
                                        <td><span class="text-muted">{{ $payment->term->term_name }}</span></td>
                                        <td>
                                            <span class="method-badge">{{ $payment->payment_method }}</span>
                                        </td>
                                        <td>
                                            <code style="background: #f1f5f9; color: #3b82f6; border-radius: 4px; padding: 2px 6px;">
                                                {{ $payment->reference_no ?? 'N/A' }}
                                            </code>
                                        </td>
                                        <td class="text-right">
                                            <span class="text-green" style="font-weight: 700; font-size: 15px;">
                                                ${{ number_format($payment->amount_paid, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="remarks-text">
                                                {{ $payment->remarks ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('fees.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to delete this payment record? This will automatically update the student\'s balance.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs btn-flat" title="Delete Payment" style="border-radius: 4px; padding: 4px 8px; font-weight: 600;">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted" style="padding: 40px;">
                                            <i class="fa fa-inbox" style="font-size: 30px; margin-bottom: 10px; color: #cbd5e1; display: block;"></i>
                                            No payment records found {{ request('search') ? 'matching your search.' : '.' }}
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="box-footer clearfix" style="border-top: 1px solid #f8fafc; background: #fff;">
                        <div class="pull-left" style="color: #64748b; font-size: 13px; margin-top: 5px;">
                            Showing <span class="text-bold">{{ $payments->firstItem() ?? 0 }}</span> to <span class="text-bold">{{ $payments->lastItem() ?? 0 }}</span> of <span class="text-bold">{{ $payments->total() }}</span> entries
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