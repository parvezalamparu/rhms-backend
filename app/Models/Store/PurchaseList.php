<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PurchaseList extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'invoice_no',
        'requisition_no',
        'purchase_type',
        'purchase_no',
        'po_no',
        'vendor',
        'generated_by',
        'date',
        'payment_terms',
        'discount_type',
        'discount_value',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            // Generate UUID 
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }

            // Auto-generate purchase_no only if not provided 
            if (empty($model->purchase_no)) {

                $today = now()->setTimezone('Asia/Kolkata')->format('ymd'); // YYMMDD
                $prefix = "PUR-$today-";

                // Lock row to avoid race condition when generating sequence
                $last = self::where('purchase_no', 'like', "$prefix%")
                    ->orderBy('purchase_no', 'desc')
                    ->lockForUpdate()
                    ->first();

                if ($last) {
                    $lastNumber = intval(substr($last->purchase_no, -3));
                    $next = $lastNumber + 1;
                } else {
                    $next = 1;
                }

                $model->purchase_no = $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_no', 'purchase_no');
    }
}
