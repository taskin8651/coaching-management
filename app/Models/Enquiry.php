<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;

    public $table = 'enquiries';

    protected $dates = [
        'enquiry_date',
        'next_follow_up_date',
        'created_at',
        'updated_at',
    ];
     protected $casts = [
        'enquiry_date' => 'date',
        'next_follow_up_date' => 'date',
    ];

    protected $fillable = [
        'branch_id',
        'course_id',
        'assigned_to_id',
        'student_name',
        'phone',
        'alternate_phone',
        'email',
        'class_name',
        'school_name',
        'source',
        'enquiry_date',
        'next_follow_up_date',
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

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function followUps()
    {
        return $this->hasMany(EnquiryFollowUp::class, 'enquiry_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}