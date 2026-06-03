<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Student extends Model implements HasMedia
{
    use SoftDeletes, HasFactory, InteractsWithMedia;

    public $table = 'students';

    protected $dates = [
        'date_of_birth',
        'admission_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'user_id',
        'guardian_user_id',
        'branch_id',
        'course_id',
        'batch_id',
        'student_code',
        'biometric_id',
        'father_name',
        'mother_name',
        'guardian_name',
        'guardian_phone',
        'guardian_whatsapp',
        'phone',
        'alternate_phone',
        'emergency_contact',
        'date_of_birth',
        'gender',
        'address',
        'school_name',
        'class_name',
        'admission_date',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'photo',
        'documents',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('student_photo')->singleFile();

        $this->addMediaCollection('student_documents');
    }

    public function getPhotoAttribute()
    {
        $file = $this->getFirstMedia('student_photo');

        if ($file) {
            return $file->getUrl();
        }

        return null;
    }

    public function getDocumentsAttribute()
    {
        return $this->getMedia('student_documents')->map(function ($file) {
            return [
                'id'   => $file->id,
                'name' => $file->file_name,
                'url'  => $file->getUrl(),
            ];
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function feePayments()
{
    return $this->hasMany(FeePayment::class, 'student_id');
}

public function examResults()
{
    return $this->hasMany(ExamResult::class, 'student_id');
}

public function admissions()
{
    return $this->hasMany(Admission::class, 'student_id');
}

public function latestAdmission()
{
    return $this->hasOne(Admission::class, 'student_id')->latestOfMany();
}

public function studentBatches()
{
    return $this->hasMany(StudentBatch::class, 'student_id');
}

public function batches()
{
    return $this->belongsToMany(Batch::class, 'student_batches', 'student_id', 'batch_id')
        ->withPivot(['subject_id', 'start_date', 'end_date', 'status'])
        ->withTimestamps();
}

public function attendances()
{
    return $this->hasMany(StudentAttendance::class, 'student_id');
}

public function whatsappLogs()
{
    return $this->hasMany(WhatsappNotificationLog::class, 'student_id');
}

public function guardianUser()
{
    return $this->belongsTo(User::class, 'guardian_user_id');
}
}
