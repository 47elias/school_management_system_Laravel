<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Expense extends Model
{
    protected $fillable = [
        'description',
        'amount',
        'expense_date',
        'category',
        'reference_no',
        'notes'
    ];
}
