<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReturnedItemDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'returned_id',
        'date',
        'department',
        'returned_by',
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

    public function returnedItem()
    {
        return $this->belongsTo(ReturnedItem::class, 'returned_id', 'returned_id');
    }
}
