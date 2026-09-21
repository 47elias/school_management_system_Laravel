<!DOCTYPE html>
<html lang="en">
<head>
    <title>Post Announcement</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('components.adminlte')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --brand-primary: #38bdf8; --bg-light: #f4f6f9; }
        html, body { font-family: 'Inter', sans-serif !important; background-color: var(--bg-light) !important; height: 100vh; overflow: hidden; margin: 0; }
        .wrapper { height: 100vh; display: flex; flex-direction: column; }
        .content-wrapper { flex: 1; overflow-y: auto; padding-bottom: 30px; }
        .box { border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid var(--brand-primary); background: #fff; margin-bottom: 20px; }
        .box-header { padding: 15px 20px; border-bottom: 1px solid #f4f4f4; }
        .box-body { padding: 20px; }
        .form-group label { font-weight: 600; color: #444; font-size: 13px; }
        .form-control { border-radius: 4px; border: 1px solid #ccc; box-shadow: none; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding: 15px 20px;">
                <h1>
                    <i class="fa fa-bullhorn" style="color: #38bdf8;"></i> Post Announcement
                    <small>Publish a new notice to the portal</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="{{ route('announcements.index') }}">Announcements</a></li>
                    <li class="active">Post</li>
                </ol>
            </section>

            <section class="content" style="padding: 0 20px;">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title" style="font-weight: 600;">Announcement Details</h3>
                    </div>
                    <form action="{{ route('announcements.store') }}" method="POST">
                        @csrf
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Announcement Title</label>
                                        <input type="text" name="title" class="form-control" placeholder="e.g. Early Dismissal on Friday" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Priority Level</label>
                                        <select name="priority" class="form-control">
                                            <option value="normal">Normal</option>
                                            <option value="high">High (Red Alert)</option>
                                            <option value="low">Low (Info)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Notice Content</label>
                                <textarea name="content" rows="6" class="form-control" placeholder="Write the details of the announcement here..." required></textarea>
                            </div>
                        </div>
                        <div class="box-footer" style="background: #f9f9f9; padding: 15px 20px;">
                            <button type="submit" class="btn btn-flat" style="background: #38bdf8; color: white; font-weight: 600; padding: 8px 20px;">
                                <i class="fa fa-check" style="margin-right: 5px;"></i> PUBLISH ANNOUNCEMENT
                            </button>
                            <a href="{{ route('announcements.index') }}" class="btn btn-default btn-flat" style="font-weight: 600;">Cancel</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
        @include('layouts.footer')
    </div>
    @include('components.scripts')
</body>
</html>