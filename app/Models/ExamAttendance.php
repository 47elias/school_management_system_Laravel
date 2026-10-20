<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttendance extends Model
{
    use HasFactory;

    protected $table = 'exam_attendances';

    protected $fillable = [
        'exam_id',
        'student_id',
        'verified_by',
        'verified_at',
        'status',
        'remarks',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}