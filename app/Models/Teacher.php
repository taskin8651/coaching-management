<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Teacher extends Model implements HasMedia
{
    use SoftDeletes, HasFactory, InteractsWithMedia;

    public $table = 'teachers';

    protected $dates = [
        'joining_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'user_id',
        'branch_id',
        'phone',
        'alternate_phone',
        'qualification',
        'experience',
        'subject_specialization',
        'address',
        'salary',
        'joining_date',
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
        $this->addMediaCollection('teacher_photo')->singleFile();

        $this->addMediaCollection('teacher_documents');
    }

    public function getPhotoAttribute()
    {
        $file = $this->getFirstMedia('teacher_photo');

        if ($file) {
            return $file->getUrl();
        }

        return null;
    }

    public function getDocumentsAttribute()
    {
        return $this->getMedia('teacher_documents')->map(function ($file) {
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

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function salaryPayments()
{
    return $this->hasMany(SalaryPayment::class, 'teacher_id');
}
}