<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    public $table = 'exams';

    protected $dates = [
        'exam_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'exam_date'     => 'date',
        'total_marks'   => 'decimal:2',
        'passing_marks' => 'decimal:2',
    ];

    protected $fillable = [
        'branch_id',
        'course_id',
        'batch_id',
        'subject_id',
        'title',
        'exam_type',
        'exam_date',
        'start_time',
        'end_time',
        'total_marks',
        'passing_marks',
        'status',
        'remarks',
        'created_at',
        'updated_at',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function results()
    {
        return $this->hasMany(ExamResult::class, 'exam_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}