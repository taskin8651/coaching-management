<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class StudentRemark extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['student_id', 'teacher_id', 'created_by_id', 'remark_type', 'remark_date', 'title', 'remark', 'visible_to_parent', 'approval_status', 'approved_by_id', 'approved_at'];
    protected $casts = ['visible_to_parent' => 'boolean', 'approved_at' => 'datetime'];
    protected $dates = ['remark_date', 'created_at', 'updated_at'];
    protected $appends = ['attachments'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('remark_attachments');
    }

    public function getAttachmentsAttribute()
    {
        return $this->getMedia('remark_attachments')->map(function ($file) {
            return [
                'id'   => $file->id,
                'name' => $file->file_name,
                'url'  => $file->getUrl(),
                'mime' => $file->mime_type,
                'size' => $file->size,
            ];
        });
    }

    public function student() { return $this->belongsTo(Student::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_id'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by_id'); }
}
