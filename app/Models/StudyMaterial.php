<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class StudyMaterial extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public $table = 'study_materials';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'branch_id',
        'course_id',
        'batch_id',
        'subject_id',
        'uploaded_by_id',
        'title',
        'material_type',
        'description',
        'external_link',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'files',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('study_material_files');
    }

    public function getFilesAttribute()
    {
        return $this->getMedia('study_material_files')->map(function ($file) {
            return [
                'id'   => $file->id,
                'name' => $file->file_name,
                'url'  => $file->getUrl(),
                'mime' => $file->mime_type,
                'size' => $file->size,
            ];
        });
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

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}