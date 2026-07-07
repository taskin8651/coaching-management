<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Admission extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public $table = 'admissions';

    protected $fillable = [
        'admission_no',
        'student_id',
        'branch_id',
        'course_id',
        'batch_id',
        'enquiry_id',
        'admission_date',
        'previous_school',
        'previous_class',
        'qualification',
        'father_name',
        'mother_name',
        'guardian_name',
        'guardian_relation',
        'guardian_phone',
        'guardian_whatsapp',
        'parent_email',
        'emergency_contact',
        'course_fee',
        'admission_fee',
        'discount',
        'payable_amount',
        'admission_source',
        'status',
        'remarks',
        'created_by_id',
        'created_at',
        'updated_at',
    ];

    protected $dates = [
        'admission_date',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'documents',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('admission_documents');
    }

    public function getDocumentsAttribute()
    {
        return $this->getMedia('admission_documents')->map(function ($file) {
            return [
                'id'   => $file->id,
                'name' => $file->file_name,
                'url'  => $file->getUrl(),
            ];
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
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

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class, 'enquiry_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
