<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    public $table = 'attendance_settings';

    protected $fillable = [
        'student_grace_minutes',
        'teacher_grace_minutes',
        'auto_absent_after_minutes',
    ];

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'student_grace_minutes' => 10,
            'teacher_grace_minutes' => 10,
            'auto_absent_after_minutes' => 30,
        ]);
    }
}
