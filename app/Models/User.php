<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * Includes all staff fields and the critical national_id for students.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'national_id',
        'employee_id',
        'phone_number',
        'ec_number',
        'base_salary',
        'dob', // Added dob
        'bank_name',
        'bank_account_no'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'base_salary' => 'decimal:2',
        'dob' => 'date', // Cast dob to a date object
    ];

    /**
     * BOOT LOGIC
     * This solves the "Not Saving" issue. Since ec_number is NOT NULL in your DB,
     * we automatically use the national_id as the ec_number for student roles.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if ($user->role === 'student') {
                // If ec_number is empty, fill it with national_id to satisfy DB constraints
                if (empty($user->ec_number)) {
                    $user->ec_number = $user->national_id;
                }
            }
        });
    }

    /**
     * LOGIN LOGIC
     * Staff login via EC Number, Students login via National ID.
     */
    public function username()
    {
        return $this->role === 'student' ? 'national_id' : 'ec_number';
    }

    /* =========================================================================
       ROLE HELPERS
       ========================================================================= */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /* =========================================================================
       RELATIONSHIPS
       ========================================================================= */

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    /**
     * Link back to student profile if user is a student
     */
    public function studentProfile()
    {
        return $this->hasOne(Student::class, 'national_id', 'national_id');
    }
}
