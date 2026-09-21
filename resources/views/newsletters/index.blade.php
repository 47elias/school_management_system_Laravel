<!DOCTYPE html>
<html lang="en">
<head>
    <title>Newsletter Management</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('components.adminlte')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --brand-primary: #a855f7; --bg-light: #f4f6f9; }
        html, body { font-family: 'Inter', sans-serif !important; background-color: var(--bg-light) !important; height: 100vh; overflow: hidden; margin: 0; }
        .wrapper { height: 100vh; display: flex; flex-direction: column; }
        .content-wrapper { flex: 1; overflow-y: auto; padding-bottom: 30px; }
        .box { border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid var(--brand-primary); background: #fff; margin-bottom: 20px; }
        .box-header { padding: 15px 20px; border-bottom: 1px solid #f4f4f4; }
        .box-body { padding: 20px; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding: 15px 20px;">
                <h1>
                    <i class="fa fa-newspaper-o" style="color: #a855f7;"></i> Newsletter Management
                    <small>Compose and broadcast institutional updates</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Newsletters</li>
                </ol>
            </section>

            <section class="content" style="padding: 0 20px;">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-radius: 4px;">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title" style="font-weight: 600;">Broadcast Directory</h3>
                        <div class="box-tools pull-right" style="top: 10px;">
                            <a href="{{ route('newsletters.create') }}" class="btn btn-sm btn-flat" style="background: #a855f7; color: white; font-weight: 600;">
                                <i class="fa fa-paper-plane" style="margin-right: 4px;"></i> COMPOSE NEWSLETTER
                            </a>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr style="background: #f4f4f4; color: #333; font-size: 11px; text-transform: uppercase;">
                                        <th>Title</th>
                                        <th>Subject Line</th>
                                        <th>Audience</th>
                                        <th>Date Created</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($newsletters ?? [] as $item)
                                    <tr>
                                        <td><strong>{{ $item->title }}</strong></td>
                                        <td>{{ $item->subject }}</td>
                                        <td><span class="label label-primary" style="text-transform: uppercase;">{{ $item->target_audience }}</span></td>
                                        <td>{{ $item->created_at->format('d M Y, H:i') }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('newsletters.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this newsletter?');" style="display:inline-block;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-default btn-xs" title="Delete"><i class="fa fa-trash text-red"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted" style="padding: 30px;">No newsletters found. Click "Compose Newsletter" to start.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
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