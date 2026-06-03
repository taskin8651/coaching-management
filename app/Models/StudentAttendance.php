<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAttendance extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'student_attendances';

    protected $dates = [
        'attendance_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'student_id',
        'batch_id',
        'subject_id',
        'biometric_device_log_id',
        'attendance_date',
        'scheduled_start_time',
        'scheduled_end_time',
        'actual_in_time',
        'actual_out_time',
        'status',
        'source',
        'remarks',
        'unique_key',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $attendance) {
            $date = $attendance->attendance_date instanceof \DateTimeInterface
                ? $attendance->attendance_date->format('Y-m-d')
                : $attendance->attendance_date;

            $attendance->unique_key = $attendance->deleted_at
                ? 'deleted:' . ($attendance->id ?: uniqid('', true))
                : self::makeUniqueKey($attendance->student_id, $attendance->batch_id, $attendance->subject_id, $date);
        });

        static::deleting(function (self $attendance) {
            if (! $attendance->isForceDeleting()) {
                $attendance->unique_key = 'deleted:' . $attendance->id;
                $attendance->saveQuietly();
            }
        });
    }

    public static function makeUniqueKey($studentId, $batchId, $subjectId, $attendanceDate): string
    {
        return implode(':', ['active', $studentId, $batchId, $subjectId ?: 0, $attendanceDate]);
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

    public function biometricLog()
    {
        return $this->belongsTo(BiometricDeviceLog::class, 'biometric_device_log_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
