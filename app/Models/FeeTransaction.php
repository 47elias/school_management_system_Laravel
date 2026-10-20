<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeTransaction extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $fillable = [
        'student_id',
        'term_id',
        'amount',
        'currency',
        'channel',
        'status',
        'poll_url',
        'pesepay_reference',
        'reference',
        'payer_phone',
        'payer_email',
        'remarks',
        'payment_id',
    ];
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

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}