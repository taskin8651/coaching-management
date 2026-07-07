<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    use HasFactory;

    public $table = 'staff_attendances';

    protected $dates = [
        'attendance_date',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id',
        'teacher_id',
        'staff_id',
        'branch_id',
        'batch_id',
        'attendance_date',
        'first_in_time',
        'last_out_time',
        'worked_minutes',
        'late_minutes',
        'early_leave_minutes',
        'status',
        'source',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
