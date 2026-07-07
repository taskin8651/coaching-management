<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBatch extends Model
{
    use HasFactory;

    public $table = 'student_batches';

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'student_id',
        'batch_id',
        'subject_id',
        'start_date',
        'end_date',
        'status',
        'unique_key',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $studentBatch) {
            $studentBatch->unique_key = self::makeUniqueKey(
                $studentBatch->student_id,
                $studentBatch->batch_id,
                $studentBatch->subject_id
            );
        });
    }

    public static function makeUniqueKey($studentId, $batchId, $subjectId = null): string
    {
        return implode(':', ['active', $studentId, $batchId, $subjectId ?: 0]);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
