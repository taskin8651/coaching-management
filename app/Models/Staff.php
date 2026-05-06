<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Staff extends Model implements HasMedia
{
    use SoftDeletes, HasFactory, InteractsWithMedia;

    public $table = 'staff';

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
        'designation',
        'department',
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
        $this->addMediaCollection('staff_photo')->singleFile();

        $this->addMediaCollection('staff_documents');
    }

    public function getPhotoAttribute()
    {
        $file = $this->getFirstMedia('staff_photo');

        if ($file) {
            return $file->getUrl();
        }

        return null;
    }

    public function getDocumentsAttribute()
    {
        return $this->getMedia('staff_documents')->map(function ($file) {
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
    return $this->hasMany(SalaryPayment::class, 'staff_id');
}
}