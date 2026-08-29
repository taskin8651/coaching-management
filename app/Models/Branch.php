<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Branch extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public $table = 'branches';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'name',
        'branch_code',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'pincode',
        'manager_id',
        'status',
        'weekly_off_day',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'logo',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('branch_logo')->singleFile();
    }

    public function getLogoAttribute()
    {
        $file = $this->getFirstMedia('branch_logo');

        if ($file) {
            return $file->getUrl();
        }

        return null;
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function courses()
{
    return $this->hasMany(Course::class, 'branch_id');
}

public function subjects()
{
    return $this->hasMany(Subject::class, 'branch_id');
}
public function batches()
{
    return $this->hasMany(Batch::class, 'branch_id');
}

public function teachers()
{
    return $this->hasMany(Teacher::class, 'branch_id');
}

public function staff()
{
    return $this->hasMany(Staff::class, 'branch_id');
}

public function students()
{
    return $this->hasMany(Student::class, 'branch_id');
}

public function enquiries()
{
    return $this->hasMany(Enquiry::class, 'branch_id');
}

public function feePayments()
{
    return $this->hasMany(FeePayment::class, 'branch_id');
}

public function salaryPayments()
{
    return $this->hasMany(SalaryPayment::class, 'branch_id');
}

public function exams()
{
    return $this->hasMany(Exam::class, 'branch_id');
}

public function studyMaterials()
{
    return $this->hasMany(StudyMaterial::class, 'branch_id');
}

public function notices()
{
    return $this->hasMany(Notice::class, 'branch_id');
}

public function holidays()
{
    return $this->hasMany(Holiday::class, 'branch_id');
}
}