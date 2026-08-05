<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Homework extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public $table = 'homeworks';

    protected $fillable = ['branch_id', 'batch_id', 'subject_id', 'teacher_id', 'title', 'details', 'homework_date', 'due_date', 'status', 'approval_status', 'approved_by_id', 'approved_at'];
    protected $dates = ['homework_date', 'due_date', 'created_at', 'updated_at'];
    protected $casts = ['approved_at' => 'datetime'];
    protected $appends = ['attachments'];

    public function registerMediaCollections(): void { $this->addMediaCollection('homework_attachments'); }

    public function getAttachmentsAttribute()
    {
        return $this->getMedia('homework_attachments')->map(function ($file) {
            return [
                'id'   => $file->id,
                'name' => $file->file_name,
                'url'  => $file->getUrl(),
                'mime' => $file->mime_type,
                'size' => $file->size,
            ];
        });
    }

    public function branch() { return $this->belongsTo(Branch::class); }
    public function batch() { return $this->belongsTo(Batch::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function submissions() { return $this->hasMany(HomeworkSubmission::class); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by_id'); }
}
