<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApprovedReturnDetail extends Model
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
        'note',
        'item_name',
        'batch_no',
        'qty',
        'reason',
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
}
