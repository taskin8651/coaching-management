<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappNotificationLog extends Model
{
    use HasFactory;

    public $table = 'whatsapp_notification_logs';

    protected $dates = [
        'sent_at',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'student_id',
        'guardian_number',
        'module_name',
        'message',
        'status',
        'response',
        'sent_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
