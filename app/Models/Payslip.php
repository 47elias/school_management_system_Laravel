<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payslip extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'user_id', 'pay_period', 'base_salary',
        'allowances', 'deductions', 'net_salary',
        'payment_date', 'remarks'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()          // Logs all attributes listed in $fillable
            ->logOnlyDirty()         // Only records a log if fields actually changed during an update
            ->dontSubmitEmptyLogs()  // Prevents saving empty log entries
            ->setDescriptionForEvent(fn(string $eventName) => "Payslip record has been {$eventName}");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
