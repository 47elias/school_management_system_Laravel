<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Student extends Authenticatable
{
    use Notifiable;

    protected $guard = 'student';

    /**
     * The attributes that are mass assignable.
     * UPDATED: Replaced 'age' with 'date_of_birth'
     */
    protected $fillable = [
        'student_number',
        'name',
        'surname',
        'balance',
        'date_of_birth', // Updated
        'gender',
        'national_id',
        'grade',
        'address',
        'parent_contact',
        'email',
        'phone',
        'enrollment_date',
        'status',
        'emergency_contact',
        'password',
        'term_id',
        'enrollment_status',
        'class_id'
    ];

    /**
     * Attributes hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting for data integrity.
     * UPDATED: Added date_of_birth cast, removed age
     */
    protected $casts = [
        'enrollment_date' => 'date',
        'date_of_birth' => 'date', // Added
        'balance' => 'float',
        'term_id' => 'integer',
        'class_id' => 'integer',
    ];

    protected $appends = [
        'calculated_balance',
        'monthly_installment',
        'monthly_arrears',
        'payment_status'
    ];

    /* =========================================================================
        DYNAMIC FINANCIAL LOGIC (PRESERVED)
       ========================================================================= */

    public function getCalculatedBalanceAttribute(): float
    {
        $hasIndividualFees = FeeStructure::where('term_id', $this->term_id)
            ->where('student_id', $this->id)
            ->exists();

        if ($hasIndividualFees) {
            $totalFee = FeeStructure::where('term_id', $this->term_id)
                ->where('student_id', $this->id)
                ->sum('amount');
        } else {
            $totalFee = FeeStructure::where('term_id', $this->term_id)
                ->where('grade', $this->grade)
                ->whereNull('student_id')
                ->sum('amount');
        }

        $totalPaid = $this->payments->where('term_id', $this->term_id)->sum('amount_paid');
        return (float)($totalFee - $totalPaid);
    }

    public function getMonthlyInstallmentAttribute(): float
    {
        if (!$this->term || !$this->term->start_date || !$this->term->end_date) {
            return 0.0;
        }

        $hasIndividualFees = FeeStructure::where('term_id', $this->term_id)
            ->where('student_id', $this->id)
            ->exists();

        if ($hasIndividualFees) {
            $totalFee = FeeStructure::where('term_id', $this->term_id)
                ->where('student_id', $this->id)
                ->sum('amount');
        } else {
            $totalFee = FeeStructure::where('term_id', $this->term_id)
                ->where('grade', $this->grade)
                ->whereNull('student_id')
                ->sum('amount');
        }

        $start = Carbon::parse($this->term->start_date);
        $end = Carbon::parse($this->term->end_date);
        $termDuration = max(1, $start->diffInMonths($end));

        return $totalFee > 0 ? (float)($totalFee / $termDuration) : 0.0;
    }

    public function getMonthlyArrearsAttribute(): float
    {
        if (!$this->term || !$this->term->start_date || !$this->term->end_date) {
            return 0.0;
        }

        $start = Carbon::parse($this->term->start_date);
        $end = Carbon::parse($this->term->end_date);
        $now = Carbon::now();

        $totalTermMonths = max(1, $start->diffInMonths($end));

        if ($now->lt($start)) {
            $monthsPassed = 0;
        } elseif ($now->gt($end)) {
            $monthsPassed = $totalTermMonths;
        } else {
            $monthsPassed = $start->diffInMonths($now) + 1;
        }

        $targetToDate = $this->monthly_installment * $monthsPassed;
        $totalPaid = $this->payments->where('term_id', $this->term_id)->sum('amount_paid');

        $arrears = $targetToDate - $totalPaid;
        return $arrears > 0 ? (float)min($arrears, $this->calculated_balance) : 0.0;
    }

    public function isFullyPaid(): bool
    {
        return $this->calculated_balance <= 0;
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->isFullyPaid()) return 'Fully Paid';
        return $this->monthly_arrears > 0 ? 'Monthly Arrears' : 'Up to Date';
    }

    /* =========================================================================
        RELATIONSHIPS
       ========================================================================= */

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class, 'student_id');
    }

    /**
     * Helper to get grade-based fees if no individual record exists
     */
    public function gradeFees(): HasMany
    {
        return $this->hasMany(FeeStructure::class, 'grade', 'grade')->whereNull('student_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class, 'student_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function username() { return 'student_number'; }

    /* =========================================================================
        BOOT LOGIC (UPDATED)
       ========================================================================= */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            // 1. Generate Student Number
            $latest = self::latest('id')->first();
            $nextNumber = $latest ? $latest->id + 1 : 1;

            $acronym = env('SCHOOL_ACRONYM', 'ST');
            $year = date('y');
            $regNumber = $acronym . $year . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $student->student_number = $regNumber;

            // 2. Generate System Email
            $schoolDomain = strtolower($acronym) . ".cac.zw";
            $student->email = strtolower($regNumber) . "@" . $schoolDomain;

            // 3. Set Default Metadata
            $student->enrollment_date = now();
            $student->status = 'active';

            // 4. Default Password (UPDATED: Set to surname123)
            if (!$student->password && $student->surname) {
                $student->password = Hash::make(strtolower($student->surname) . '123');
            }

            if (!$student->term_id) {
                $activeTerm = \App\Models\Term::where('is_current', true)->first();
                if ($activeTerm) {
                    $student->term_id = $activeTerm->id;
                }
            }
        });
    }
}
