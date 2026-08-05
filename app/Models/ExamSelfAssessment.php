<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSelfAssessment extends Model
{
    use HasFactory;

    protected $fillable = ['exam_id', 'student_id', 'expected_marks', 'preparation_status', 'notes', 'submitted_at'];
    protected $casts = ['submitted_at' => 'datetime'];

    public function exam() { return $this->belongsTo(Exam::class); }
    public function student() { return $this->belongsTo(Student::class); }
}
