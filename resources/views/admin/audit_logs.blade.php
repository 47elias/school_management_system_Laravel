<!DOCTYPE html>
<html lang="en">
<head>
    <title>Audit Logs | {{ env('SCHOOL_ACRONYM', 'SMS') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('components.adminlte')

    <!-- Scoped Modern UI Updates (Will not affect AdminLTE Footer/Layout) -->
    <style>
        /* Modern Box Styling */
        .content .box { 
            border-radius: 12px; 
            border-top: none; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
            margin-bottom: 25px; 
            overflow: hidden; 
            background: #ffffff;
        }
        .content .box-header { 
            border-bottom: 1px solid #f1f5f9; 
            padding: 20px 25px; 
            background: #fff; 
        }
        .content .box-title { 
            font-weight: 800 !important; 
            color: #1e293b; 
            font-size: 18px; 
        }
        
        /* Table overrides */
        .content .table > tbody > tr > td { 
            vertical-align: middle !important; 
            padding: 16px 20px; 
            border-top: 1px solid #f1f5f9; 
            font-size: 15px; 
            color: #334155; 
        }
        .content .table > thead > tr > th { 
            border-bottom: 2px solid #e2e8f0; 
            color: #64748b; 
            font-weight: 700; 
            padding: 16px 20px; 
            text-transform: uppercase; 
            font-size: 12px; 
            letter-spacing: 0.5px; 
            background: #f8fafc; 
        }
        .content .table-hover > tbody > tr:hover { 
            background-color: #f8fafc; 
        }

        /* Event Badges */
        .content .badge-event {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
        }
        .content .event-created { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .content .event-updated { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .content .event-deleted { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .content .event-default { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        
        /* Pagination Override */
        .content .box-footer {
            background: #fff;
            padding: 15px 25px;
            border-top: 1px solid #f1f5f9;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-collapse sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding-bottom: 15px;">
                <h1 style="font-weight: 800; color: #1e293b; font-size: 28px;">
                    System Audit Logs
                    <small style="color: #64748b; font-weight: 600; font-size: 14px;">Track user activities and system changes</small>
                </h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-history text-blue" style="margin-right: 8px;"></i> Activity Trail</h3>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 15%;">Date & Time</th>
                                            <th style="width: 20%;">Action Performed By</th>
                                            <th style="width: 20%;">Module / Record</th>
                                            <th style="width: 15%;">Event</th>
                                            <th style="width: 30%;">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($logs as $log)
                                        <tr>
                                            <td>
                                                <strong style="color: #1e293b; font-size: 15px; display: block;">{{ $log->created_at->format('d M, Y') }}</strong>
                                                <span style="color: #64748b; font-size: 13px; font-family: monospace;">{{ $log->created_at->format('H:i:s') }}</span>
                                            </td>
                                            <td>
                                                @if($log->causer)
                                                    <div style="display: flex; align-items: center;">
                                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 10px;">
                                                            {{ substr($log->causer->name, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <strong style="color: #1e293b; font-size: 14px;">{{ $log->causer->name }}</strong>
                                                            <div style="font-size: 12px; color: #64748b;">{{ class_basename($log->causer_type) }}</div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span style="color: #94a3b8; font-style: italic;"><i class="fa fa-server" style="margin-right: 4px;"></i> System / Auto</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span style="font-family: monospace; color: #475569; background: #f1f5f9; padding: 4px 8px; border-radius: 4px; border: 1px solid #e2e8f0; font-size: 13px;">
                                                    {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = 'event-default';
                                                    if($log->event === 'created') $badgeClass = 'event-created';
                                                    if($log->event === 'updated') $badgeClass = 'event-updated';
                                                    if($log->event === 'deleted') $badgeClass = 'event-deleted';
                                                @endphp
                                                <span class="badge-event {{ $badgeClass }}">{{ $log->event }}</span>
                                            </td>
                                            <td>
                                                <span style="color: #334155; font-size: 14px;">{{ $log->description }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center" style="padding: 60px 20px;">
                                                <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                                    <i class="fa fa-clock-o" style="font-size: 35px; color: #94a3b8;"></i>
                                                </div>
                                                <h4 style="font-weight: 700; color: #475569;">No Activity Found</h4>
                                                <p style="color: #94a3b8;">There are currently no recorded audit logs in the system.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            @if($logs->hasPages())
                            <div class="box-footer text-center">
                                {{ $logs->links() }}
                            </div>
                            @endif
                            
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