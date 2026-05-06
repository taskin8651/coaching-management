<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Notice extends Model implements HasMedia
{
    use SoftDeletes, HasFactory, InteractsWithMedia;

    public $table = 'notices';

    protected $dates = [
        'publish_date',
        'expiry_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'publish_date' => 'date',
        'expiry_date'  => 'date',
    ];

    protected $fillable = [
        'branch_id',
        'course_id',
        'batch_id',
        'created_by_id',
        'title',
        'notice_type',
        'target_audience',
        'description',
        'publish_date',
        'expiry_date',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'attachments',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('notice_attachments');
    }

    public function getAttachmentsAttribute()
    {
        return $this->getMedia('notice_attachments')->map(function ($file) {
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}