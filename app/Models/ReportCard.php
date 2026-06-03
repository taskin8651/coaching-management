<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['student_id', 'exam_id', 'batch_id', 'total_marks', 'marks_obtained', 'percentage', 'grade', 'rank', 'remarks', 'published_to_parent', 'published_at'];
    protected $casts = ['published_to_parent' => 'boolean', 'total_marks' => 'decimal:2', 'marks_obtained' => 'decimal:2', 'percentage' => 'decimal:2'];
    protected $dates = ['published_at', 'created_at', 'updated_at', 'deleted_at'];

    public function student() { return $this->belongsTo(Student::class); }
    public function exam() { return $this->belongsTo(Exam::class); }
    public function batch() { return $this->belongsTo(Batch::class); }
}
