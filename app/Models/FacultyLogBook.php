<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacultyLogBook extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'faculty_log_books';

    protected $dates = [
        'lecture_date',
        'approved_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_salary_eligible' => 'boolean',
    ];

    protected $fillable = [
        'teacher_id',
        'branch_id',
        'batch_id',
        'subject_id',
        'lecture_date',
        'scheduled_start_time',
        'scheduled_end_time',
        'actual_start_time',
        'actual_end_time',
        'topic_taught',
        'remarks',
        'log_status',
        'approval_status',
        'scheduled_minutes',
        'salary_minutes',
        'is_salary_eligible',
        'approved_by',
        'approved_at',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
