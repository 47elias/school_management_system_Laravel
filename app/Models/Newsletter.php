<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = [
        'title',
        'subject',
        'content',
        'target_audience',
        'sent_at',
    ];
}