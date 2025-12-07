<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApprovedReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'approved_id',
        'returned_id',
        'date',
        'department',
        'returned_by',
        'approved_by',
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


    protected $primaryKey = 'approved_id';
    public $incrementing = true;

    public function details()
    {
        return $this->hasMany(ApprovedReturnDetail::class, 'approved_id', 'approved_id');
    }
}
