<?php

namespace App\Models;

use DateTimeInterface;
use Hash;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    public $table = 'users';

    protected $hidden = [
        'remember_token',
        'password',
    ];

    protected $dates = [
        'email_verified_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'branch_id',
        'biometric_id',
        'email_verified_at',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getIsAdminAttribute()
    {
        return $this->roles()->where('id', 1)->exists();
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        self::created(function (self $user) {
            $registrationRole = config('panel.registration_default_role');

            if ($registrationRole && ! $user->roles()->get()->contains($registrationRole)) {
                $user->roles()->attach($registrationRole);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Important Date Fix
    |--------------------------------------------------------------------------
    | getEmailVerifiedAtAttribute remove kiya gaya hai,
    | kyunki wo Carbon date ko string bana raha tha.
    | Us wajah se "Call to a member function format() on string" error aa raha tha.
    */

    public function setEmailVerifiedAtAttribute($value)
    {
        $this->attributes['email_verified_at'] = $value ?: null;
    }

    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] = app('hash')->needsRehash($input)
                ? Hash::make($input)
                : $input;
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Branch Manager Relations
    |--------------------------------------------------------------------------
    */

    public function managedBranch()
    {
        return $this->hasOne(Branch::class, 'manager_id');
    }

    public function managedBranches()
    {
        return $this->hasMany(Branch::class, 'manager_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Relations
    |--------------------------------------------------------------------------
    */

    public function teacherProfile()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    public function staffProfile()
    {
        return $this->hasOne(Staff::class, 'user_id');
    }

    public function studentProfile()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Old Alias Relations
    |--------------------------------------------------------------------------
    | Agar aapke purane code me teacher(), student() use hai,
    | to wo bhi work karega.
    */

    public function teacher()
    {
        return $this->teacherProfile();
    }

    public function staff()
    {
        return $this->staffProfile();
    }

    public function student()
    {
        return $this->studentProfile();
    }

    /*
    |--------------------------------------------------------------------------
    | Enquiry Relations
    |--------------------------------------------------------------------------
    */

    public function assignedEnquiries()
    {
        return $this->hasMany(Enquiry::class, 'assigned_to_id');
    }

    public function enquiryFollowUps()
    {
        return $this->hasMany(EnquiryFollowUp::class, 'followed_by_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Fee Relations
    |--------------------------------------------------------------------------
    */

    public function collectedFeePayments()
    {
        return $this->hasMany(FeePayment::class, 'collected_by_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Salary Relations
    |--------------------------------------------------------------------------
    */

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class, 'user_id');
    }

    public function paidSalaryPayments()
    {
        return $this->hasMany(SalaryPayment::class, 'paid_by_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Study Material Relations
    |--------------------------------------------------------------------------
    */

    public function uploadedStudyMaterials()
    {
        return $this->hasMany(StudyMaterial::class, 'uploaded_by_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Notice Relations
    |--------------------------------------------------------------------------
    */

    public function createdNotices()
    {
        return $this->hasMany(Notice::class, 'created_by_id');
    }

    public function staffAttendances()
    {
        return $this->hasMany(StaffAttendance::class, 'user_id');
    }
}
