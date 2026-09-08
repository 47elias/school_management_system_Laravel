<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FeeTransaction extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'student_id',
        'term_id',
        'amount',
        'channel',
        'status',
        'poll_url',
        'paynow_reference',
        'payer_phone',
        'payer_email',
        'remarks',
        'payment_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()          // Logs all attributes listed in $fillable
            ->logOnlyDirty()         // Only records a log if fields actually changed during an update
            ->dontSubmitEmptyLogs()  // Prevents saving empty log entries
            ->setDescriptionForEvent(fn(string $eventName) => "Fee Transaction record has been {$eventName}");
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    // The row created in `payments` once Paynow confirms this transaction was paid.
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
