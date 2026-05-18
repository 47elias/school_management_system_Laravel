<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Admission extends Model
{
    use HasFactory;

    /**
     * FULLY SYNCHRONIZED WITH SQL DUMP (sit (10).sql)
     * All columns from the database table are now fillable.
     */
    protected $fillable = [
        'tracking_id',
        'identity_number',
        'student_name',
        'date_of_birth',
        'applied_grade',
        'address',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'previous_school',
        'subjects_passed',
        'academic_history',
        'results_file',
        'recommendation_letter',
        'status',
        'admin_remarks'
    ];

    /**
     * Attributes that should be cast to native types.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * MODEL EVENTS
     * Automated logic for record lifecycle.
     */
    protected static function booted()
    {
        /**
         * Before creating: Generate Tracking ID and set default status.
         */
        static::creating(function ($admission) {
            // Format: KPC-2026-X1Y2Z3
            if (empty($admission->tracking_id)) {
                $admission->tracking_id = 'KPC-' . date('Y') . '-' . strtoupper(Str::random(6));
            }

            // Default status to pending if not provided
            if (empty($admission->status)) {
                $admission->status = 'pending';
            }
        });

        /**
         * After deleting: Cleanup physical files from storage.
         */
        static::deleted(function ($admission) {
            foreach (['results_file', 'recommendation_letter'] as $field) {
                if ($admission->$field && Storage::disk('public')->exists($admission->$field)) {
                    Storage::disk('public')->delete($admission->$field);
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Helper functions for Blade & Tailwind)
    |--------------------------------------------------------------------------
    */

    /**
     * Get the full URL for the results file.
     * Usage in Blade: <a href="{{ $admission->results_url }}">Download</a>
     */
    public function getResultsUrlAttribute()
    {
        return $this->results_file ? Storage::url($this->results_file) : null;
    }

    /**
     * Get the full URL for the recommendation letter.
     */
    public function getRecommendationUrlAttribute()
    {
        return $this->recommendation_letter ? Storage::url($this->recommendation_letter) : null;
    }

    /**
     * TAILWIND BADGE COLORS
     * Returns the hex code based on the current status.
     */
    public function getStatusColorAttribute()
    {
        return [
            'pending'   => '#f59e0b', // Amber
            'reviewing' => '#3b82f6', // Blue
            'interview' => '#8b5cf6', // Violet
            'approved'  => '#10b981', // Emerald
            'declined'  => '#ef4444', // Red
            'rejected'  => '#ef4444', // Red
        ][$this->status] ?? '#6b7280'; // Gray default
    }

    /**
     * DASHBOARD GRADIENTS (For AdminLTE Cards)
     */
    public function getStatusGradientAttribute()
    {
        return [
            'pending'   => 'linear-gradient(45deg, #f39c12, #f1c40f)',
            'reviewing' => 'linear-gradient(45deg, #3498db, #2980b9)',
            'interview' => 'linear-gradient(45deg, #9b59b6, #8e44ad)',
            'approved'  => 'linear-gradient(45deg, #27ae60, #2ecc71)',
            'declined'  => 'linear-gradient(45deg, #e74c3c, #c0392b)',
            'rejected'  => 'linear-gradient(45deg, #e74c3c, #c0392b)',
        ][$this->status] ?? 'linear-gradient(45deg, #95a5a6, #7f8c8d)';
    }

    /**
     * PROGRESS BAR PERCENTAGE
     * Calculates visual progress based on the application stage.
     */
    public function getProgressPercentageAttribute()
    {
        return [
            'pending'   => 20,
            'reviewing' => 45,
            'interview' => 75,
            'approved'  => 100,
            'declined'  => 100,
            'rejected'  => 100,
        ][$this->status] ?? 0;
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES (For Admin Filtering)
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)  { return $query->where('status', 'pending'); }
    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopeDeclined($query) { return $query->where('status', 'declined'); }
    public function scopeActive($query)   { return $query->whereNotIn('status', ['approved', 'declined', 'rejected']); }
}
