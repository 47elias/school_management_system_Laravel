<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Newsletter | {{ $newsletter->title }}</title>
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
        .box-body { padding: 25px 20px; }
        .newsletter-meta { background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 25px; }
        .meta-label { font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; display: block; }
        .meta-value { font-size: 14px; color: #1e293b; font-weight: 600; }
        .newsletter-content { font-size: 15px; color: #334155; line-height: 1.6; white-space: pre-wrap; }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding: 15px 20px;">
                <h1>
                    <i class="fa fa-newspaper-o" style="color: #a855f7;"></i> View Newsletter
                    <small>Review broadcast details</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="{{ route('newsletters.index') }}">Newsletters</a></li>
                    <li class="active">View</li>
                </ol>
            </section>

            <section class="content" style="padding: 0 20px;">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title" style="font-weight: 600;">{{ $newsletter->title }}</h3>
                        <div class="box-tools pull-right" style="top: 10px;">
                            <a href="{{ route('newsletters.index') }}" class="btn btn-default btn-sm btn-flat" style="font-weight: 600;">
                                <i class="fa fa-arrow-left" style="margin-right: 4px;"></i> BACK TO DIRECTORY
                            </a>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="newsletter-meta">
                            <div class="row">
                                <div class="col-md-4">
                                    <span class="meta-label">Subject Line</span>
                                    <span class="meta-value">{{ $newsletter->subject }}</span>
                                </div>
                                <div class="col-md-4">
                                    <span class="meta-label">Target Audience</span>
                                    <span class="meta-value label label-primary" style="text-transform: uppercase;">{{ $newsletter->target_audience }}</span>
                                </div>
                                <div class="col-md-4">
                                    <span class="meta-label">Broadcast Date</span>
                                    <span class="meta-value">{{ $newsletter->created_at->format('F d, Y \a\t h:i A') }}</span>
                                </div>
                            </div>
                        </div>

                        <h4 style="font-weight: 700; color: #1e293b; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">Message Content</h4>
                        
                        <div class="newsletter-content">{!! nl2br(e($newsletter->content)) !!}</div>
                    </div>
                </div>
            </section>
        </div>
        
        @include('layouts.footer')
    </div>
    
    @include('components.scripts')
</body>
</html>