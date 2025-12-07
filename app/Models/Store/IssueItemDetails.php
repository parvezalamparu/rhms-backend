<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IssueItemDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'issue_no',
        'requisition_no',
        'issue_to',
        'generated_by',
        'issue_date',
        'item_id',
        'batch_no',
        'exp_date',
        'unit_qty',
        'unit',
        'sub_unit_qty',
        'sub_unit',
        'qty',
        'amount',
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

    public function issue()
    {
        return $this->belongsTo(IssueItems::class, 'issue_no', 'issue_no');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\Store\Item::class, 'item_id');
    }
}
