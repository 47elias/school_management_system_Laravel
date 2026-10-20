<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model implements AuditableContract
{
    use HasFactory, Auditable;

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