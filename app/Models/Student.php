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
        'branch_id',
        'course_id',
        'batch_id',
        'student_code',
        'father_name',
        'mother_name',
        'phone',
        'alternate_phone',
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
}