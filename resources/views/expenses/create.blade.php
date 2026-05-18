<!DOCTYPE html>
<html>
<head>
    <title>New Expense | {{ env('SCHOOL_ACRONYM') }}</title>
    @include('components.adminlte')
    @include('components.scripts')
    {{-- Removed Tailwind CDN --}}
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Record New Expense</h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2"> {{-- Center the form --}}
                        <div class="box box-success" style="border-radius: 4px;">
                            <div class="box-header with-border bg-black" style="color: white; border-radius: 4px 4px 0 0;">
                                <h3 class="box-title" style="color: white;">Expenditure Details</h3>
                            </div>

                            <form action="{{ route('expenses.store') }}" method="POST">
                                @csrf
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Description</label>
                                                <input type="text" name="description" required class="form-control" placeholder="e.g. Electricity Bill February">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Amount ($)</label>
                                                <input type="number" step="0.01" name="amount" required class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Date</label>
                                                <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Category</label>
                                                <select name="category" class="form-control">
                                                    <option value="Utilities">Utilities</option>
                                                    <option value="Salaries">Salaries</option>
                                                    <option value="Maintenance">Maintenance</option>
                                                    <option value="Stationery">Stationery</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Reference / Receipt No.</label>
                                                <input type="text" name="reference_no" class="form-control" placeholder="Optional">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Additional Notes</label>
                                                <textarea name="notes" rows="3" class="form-control"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="box-footer">
                                    <a href="{{ route('expenses.index') }}" class="btn btn-default">Cancel</a>
                                    <button type="submit" class="btn btn-success pull-right" style="padding: 6px 30px; font-weight: bold;">
                                        SAVE EXPENSE
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @include('layouts.footer')
</body>
</html>
