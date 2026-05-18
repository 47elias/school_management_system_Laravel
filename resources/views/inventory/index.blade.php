<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>School Inventory <small>Items & Stock Levels</small></h1>
            </section>

            <section class="content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Add New Item Type</h3>
                            </div>
                            <form action="{{ route('inventory.store') }}" method="POST">
                                @csrf
                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Item Name</label>
                                        <input type="text" name="item_name" class="form-control" placeholder="e.g. Chalk, Uniform, Textbooks" required>
                                    </div>
                                    <div class="form-group">
                                        <label>SKU / Code</label>
                                        <input type="text" name="sku" class="form-control" placeholder="e.g. STAT-001" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select name="category" class="form-control">
                                            <option value="Stationery">Stationery</option>
                                            <option value="Uniforms">Uniforms</option>
                                            <option value="Furniture">Furniture</option>
                                            <option value="Electronics">Electronics</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <label>Alert Level</label>
                                            <input type="number" name="alert_level" class="form-control" value="5" title="Notify when stock falls below this">
                                        </div>
                                        <div class="col-xs-6">
                                            <label>Unit Price</label>
                                            <input type="number" name="unit_price" class="form-control" step="0.01" value="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary btn-block">Register Item</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Current Stock Status</h3>
                            </div>
                            <div class="box-body table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-gray">
                                            <th>SKU</th>
                                            <th>Item Name</th>
                                            <th>Category</th>
                                            <th>Qty</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                        <tr>
                                            <td><code>{{ $item->sku }}</code></td>
                                            <td><strong>{{ $item->item_name }}</strong></td>
                                            <td>{{ $item->category }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>
                                                @if($item->quantity <= $item->alert_level)
                                                    <span class="label label-danger">Low Stock</span>
                                                @else
                                                    <span class="label label-success">Available</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#adjustStock{{ $item->id }}">
                                                    <i class="fa fa-refresh"></i> Adjust Stock
                                                </button>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="adjustStock{{ $item->id }}">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('inventory.updateStock') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                            <h4 class="modal-title">Adjust Stock: {{ $item->item_name }}</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label>Transaction Type</label>
                                                                <select name="type" class="form-control">
                                                                    <option value="in">Restock / Purchase (+)</option>
                                                                    <option value="out">Issue / Sale / Damage (-)</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Quantity</label>
                                                                <input type="number" name="quantity" class="form-control" min="1" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Remarks</label>
                                                                <input type="text" name="remarks" class="form-control" placeholder="e.g. Received from Supplier X">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Update Stock</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @include('components.scripts')
</body>
</html>
