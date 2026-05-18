<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['subject_name', 'subject_code', 'type', 'pass_mark'];
    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject', 'subject_id', 'school_class_id');
    }
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'school_class_id', 'subject_id');
    }
}
