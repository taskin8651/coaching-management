<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiometricDeviceLog extends Model
{
    use HasFactory;

    public $table = 'biometric_device_logs';

    protected $casts = [
        'punch_time' => 'datetime',
        'processed_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    protected $fillable = [
        'biometric_user_id',
        'user_type',
        'punch_time',
        'punch_type',
        'device_id',
        'raw_payload',
        'processed_status',
        'processing_message',
        'processed_at',
    ];
}
