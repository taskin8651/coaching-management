<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class ExternalContact extends Model
{
    public $table = 'external_contacts';

    protected $fillable = [
        'name',
        'gender',
        'date_of_birth',
        'standard',
        'school_name',
        'mobile',
        'whatsapp_number',
        'email',
        'guardian_name',
        'guardian_mobile',
        'guardian_email',
        'city',
        'area',
        'remarks',
        'created_by_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function enrollments()
    {
        return $this->hasMany(EventEnrollment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
