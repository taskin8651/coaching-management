<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeworkSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['homework_id', 'student_id', 'status', 'submitted_at', 'remarks', 'unique_key'];
    protected $dates = ['submitted_at', 'created_at', 'updated_at', 'deleted_at'];

    protected static function booted(): void
    {
        static::saving(function (self $submission) {
            $submission->unique_key = $submission->deleted_at
                ? 'deleted:' . ($submission->id ?: uniqid('', true))
                : self::makeUniqueKey($submission->homework_id, $submission->student_id);
        });

        static::deleting(function (self $submission) {
            if (! $submission->isForceDeleting()) {
                $submission->unique_key = 'deleted:' . $submission->id;
                $submission->saveQuietly();
            }
        });
    }

    public static function makeUniqueKey($homeworkId, $studentId): string
    {
        return implode(':', ['active', $homeworkId, $studentId]);
    }

    public function homework() { return $this->belongsTo(Homework::class); }
    public function student() { return $this->belongsTo(Student::class); }
}
