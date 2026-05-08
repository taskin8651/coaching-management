<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'batches';

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
        'deleted_at',
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
        'deleted_at',
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
}