<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimetableSubstitution extends Model
{
    use HasFactory;

    protected $fillable = ['timetable_id', 'original_teacher_id', 'substitute_teacher_id', 'substitution_date', 'reason', 'changed_by_id', 'change_note'];
    protected $dates = ['substitution_date', 'created_at', 'updated_at'];

    public function timetable() { return $this->belongsTo(Timetable::class); }
    public function originalTeacher() { return $this->belongsTo(Teacher::class, 'original_teacher_id'); }
    public function substituteTeacher() { return $this->belongsTo(Teacher::class, 'substitute_teacher_id'); }
    public function changedBy() { return $this->belongsTo(User::class, 'changed_by_id'); }
}
