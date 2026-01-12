<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DiscardItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'return_id',
        'item_id',
        'batch_no',
        'returned_department',
        'return_by',
        'qty',
        'unit',
        'sub_unit_qty',
        'sub_unit',
        'discarded_by',
        'discarded_reason',
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

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
