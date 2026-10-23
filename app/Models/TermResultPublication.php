<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermResultPublication extends Model
{
    protected $fillable = ['term_id', 'is_published', 'published_by', 'published_at'];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
