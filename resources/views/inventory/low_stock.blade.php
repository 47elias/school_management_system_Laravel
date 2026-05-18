<!DOCTYPE html>
<html>
<head>
    <title>Low Stock Alerts | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Critical Stock Alerts <small>Items below alert level</small></h1>
            </section>

            <section class="content">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title text-red"><i class="fa fa-exclamation-triangle"></i> Action Required</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr class="bg-red">
                                    <th>Item Name</th>
                                    <th>SKU</th>
                                    <th>Current Qty</th>
                                    <th>Alert Level</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockItems as $item)
                                <tr>
                                    <td><strong>{{ $item->item_name }}</strong></td>
                                    <td><code>{{ $item->sku }}</code></td>
                                    <td><span class="badge bg-red">{{ $item->quantity }}</span></td>
                                    <td>{{ $item->alert_level }}</td>
                                    <td>
                                        @if($item->quantity == 0)
                                            <span class="label label-inverse">Out of Stock</span>
                                        @else
                                            <span class="label label-danger">Critically Low</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('inventory.index') }}" class="btn btn-xs btn-default">
                                            <i class="fa fa-plus"></i> Restock Now
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-green" style="padding: 20px;">
                                        <i class="fa fa-check-circle fa-2x"></i><br> All stock levels are healthy!
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @include('components.scripts')
</body>
</html>
