<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnquiryFollowUp extends Model
{
    use HasFactory;

    public $table = 'enquiry_follow_ups';

    protected $dates = [
        'follow_up_date',
        'next_follow_up_date',
        'created_at',
        'updated_at',
    ];

        protected $casts = [
            'follow_up_date' => 'date',
            'next_follow_up_date' => 'date',
        ];

    protected $fillable = [
        'enquiry_id',
        'followed_by_id',
        'follow_up_date',
        'follow_up_type',
        'response',
        'next_follow_up_date',
        'status',
        'remarks',
        'created_at',
        'updated_at',
    ];

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class, 'enquiry_id');
    }

    public function followedBy()
    {
        return $this->belongsTo(User::class, 'followed_by_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}