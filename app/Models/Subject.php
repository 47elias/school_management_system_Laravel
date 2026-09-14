<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Subject extends Model
{
    use LogsActivity;

    protected $fillable = ['subject_name', 'subject_code', 'type', 'pass_mark'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()         // Logs all attributes listed in $fillable
            ->logOnlyDirty()        // Only records a log if fields actually changed during an update
            ->dontSubmitEmptyLogs() // Prevents saving empty log entries
            ->setDescriptionForEvent(fn(string $eventName) => "Subject record has been {$eventName}");
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject', 'subject_id', 'school_class_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'school_class_id', 'subject_id');
    }
}