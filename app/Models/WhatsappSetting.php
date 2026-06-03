<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappSetting extends Model
{
    use HasFactory;

    public $table = 'whatsapp_settings';

    protected $fillable = [
        'api_provider',
        'api_base_url',
        'api_key',
        'sender_number',
        'biometric_device_token',
        'status',
    ];
}
