<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Bulk Promotion | {{ env('SCHOOL_ACRONYM') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @include('components.adminlte')
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap.min.css">

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
        
        /* Form Inputs */
        .content .form-control-modern { 
            border-radius: 8px; 
            border: 1px solid #cbd5e1; 
            box-shadow: none; 
            padding: 10px 15px; 
            height: auto; 
            font-size: 15px; 
            transition: all 0.3s ease; 
        }
        .content .form-control-modern:focus { 
            border-color: #4f46e5; 
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); 
        }
        
        /* Execution Button */
        .content .btn-execute { 
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); 
            border: none; 
            border-radius: 8px; 
            color: white;
            font-weight: 700; 
            padding: 12px 25px; 
            transition: transform 0.2s, box-shadow 0.2s; 
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3); 
        }
        .content .btn-execute:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4); 
            color: white;
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

        /* Checkbox styling */
        .content input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #4f46e5;
            cursor: pointer;
        }

        .content .arrow-icon { 
            color: #94a3b8; 
            font-size: 18px;
            background: #f1f5f9;
            padding: 8px 12px;
            border-radius: 50%;
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
                    Mass Academic Promotion
                    <small style="color: #64748b; font-weight: 600; font-size: 14px;">Year-End Transition Management</small>
                </h1>
            </section>

            <section class="content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" style="border-radius: 8px; box-shadow: 0 4px 10px rgba(16,185,129,0.2);">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4 style="font-weight: 700; margin-bottom: 0;"><i class="icon fa fa-check-circle"></i> {{ session('success') }}</h4>
                    </div>
                @endif

                <form id="massPromotionForm" action="{{ route('students.promote.mass') }}" method="POST">
                    @csrf

                    <!-- 1. Target Academic Term -->
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-calendar text-blue"></i> 1. Target Academic Term</h3>
                        </div>
                        <div class="box-body" style="padding: 25px;">
                            <label style="font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 10px;">Promote students into term:</label>
                            <div class="input-group" style="max-width: 400px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); border-radius: 8px;">
                                <span class="input-group-addon" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1; background: #f8fafc; color: #64748b;"><i class="fa fa-bookmark"></i></span>
                                <select name="target_term_id" class="form-control form-control-modern" style="border-radius: 0 8px 8px 0; border-left: none;" required>
                                    @foreach($terms as $term)
                                        <option value="{{ $term->id }}" {{ $term->is_current ? 'selected' : '' }}>
                                            {{ $term->term_name }} ({{ $term->academic_year }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Class Mapping Overview -->
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-exchange text-indigo"></i> 2. Class Mapping Overview</h3>
                        </div>
                        <div class="box-body table-responsive no-padding">
                            <table id="promo-table" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="8%" class="text-center">Select</th>
                                        <th width="35%">Current Class (From)</th>
                                        <th width="12%" class="text-center">Transition</th>
                                        <th width="45%">Destination Class (To)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classes as $index => $class)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="promote[{{$index}}][active]" value="1" checked>
                                        </td>
                                        <td>
                                            <input type="hidden" name="promote[{{$index}}][from_class_id]" value="{{ $class->id }}">
                                            <span class="label" style="background: #e2e8f0; color: #475569; font-size: 14px; padding: 8px 12px;">{{ $class->class_name }}</span>
                                        </td>
                                        <td class="text-center">
                                            <i class="fa fa-long-arrow-right arrow-icon"></i>
                                        </td>
                                        <td>
                                            <select name="promote[{{$index}}][to_class_id]" class="form-control form-control-modern" required>
                                                <option value="">-- Select Destination --</option>
                                                @foreach($classes as $destClass)
                                                    <option value="{{ $destClass->id }}">{{ $destClass->class_name }}</option>
                                                @endforeach
                                                <option value="graduated" style="color: #ef4444; font-weight: bold;">🎓 Graduated / Alumni</option>
                                            </select>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer" style="padding: 25px; background: #f8fafc; border-top: 1px solid #f1f5f9;">
                            <button type="submit" class="btn btn-execute pull-right">
                                <i class="fa fa-rocket" style="margin-right: 5px;"></i> PROCESS ALL PROMOTIONS
                            </button>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </form>
            </section>
        </div>
        
        @include('layouts.footer')
    </div>

    @include('components.scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#promo-table').DataTable({ 
                "paging": false, 
                "searching": true,
                "info": false,
                "language": {
                    "search": "Filter Classes:"
                }
            });
            
            $('#massPromotionForm').on('submit', function() {
                return confirm("Are you sure? This will update student statuses and class assignments across your system. This action cannot be easily undone.");
            });
        });
    </script>
</body>
</html>