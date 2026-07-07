<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Course extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public $table = 'courses';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'branch_id',
        'name',
        'course_code',
        'duration',
        'fee',
        'description',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'image',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('course_image')->singleFile();
    }

    public function getImageAttribute()
    {
        $file = $this->getFirstMedia('course_image');

        if ($file) {
            return $file->getUrl();
        }

        return null;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function subjects()
{
    return $this->hasMany(Subject::class, 'course_id');
}
public function batches()
{
    return $this->hasMany(Batch::class, 'course_id');
}

public function students()
{
    return $this->hasMany(Student::class, 'course_id');
}

public function enquiries()
{
    return $this->hasMany(Enquiry::class, 'course_id');
}

public function feePayments()
{
    return $this->hasMany(FeePayment::class, 'course_id');
}

public function exams()
{
    return $this->hasMany(Exam::class, 'course_id');
}
public function studyMaterials()
{
    return $this->hasMany(StudyMaterial::class, 'course_id');
}

public function teacherAssignments()
{
    return $this->hasMany(TeacherAssignment::class, 'course_id');
}
}