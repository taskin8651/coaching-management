<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FeeAccount extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $table = 'fee_accounts';

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'type',
        'bank_name',
        'account_number',
        'ifsc_code',
        'upi_id',
        'gst_applicable',
        'gst_number',
        'receipt_address',
        'status',
    ];

    protected $casts = [
        'gst_applicable' => 'boolean',
    ];

    protected $appends = [
        'qr_code_url',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('fee_account_qr')->singleFile();
    }

    public function getQrCodeUrlAttribute()
    {
        $file = $this->getFirstMedia('fee_account_qr');

        return $file ? $file->getUrl() : null;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function structureInstallments()
    {
        return $this->hasMany(FeeStructureInstallment::class);
    }

    public function feeInstallments()
    {
        return $this->hasMany(FeeInstallment::class);
    }

    public function feePayments()
    {
        return $this->hasMany(FeePayment::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
