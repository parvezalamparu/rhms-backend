<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReturnedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'returned_id',
        'date',
        'department',
        'returned_by',
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

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         $model->returned_id = 'RET-' . str_pad(self::max('id') + 1, 5, '0', STR_PAD_LEFT);
    //     });
    // }

    public function details()
    {
        return $this->hasMany(ReturnedItemDetail::class, 'returned_id', 'returned_id');
    }
}
