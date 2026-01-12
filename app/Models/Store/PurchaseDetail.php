<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'purchase_no',
        'purchase_date',
        'generated_by',
        'vendor',
        'payment_terms',
        'note',
        'item_id',
        'batch_no',
        'exp_date',
        'qty',
        'unit',
        'sub_unit_qty',
        'sub_unit',
        'test_qty',
        'rate_per_unit',
        'discount_rs',
        'discount_percent',
        'mrp_per_unit',
        'cgst_percent',
        'sgst_percent',
        'igst_percent',
        'total_gst_amount',
        'amount',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'exp_date' => 'date',
        'qty' => 'integer',
        'test_qty' => 'integer',
        'rate_per_unit' => 'decimal:2',
        'discount_rs' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'mrp_per_unit' => 'decimal:2',
        'cgst_percent' => 'decimal:2',
        'sgst_percent' => 'decimal:2',
        'igst_percent' => 'decimal:2',
        'total_gst_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }

            // Ensure nullable numeric values are stored as 0 instead of NULL
            foreach ([
                'test_qty', 'rate_per_unit', 'discount_rs', 'discount_percent',
                'mrp_per_unit', 'cgst_percent', 'sgst_percent', 'igst_percent',
                'total_gst_amount', 'amount'
            ] as $field) {

                if ($model->$field === null) {
                    $model->$field = 0;
                }
            }
        });
    }

    public function purchase()
    {
        return $this->belongsTo(PurchaseList::class, 'purchase_no', 'purchase_no');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\Store\Item::class, 'item_id');
    }
}
