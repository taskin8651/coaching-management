<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    public $table = 'batches';

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'branch_id',
        'course_id',
        'name',
        'batch_code',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'max_students',
        'description',
        'status',
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

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

public function students()
{
    return $this->hasMany(Student::class, 'batch_id');
}

public function assignedStudents()
{
    return $this->belongsToMany(Student::class, 'student_batches', 'batch_id', 'student_id')
        ->withPivot(['subject_id', 'start_date', 'end_date', 'status'])
        ->withTimestamps();
}

public function studentBatches()
{
    return $this->hasMany(StudentBatch::class, 'batch_id');
}

public function studentAttendances()
{
    return $this->hasMany(StudentAttendance::class, 'batch_id');
}
public function feePayments()
{
    return $this->hasMany(FeePayment::class, 'batch_id');
}

public function exams()
{
    return $this->hasMany(Exam::class, 'batch_id');
}

public function studyMaterials()
{
    return $this->hasMany(StudyMaterial::class, 'batch_id');
}

public function notices()
{
    return $this->hasMany(Notice::class, 'batch_id');
}

public function teacherAssignments()
{
    return $this->hasMany(TeacherAssignment::class, 'batch_id');
}

public function subjects()
{
    return $this->belongsToMany(Subject::class, 'batch_subject', 'batch_id', 'subject_id')
        ->withTimestamps();
}
}
