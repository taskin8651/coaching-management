<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model
{
    use HasFactory;

    protected $fillable = ['homework_id', 'student_id', 'status', 'submitted_at', 'remarks', 'unique_key'];
    protected $dates = ['submitted_at', 'created_at', 'updated_at'];

    protected static function booted(): void
    {
        static::saving(function (self $submission) {
            $submission->unique_key = self::makeUniqueKey($submission->homework_id, $submission->student_id);
        });
    }

    public static function makeUniqueKey($homeworkId, $studentId): string
    {
        return implode(':', ['active', $homeworkId, $studentId]);
    }

    public function homework() { return $this->belongsTo(Homework::class); }
    public function student() { return $this->belongsTo(Student::class); }
}
