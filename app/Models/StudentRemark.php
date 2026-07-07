<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRemark extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'teacher_id', 'created_by_id', 'remark_type', 'remark_date', 'title', 'remark', 'visible_to_parent'];
    protected $casts = ['visible_to_parent' => 'boolean'];
    protected $dates = ['remark_date', 'created_at', 'updated_at'];

    public function student() { return $this->belongsTo(Student::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_id'); }
}
