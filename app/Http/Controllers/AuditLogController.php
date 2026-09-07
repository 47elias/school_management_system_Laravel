<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index()
    {
        // Fetch logs with the user who caused the action (causer)
        $logs = Activity::with('causer')->latest()->paginate(50);
        return view('admin.audit_logs', compact('logs'));
    }
}
