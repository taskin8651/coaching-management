<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timetable extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['branch_id', 'course_id', 'batch_id', 'subject_id', 'teacher_id', 'day_of_week', 'schedule_date', 'start_time', 'end_time', 'room', 'status'];
    protected $dates = ['schedule_date', 'created_at', 'updated_at', 'deleted_at'];

    public function branch() { return $this->belongsTo(Branch::class); }
    public function course() { return $this->belongsTo(Course::class); }
    public function batch() { return $this->belongsTo(Batch::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function substitutions() { return $this->hasMany(TimetableSubstitution::class); }
}
