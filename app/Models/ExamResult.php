<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use HasFactory;

    public $table = 'exam_results';

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'total_marks'    => 'decimal:2',
        'percentage'     => 'decimal:2',
    ];

    protected $fillable = [
        'exam_id',
        'student_id',
        'marks_obtained',
        'total_marks',
        'percentage',
        'result_status',
        'rank',
        'remarks',
        'created_at',
        'updated_at',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}