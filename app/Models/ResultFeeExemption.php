<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultFeeExemption extends Model
{
    protected $fillable = ['student_id', 'term_id', 'reason', 'granted_by', 'revoked_by', 'revoked_at'];

    protected $casts = ['revoked_at' => 'datetime'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }
}
