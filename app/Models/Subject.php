<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'subjects';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'branch_id',
        'course_id',
        'name',
        'subject_code',
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

    public function exams()
{
    return $this->hasMany(Exam::class, 'subject_id');
}

public function studyMaterials()
{
    return $this->hasMany(StudyMaterial::class, 'subject_id');
}

public function teacherAssignments()
{
    return $this->hasMany(TeacherAssignment::class, 'subject_id');
}

public function batches()
{
    return $this->belongsToMany(Batch::class, 'batch_subject', 'subject_id', 'batch_id')
        ->withTimestamps();
}
}
