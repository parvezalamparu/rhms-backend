<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RequisitionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'requisition_no',
        'note',
        'date',
        'generated_by',
        'department',
        'item_id',
        'req_qty',
        'unit_qty',
        'unit',
        'sub_unit_qty',
        'sub_unit',
        'total',
        'relation',
        'issued_unit',
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

    public function requisition()
    {
        return $this->belongsTo(Requisition::class, 'requisition_no', 'requisition_no');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\Store\Item::class, 'item_id');
    }
}