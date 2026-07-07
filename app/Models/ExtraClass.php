<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraClass extends Model
{
    use HasFactory;

    public $table = 'extra_classes';

    protected $dates = [
        'class_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'salary_amount' => 'decimal:2',
    ];

    protected $fillable = [
        'teacher_id',
        'branch_id',
        'batch_id',
        'subject_id',
        'class_date',
        'start_time',
        'end_time',
        'reason',
        'assigned_by',
        'approval_status',
        'approved_by',
        'salary_minutes',
        'salary_amount',
        'remarks',
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

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
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
