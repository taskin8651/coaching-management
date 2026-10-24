<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayTarget extends Model
{
    protected $fillable = ['holiday_id', 'target_type', 'target_id'];

    public function holiday()
    {
        return $this->belongsTo(Holiday::class);
    }
}
