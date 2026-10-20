<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model implements AuditableContract
{
    use Auditable;
    protected $fillable = [
        'description',
        'amount',
        'expense_date',
        'category',
        'reference_no',
        'notes'
    ];
}
