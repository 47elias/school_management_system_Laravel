<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Including 'received_by' to track which staff member processed the payment.
     */
    protected $fillable = [
        'student_id',
        'term_id',
        'amount_paid',
        'payment_date',
        'payment_method',
        'reference_no',
        'received_by',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     * Casting 'payment_date' ensures it returns a Carbon instance,
     * making it easy to format in your Blade files.
     */
    protected $casts = [
        'payment_date' => 'date',
        'amount_paid'  => 'decimal:2',
    ];

    /**
     * Get the student that owns the payment.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the term associated with the payment.
     */
    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Get the user (staff/receptionist) who received the payment.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
