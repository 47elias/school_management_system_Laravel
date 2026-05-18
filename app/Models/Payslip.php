<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'pay_period', 'base_salary',
        'allowances', 'deductions', 'net_salary',
        'payment_date', 'remarks'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
