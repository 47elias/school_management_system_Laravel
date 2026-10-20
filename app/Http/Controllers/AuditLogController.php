<?php

namespace App\Http\Controllers;

use App\Models\ActivityMark;
use App\Models\ClassActivity;
use App\Models\Exam;
use App\Models\FeeStructure;
use App\Models\FeeTransaction;
use App\Models\InventoryStock;
use App\Models\Mark;
use App\Models\Payment;
use App\Models\Payslip;
use App\Models\SubjectAssignment;
use App\Models\Timetable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $event = $request->query('event');
        $search = trim((string) $request->query('search', ''));

        $query = Audit::with([
            'user',
            'auditable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    ActivityMark::class => ['student'],
                    ClassActivity::class => ['subjectAssignment.subject', 'subjectAssignment.schoolClass'],
                    Exam::class => ['subject', 'schoolClass'],
                    FeeStructure::class => ['student'],
                    FeeTransaction::class => ['student'],
                    InventoryStock::class => ['item'],
                    Mark::class => ['student'],
                    Payment::class => ['student'],
                    Payslip::class => ['user'],
                    SubjectAssignment::class => ['subject', 'schoolClass', 'teacher'],
                    Timetable::class => ['schoolClass', 'subject', 'teacher'],
                ]);
            },
        ])->latest();

        if (in_array($event, ['created', 'updated', 'deleted', 'restored'], true)) {
            $query->where('event', $event);
        } else {
            $event = '';
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('auditable_type', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $builder->orWhere('auditable_id', $search);
                }

                $builder->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('admin.audit_logs', compact('logs', 'event', 'search'));
    }
}
