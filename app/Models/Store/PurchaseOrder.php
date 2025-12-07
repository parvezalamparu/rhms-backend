<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'po_no',
        'vendor',
        'generated_by',
        'date',
        'status'
    ];


    protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {

        // Generate UUID if empty
        if (empty($model->uuid)) {
            $model->uuid = Str::uuid()->toString();
        }

        // Auto Increment PO Number
        if (empty($model->po_no)) {

            $today = now()->setTimezone('Asia/Kolkata')->format('ymd'); // yymmdd format
            $prefix = "POR-$today-";
    
            // Get last PO created today
            $last = self::where('po_no', 'like', "$prefix%")
                ->orderBy('po_no', 'desc')
                ->lockForUpdate()
                ->first();
    
            if ($last) {
                // Get last 3 number digits
                $lastNumber = (int) substr($last->po_no, -3);
                $next = $lastNumber + 1;
            } else {
                $next = 1;
            }
    
            $model->po_no = $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
        }

    });
}
    // }

    public function details()
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'po_no', 'po_no');
    }
}

