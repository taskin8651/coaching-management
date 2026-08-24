<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffTimetable extends Model
{
    use HasFactory;

    public $table = 'staff_timetables';

    protected $dates = [
        'schedule_date',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'staff_id',
        'branch_id',
        'day_of_week',
        'schedule_date',
        'start_time',
        'end_time',
        'location',
        'status',
        'remarks',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
