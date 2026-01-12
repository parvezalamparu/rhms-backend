<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReturnedItemDetail extends Model
{
    protected $table = 'returned_item_details';

    protected $fillable = [
        'uuid',
        'returned_id',
        'item_id',
        'batch_no',
        'qty',
        'unit_qty',
        'unit',
        'sub_unit_qty',
        'sub_unit',
        'reason',
        'note',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function returnedItem()
    {
        return $this->belongsTo(ReturnedItem::class, 'returned_id', 'returned_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
