<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RepairItemDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'return_id',
        'item_id',
        'batch_no',
        'qty',
        'unit_qty',
        'unit',
        'sub_unit_qty',
        'sub_unit',
        'repair_amount',
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

    public function repair()
    {
        return $this->belongsTo(RepairItems::class, 'return_id', 'return_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
