<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErpAlertLog extends Model
{
    protected $fillable = [
        'branch_id',
        'module_name',
        'alert_type',
        'title',
        'message',
        'status',
        'payload',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];
}
