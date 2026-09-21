@extends('layouts.student')

@section('content')
<!-- Scoped Modern UI Updates -->
<style>
    body { font-family: 'Inter', sans-serif !important; background: #f8fafc; }

    /* Modern Card Styling */
    .notice-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        padding: 25px;
        margin-bottom: 25px;
        border-left: 5px solid #cbd5e1;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .notice-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .priority-high { border-left-color: #ef4444; }
    .priority-normal { border-left-color: #3b82f6; }
    .priority-low { border-left-color: #10b981; }

    .newsletter-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        padding: 25px;
        margin-bottom: 25px;
        border-top: 5px solid #a855f7;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .newsletter-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    /* Typography Utilities */
    .section-title { 
        font-weight: 800 !important; 
        color: #0f172a; 
        font-size: 20px; 
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .meta-text { 
        font-size: 12px; 
        color: #64748b; 
        font-weight: 700; 
        text-transform: uppercase; 
        letter-spacing: 0.5px;
        margin-bottom: 12px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center;
    }
    
    .card-title { 
        font-size: 18px; 
        font-weight: 800; 
        color: #0f172a; 
        margin-top: 0; 
        margin-bottom: 12px; 
    }
    
    .card-body-text { 
        font-size: 14px; 
        color: #475569; 
        line-height: 1.6; 
        white-space: pre-wrap; 
        margin: 0; 
    }

    .badge-priority { 
        font-size: 10px; 
        font-weight: 800; 
        padding: 4px 10px; 
        border-radius: 6px; 
        letter-spacing: 0.5px; 
    }
    .badge-high { background: #fee2e2; color: #991b1b; }
    .badge-normal { background: #dbeafe; color: #1e40af; }
    .badge-low { background: #dcfce7; color: #166534; }
    
    .empty-state {
        background: #ffffff;
        border-radius: 12px;
        border: 1px dashed #cbd5e1;
        padding: 40px 20px;
        text-align: center;
    }
</style>

<section class="content-header" style="padding-bottom: 15px;">
    <h1 style="font-weight: 800; color: #0f172a; font-size: 28px;">
        Noticeboard & Updates
    </h1>
</section>

<section class="content">
    <div class="row">
        
        {{-- 1. SCHOOL ANNOUNCEMENTS COLUMN --}}
        <div class="col-md-6">
            <h3 class="section-title">
                <i class="fa fa-bullhorn text-blue"></i> Urgent Notices
            </h3>
            
            @forelse($announcements as $announcement)
                <div class="notice-card priority-{{ $announcement->priority }}">
                    <div class="meta-text">
                        <span><i class="fa fa-clock-o"></i> {{ $announcement->created_at->diffForHumans() }}</span>
                        
                        @if($announcement->priority == 'high')
                            <span class="badge-priority badge-high">HIGH PRIORITY</span>
                        @elseif($announcement->priority == 'normal')
                            <span class="badge-priority badge-normal">NORMAL</span>
                        @else
                            <span class="badge-priority badge-low">INFO</span>
                        @endif
                    </div>
                    <h3 class="card-title">{{ $announcement->title }}</h3>
                    <div class="card-body-text">{!! nl2br(e($announcement->content)) !!}</div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fa fa-bell-slash-o" style="font-size: 40px; color: #cbd5e1; margin-bottom: 15px;"></i>
                    <h4 style="font-weight: 700; color: #475569; margin-top: 0;">No Active Announcements</h4>
                    <p style="color: #94a3b8; font-size: 14px; margin: 0;">You're all caught up! There are no urgent notices right now.</p>
                </div>
            @endforelse
        </div>

        {{-- 2. NEWSLETTERS COLUMN --}}
        <div class="col-md-6">
            <h3 class="section-title" style="color: #0f172a;">
                <i class="fa fa-newspaper-o" style="color: #a855f7;"></i> Newsletters
            </h3>
            
            @forelse($newsletters as $newsletter)
                <div class="newsletter-card">
                    <div class="meta-text">
                        <span><i class="fa fa-calendar-check-o"></i> Published: {{ $newsletter->created_at->format('M d, Y') }}</span>
                    </div>
                    <h3 class="card-title">{{ $newsletter->title }}</h3>
                    
                    <div style="background: #f1f5f9; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e2e8f0;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Subject Line</span><br>
                        <strong style="color: #0f172a; font-size: 13px;">{{ $newsletter->subject }}</strong>
                    </div>
                    
                    <div class="card-body-text">{!! nl2br(e($newsletter->content)) !!}</div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fa fa-envelope-open-o" style="font-size: 40px; color: #cbd5e1; margin-bottom: 15px;"></i>
                    <h4 style="font-weight: 700; color: #475569; margin-top: 0;">No Newsletters Found</h4>
                    <p style="color: #94a3b8; font-size: 14px; margin: 0;">School newsletters will appear here once published.</p>
                </div>
            @endforelse
        </div>

    </div>
</section>
@endsection