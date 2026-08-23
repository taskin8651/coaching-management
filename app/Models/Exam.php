<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    public $table = 'exams';

    protected $dates = [
        'exam_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'exam_date'     => 'date',
        'total_marks'   => 'decimal:2',
        'passing_marks' => 'decimal:2',
    ];

    protected $fillable = [
        'branch_id',
        'course_id',
        'batch_id',
        'subject_id',
        'title',
        'exam_type',
        'exam_date',
        'start_time',
        'end_time',
        'total_marks',
        'passing_marks',
        'status',
        'remarks',
        'created_at',
        'updated_at',
    ];

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

    public function results()
    {
        return $this->hasMany(ExamResult::class, 'exam_id');
    }

    public function selfAssessments()
    {
        return $this->hasMany(ExamSelfAssessment::class, 'exam_id');
    }

    /**
     * Scheduled/Completed here reflects whether the exam's date & time have passed —
     * independent of the stored `status` column, which the results-entry flow flips to
     * "completed" only once marks are saved. Manual cancellation still wins over both.
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        if ($this->exam_date) {
            $examEndsAt = Carbon::parse($this->exam_date->format('Y-m-d').' '.($this->end_time ?: '23:59:59'));

            if (now()->greaterThanOrEqualTo($examEndsAt)) {
                return 'completed';
            }

            return 'scheduled';
        }

        return $this->status ?: 'scheduled';
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}