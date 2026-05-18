<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Term extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'term_name',
        'academic_year',
        'start_date',
        'end_date',
        'is_current',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_current' => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    /**
     * Boot logic for the model.
     * Guarantees that only one term is ever marked as 'is_current'.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function (Term $term) {
            if ($term->is_current) {
                // Deactivate all other terms before saving this one as current
                static::where('id', '!=', $term->id)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
            }
        });
    }

    /**
     * RELATIONSHIPS
     * -------------------------------------------------------------------------
     */

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * SCOPES
     * -------------------------------------------------------------------------
     */

    public function scopeCurrent(Builder $query)
    {
        return $query->where('is_current', true);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active');
    }

    /**
     * BILLING LOGIC (3-MONTH CYCLE)
     * -------------------------------------------------------------------------
     */

    /**
     * Force the billing duration to exactly 3 months.
     * Preserved Logic: Fixed 3 month cycle.
     * * @return int
     */
    public function getBillingDuration(): int
    {
        return 3;
    }

    /**
     * Generates the 3 specific Carbon dates for the billing cycle.
     * Preserved Logic: Picks the first day of the first three months of the term.
     * * @return array<Carbon>
     */
    public function getBillingMonthDates(): array
    {
        $dates = [];

        // Use the casted start_date property
        $start = ($this->start_date ?? Carbon::now())->copy()->startOfMonth();

        for ($i = 0; $i < $this->getBillingDuration(); $i++) {
            $dates[] = $start->copy()->addMonths($i);
        }

        return $dates;
    }

    /**
     * HELPERS
     * -------------------------------------------------------------------------
     */

    /**
     * Helper to check if the term is currently open for payments.
     * * @return bool
     */
    public function isOpen(): bool
    {
        return $this->status === 'open' || $this->status === 'active';
    }

    /**
     * Formats the term name for display (e.g., "Term 1 - 2026")
     * * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->term_name} ({$this->academic_year})";
    }
}
