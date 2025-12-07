<?php

namespace App\Models\UserManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class Users extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'uuid',
        'employee_id',
        'role_id',
        'salutation',
        'username',
        'email',
        'father_name',
        'mother_name',
        'gender',
        'marital_status',
        'dob',
        'doj',
        'phone_number',
        'emg_number',
        'user_photo',
        'current_address',
        'permanent_address',
        'qualification',
        'experience',
        'specialization',
        'note',
        'pan_number',
        'identification_name',
        'identification_number',
        'password',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'dob' => 'date',
        'doj' => 'date',
        'marital_status' => 'boolean',
    ];

    // user has one role ---mandatory

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class,'role_id','id');
    }
}
