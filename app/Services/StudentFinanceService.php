<?php

namespace App\Services;

use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Term;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StudentFinanceService
{
    public function enrollmentDate(Student $student): Carbon
    {
        return Carbon::parse($student->enrollment_date ?? $student->created_at ?? now())->startOfDay();
    }

    public function canBillForTerm(Student $student, Term $term): bool
    {
        return $term->start_date
            && $term->end_date
            && Carbon::parse($term->start_date)->startOfDay()->lte(now()->startOfDay())
            && $this->enrollmentDate($student)->lte(Carbon::parse($term->end_date)->endOfDay());
    }

    public function availableTerms(Student $student)
    {
        $enrolled = $this->enrollmentDate($student)->toDateString();

        return Term::query()
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', $enrolled)
            ->orderByDesc('start_date')
            ->get();
    }

    public function scheduledFees(Student $student, Term $term): float
    {
        if (!$this->canBillForTerm($student, $term)) {
            return 0.0;
        }

        $individualFees = FeeStructure::where('term_id', $term->id)
            ->where('student_id', $student->id);

        // Student-specific fees are an explicit override for that student's schedule.
        if ($individualFees->exists()) {
            return (float) $individualFees->sum('amount');
        }

        $class = $student->class_id ? SchoolClass::with('gradeLevel')->find($student->class_id) : null;
        $gradeNames = collect([$student->grade, $class?->gradeLevel?->name])
            ->filter()
            ->unique()
            ->values();

        return (float) FeeStructure::where('term_id', $term->id)
            ->whereNull('student_id')
            ->whereIn('grade', $gradeNames)
            ->where(function ($query) use ($student) {
                $query->whereNull('class_id')
                    ->orWhere('class_id', $student->class_id);
            })
            ->sum('amount');
    }

    /**
     * Return term totals using posted ledger entries, falling back to the fee
     * schedule until the term invoice is posted. Older transactions dated
     * before enrolment are deliberately excluded.
     */
    public function statement(Student $student, Term $term): array
    {
        $enrollmentDate = $this->enrollmentDate($student);
        $empty = [
            'carried_forward' => 0.0,
            'scheduled_fees' => 0.0,
            'base_term_fees' => 0.0,
            'term_adjustments' => 0.0,
            'term_charges' => 0.0,
            'term_paid' => 0.0,
            'balance' => 0.0,
            'transactions' => collect(),
        ];

        if (!$this->canBillForTerm($student, $term)) {
            return $empty;
        }

        $transactions = Payment::query()
            ->with('term')
            ->where('student_id', $student->id)
            ->whereDate('payment_date', '>=', $enrollmentDate->toDateString())
            ->whereHas('term', fn ($query) => $query->whereDate('end_date', '>=', $enrollmentDate->toDateString()))
            ->get();

        $priorTerms = Term::query()
            ->whereDate('start_date', '<', Carbon::parse($term->start_date)->toDateString())
            ->whereDate('end_date', '>=', $enrollmentDate->toDateString())
            ->orderBy('start_date')
            ->get();
        $carriedForward = 0.0;
        foreach ($priorTerms as $priorTerm) {
            $priorEntries = $transactions->where('term_id', $priorTerm->id);
            $priorBreakdown = $this->termChargeBreakdown($priorEntries, $this->scheduledFees($student, $priorTerm));
            $priorCharges = $priorBreakdown['base'] + $priorBreakdown['adjustments'];
            $priorPaid = (float) $priorEntries->where('amount_paid', '>', 0)->sum('amount_paid');
            $carriedForward += $priorCharges - $priorPaid;
        }

        $termTransactions = $transactions
            ->where('term_id', $term->id)
            ->sortBy(fn ($payment) => sprintf('%s-%010d', $payment->payment_date?->format('Y-m-d') ?? '', $payment->id))
            ->values();
        $termPaid = (float) $termTransactions->where('amount_paid', '>', 0)->sum('amount_paid');
        $scheduledFees = $this->scheduledFees($student, $term);
        $chargeBreakdown = $this->termChargeBreakdown($termTransactions, $scheduledFees);
        $termCharges = $chargeBreakdown['base'] + $chargeBreakdown['adjustments'];

        return [
            'carried_forward' => (float) $carriedForward,
            'scheduled_fees' => $scheduledFees,
            'base_term_fees' => $chargeBreakdown['base'],
            'term_adjustments' => $chargeBreakdown['adjustments'],
            'term_charges' => $termCharges,
            'term_paid' => $termPaid,
            'balance' => (float) ($carriedForward + $termCharges - $termPaid),
            'transactions' => $termTransactions,
        ];
    }

    /** Record one receipt against the oldest outstanding eligible term first. */
    public function recordReceipt(Student $student, Term $selectedTerm, array $attributes): Payment
    {
        $paymentDate = Carbon::parse($attributes['payment_date'])->startOfDay();
        if (!$this->canBillForTerm($student, $selectedTerm)
            || $paymentDate->lt($this->enrollmentDate($student))) {
            throw new \InvalidArgumentException('A receipt can only be recorded for a term covered by the student’s enrolment.');
        }

        return DB::transaction(function () use ($student, $selectedTerm, $attributes, $paymentDate) {
            $student = Student::whereKey($student->id)->lockForUpdate()->firstOrFail();

            $reference = $attributes['reference_no'] ?? null;
            if ($reference) {
                $existing = Payment::where('reference_no', $reference)->lockForUpdate()->first();
                if ($existing) {
                    return $existing;
                }
            }

            $enrolled = $this->enrollmentDate($student)->toDateString();
            $terms = Term::query()
                ->whereDate('start_date', '<=', Carbon::parse($selectedTerm->start_date)->toDateString())
                ->whereDate('end_date', '>=', $enrolled)
                ->orderBy('start_date')
                ->orderBy('id')
                ->get();

            $targetTerm = $selectedTerm;
            foreach ($terms as $term) {
                if (!$this->canBillForTerm($student, $term)) {
                    continue;
                }

                if ($this->termLedgerBalance($student, $term) > 0.004) {
                    $targetTerm = $term;
                    break;
                }
            }

            $remarks = trim((string) ($attributes['remarks'] ?? ''));
            if ($targetTerm->id !== $selectedTerm->id) {
                $note = "Allocated to {$targetTerm->term_name} ({$targetTerm->academic_year}) first to clear the oldest outstanding term balance; any excess carries forward.";
                $remarks = $remarks === '' ? $note : $remarks . ' | ' . $note;
            }

            return Payment::create([
                'student_id' => $student->id,
                'term_id' => $targetTerm->id,
                'amount_paid' => abs((float) $attributes['amount_paid']),
                'payment_date' => $paymentDate->toDateString(),
                'payment_method' => $attributes['payment_method'],
                'reference_no' => $reference,
                'received_by' => $attributes['received_by'] ?? null,
                'remarks' => $remarks ?: null,
            ]);
        });
    }

    private function termLedgerBalance(Student $student, Term $term): float
    {
        $entries = Payment::query()
            ->where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->whereDate('payment_date', '>=', $this->enrollmentDate($student)->toDateString())
            ->get();
        $charges = $this->termChargeBreakdown($entries, $this->scheduledFees($student, $term));
        $paid = (float) $entries->where('amount_paid', '>', 0)->sum('amount_paid');

        return $charges['base'] + $charges['adjustments'] - $paid;
    }

    /**
     * Post each eligible student's term invoice once. Locking each student
     * row prevents simultaneous invoice runs from creating duplicate charges.
     */
    public function invoiceTerm(Term $term): array
    {
        $charged = 0;
        $skipped = 0;
        $alreadyBilled = 0;
        $noSchedule = 0;
        $enrolledAfterTerm = 0;
        $termNotStarted = 0;
        $missingTermDates = 0;

        Student::query()->where('status', 'active')->orderBy('id')->chunkById(100, function ($students) use (
            $term,
            &$charged,
            &$skipped,
            &$alreadyBilled,
            &$noSchedule,
            &$enrolledAfterTerm,
            &$termNotStarted,
            &$missingTermDates
        ) {
            foreach ($students as $student) {
                if (!$term->start_date || !$term->end_date) {
                    $skipped++;
                    $missingTermDates++;
                    continue;
                }

                if (Carbon::parse($term->start_date)->startOfDay()->gt(now()->startOfDay())) {
                    $skipped++;
                    $termNotStarted++;
                    continue;
                }

                if ($this->enrollmentDate($student)->gt(Carbon::parse($term->end_date)->endOfDay())) {
                    $skipped++;
                    $enrolledAfterTerm++;
                    continue;
                }

                DB::transaction(function () use ($student, $term, &$charged, &$skipped, &$alreadyBilled, &$noSchedule) {
                    Student::whereKey($student->id)->lockForUpdate()->first();
                    $enrollmentDate = $this->enrollmentDate($student)->toDateString();
                    $invoiceExists = Payment::query()
                        ->where('student_id', $student->id)
                        ->where('term_id', $term->id)
                        ->whereDate('payment_date', '>=', $enrollmentDate)
                        ->where(function ($query) {
                            $query->where('payment_method', 'Term Invoice')
                                ->orWhere('remarks', 'like', '%Term Fees Charged%');
                        })
                        ->exists();

                    if ($invoiceExists) {
                        $skipped++;
                        $alreadyBilled++;
                        return;
                    }

                    $amount = $this->scheduledFees($student, $term);
                    if ($amount <= 0) {
                        $skipped++;
                        $noSchedule++;
                        return;
                    }

                    $enrollmentKey = substr(sha1($enrollmentDate), 0, 8);
                    Payment::create([
                        'student_id' => $student->id,
                        'term_id' => $term->id,
                        'amount_paid' => -$amount,
                        'payment_date' => now()->toDateString(),
                        'payment_method' => 'Term Invoice',
                        'remarks' => 'Term Fees Charged (' . $term->term_name . ')',
                        'reference_no' => 'INV-' . $term->id . '-' . $student->id . '-' . $enrollmentKey,
                    ]);
                    $charged++;
                });
            }
        });

        return [
            'charged' => $charged,
            'skipped' => $skipped,
            'already_billed' => $alreadyBilled,
            'no_schedule' => $noSchedule,
            'enrolled_after_term' => $enrolledAfterTerm,
            'term_not_started' => $termNotStarted,
            'missing_term_dates' => $missingTermDates,
        ];
    }

    /** Keep already-issued invoices aligned with fee structures after an edit. */
    public function refreshTermInvoices(Term $term): int
    {
        $updated = 0;

        Student::query()->orderBy('id')->chunkById(100, function ($students) use ($term, &$updated) {
            foreach ($students as $student) {
                if (!$this->canBillForTerm($student, $term)) {
                    continue;
                }

                $invoices = Payment::query()
                    ->where('student_id', $student->id)
                    ->where('term_id', $term->id)
                    ->whereDate('payment_date', '>=', $this->enrollmentDate($student)->toDateString())
                    ->where(function ($query) {
                        $query->where('payment_method', 'Term Invoice')
                            ->orWhere('remarks', 'like', '%Term Fees Charged%');
                    })
                    ->orderBy('id')
                    ->get();

                if ($invoices->isEmpty()) {
                    continue;
                }

                $amount = $this->scheduledFees($student, $term);
                foreach ($invoices as $index => $invoice) {
                    $targetAmount = $index === 0 ? -$amount : 0;
                    if (abs((float) $invoice->amount_paid - $targetAmount) > 0.004) {
                        $invoice->amount_paid = $targetAmount;
                        $invoice->save();
                        $updated++;
                    }
                }
            }
        });

        return $updated;
    }

    private function termChargeBreakdown($entries, float $scheduledFees): array
    {
        $debits = $entries->where('amount_paid', '<', 0);
        $postedInvoice = (float) $debits
            ->filter(fn ($payment) => $payment->payment_method === 'Term Invoice'
                || str_contains((string) $payment->remarks, 'Term Fees Charged'))
            ->sum(fn ($payment) => abs((float) $payment->amount_paid));
        $otherDebits = (float) $debits
            ->reject(fn ($payment) => $payment->payment_method === 'Term Invoice'
                || str_contains((string) $payment->remarks, 'Term Fees Charged'))
            ->sum(fn ($payment) => abs((float) $payment->amount_paid));

        return [
            'base' => $postedInvoice > 0 ? $postedInvoice : $scheduledFees,
            'adjustments' => $otherDebits,
        ];
    }
}
