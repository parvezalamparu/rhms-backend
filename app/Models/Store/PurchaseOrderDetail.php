<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'po_no',
        'vendor',
        'generated_by',
        'date',
        'note',
        'item_name',
        'unit_qty',
        'unit',
        'sub_unit_qty',
        'sub_unit',
        'status',
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

    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_no', 'po_no');
    }
}

