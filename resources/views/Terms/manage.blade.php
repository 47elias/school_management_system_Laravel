<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Terms | {{ env('SCHOOL_ACRONYM', 'SMS') }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('components.adminlte')
    @include('components.scripts')

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

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
            padding: 20px; 
            background: #fff; 
        }
        .content .box-title { 
            font-weight: 800 !important; 
            color: #1e293b; 
            font-size: 18px; 
        }
        
        /* Form Inputs */
        .content .form-group label { 
            font-weight: 700; 
            color: #475569; 
            font-size: 13px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            margin-bottom: 8px; 
        }
        .content .form-control { 
            border-radius: 8px; 
            border: 1px solid #cbd5e1; 
            box-shadow: none; 
            padding: 12px 15px; 
            height: auto; 
            font-size: 15px; 
            transition: all 0.3s ease; 
        }
        .content .form-control:focus { 
            border-color: #3b82f6; 
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); 
        }
        .content .input-group-addon { 
            border-radius: 8px 0 0 8px; 
            border-color: #cbd5e1; 
            background: #f8fafc; 
            color: #64748b; 
        }
        
        /* Primary Button */
        .content .btn-primary { 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
            border: none; 
            border-radius: 8px; 
            font-weight: 700; 
            padding: 12px 20px; 
            transition: transform 0.2s, box-shadow 0.2s; 
            box-shadow: 0 4px 15px rgba(59,130,246,0.3); 
        }
        .content .btn-primary:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(59,130,246,0.4); 
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
        
        /* Status Badges */
        .content .status-badge { 
            padding: 6px 15px; 
            border-radius: 30px; 
            font-size: 11px; 
            font-weight: 800; 
            letter-spacing: 1px; 
            text-transform: uppercase; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
        }
        .content .badge-active { background-color: #10b981; color: white; }
        .content .badge-inactive { background-color: #e2e8f0; color: #64748b; }

        /* Action Button */
        .content .btn-activate { 
            border-radius: 6px; 
            font-weight: 700; 
            font-size: 12px; 
            padding: 6px 15px; 
            background: #fff; 
            border: 1px solid #f59e0b; 
            color: #f59e0b; 
            transition: all 0.2s; 
        }
        .content .btn-activate:hover { 
            background: #f59e0b; 
            color: #fff; 
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <section class="content-header" style="padding-bottom: 15px;">
                <h1 style="font-weight: 800; color: #1e293b; font-size: 28px;">
                    Academic Terms
                    <small style="color: #64748b; font-weight: 600; font-size: 14px;">Manage school semesters and dates</small>
                </h1>
            </section>

            <section class="content">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(16,185,129,0.2);">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4 style="font-weight: 700; margin-bottom: 0;"><i class="icon fa fa-check-circle"></i> {{ session('success') }}</h4>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(239,68,68,0.2);">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4 style="font-weight: 700; margin-bottom: 0;"><i class="icon fa fa-ban"></i> {{ session('error') }}</h4>
                    </div>
                @endif

                <div class="row">
                    <!-- LEFT COLUMN: CREATE TERM FORM -->
                    <div class="col-md-4">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-calendar-plus-o text-blue"></i> Create New Term</h3>
                            </div>
                            <form method="POST" action="{{ route('terms.store') }}">
                                @csrf
                                <div class="box-body" style="padding: 25px;">
                                    <div class="form-group">
                                        <label>Term Name</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-bookmark"></i></span>
                                            <select name="term_name" class="form-control" required>
                                                <option value="Term 1">Term 1</option>
                                                <option value="Term 2">Term 2</option>
                                                <option value="Term 3">Term 3</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Academic Year</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-graduation-cap"></i></span>
                                            <input type="text" name="academic_year" class="form-control" value="{{ date('Y') }}" placeholder="e.g. 2026" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Start Date</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar-check-o"></i></span>
                                            <input type="date" name="start_date" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>End Date</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar-times-o"></i></span>
                                            <input type="date" name="end_date" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-footer" style="padding: 20px 25px; background: #f8fafc;">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fa fa-save"></i> SAVE ACADEMIC TERM
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: TERM HISTORY -->
                    <div class="col-md-8">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-history text-blue"></i> Term History & Management</h3>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Term Detail</th>
                                            <th>Academic Year</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($terms as $term)
                                        <tr>
                                            <td>
                                                <strong style="color: #1e293b;">{{ $term->term_name }}</strong>
                                            </td>
                                            <td>
                                                <span class="label" style="background: #e2e8f0; color: #475569; font-size: 13px; padding: 5px 10px;">
                                                    {{ $term->academic_year }}
                                                </span>
                                            </td>
                                            <td>
                                                <div style="font-size: 13px;">
                                                    <span style="color: #10b981; font-weight: 600;"><i class="fa fa-play-circle"></i> {{ \Carbon\Carbon::parse($term->start_date)->format('d M Y') }}</span><br>
                                                    <span style="color: #ef4444; font-weight: 600;"><i class="fa fa-stop-circle"></i> {{ \Carbon\Carbon::parse($term->end_date)->format('d M Y') }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($term->is_current)
                                                    <span class="status-badge badge-active"><i class="fa fa-check"></i> CURRENT</span>
                                                @else
                                                    <span class="status-badge badge-inactive">INACTIVE</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if(!$term->is_current)
                                                <form action="{{ route('terms.activate', $term->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-activate" onclick="return confirm('Set {{ $term->term_name }} ({{ $term->academic_year }}) as the active current term?');">
                                                        <i class="fa fa-toggle-on"></i> SET ACTIVE
                                                    </button>
                                                </form>
                                                @else
                                                <button class="btn btn-default btn-sm" disabled style="border-radius: 6px; font-weight: 700; color: #94a3b8; border: none; background: #f8fafc;">
                                                    <i class="fa fa-lock"></i> ACTIVE TERM
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center" style="padding: 50px 20px;">
                                                <div style="width: 70px; height: 70px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                                                    <i class="fa fa-calendar text-muted" style="font-size: 30px;"></i>
                                                </div>
                                                <h4 style="font-weight: 700; color: #475569;">No Academic Terms Found</h4>
                                                <p style="color: #94a3b8; font-size: 14px;">Use the form on the left to create the first term.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        @include('layouts.footer')
    </div>
</body>
</html>