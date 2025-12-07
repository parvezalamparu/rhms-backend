<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Requisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'requisition_no',
        'generated_by',
        'department',
        'date',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate UUID if not set
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }

            // Generate requisition_no if not set
            if (empty($model->requisition_no)) {
                $today = now()->setTimezone('Asia/Kolkata')->format('ymd'); // yymmdd format
                $prefix = "REQ-$today-";
            
                // Get last record for today's format
                $last = Requisition::where('requisition_no', 'like', "$prefix%")
                    ->orderBy('requisition_no', 'desc')
                    ->lockForUpdate()
                    ->first();
            
                if ($last) {
                    // Extract last 3 digits and increment
                    $lastNumber = intval(substr($last->requisition_no, -3));
                    $next = $lastNumber + 1;
                } else {
                    $next = 1;
                }
            
                // Format next number with 3 digits padding
                $model->requisition_no = $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
            }
        });

        // Cascade delete related details when requisition is deleted
        static::deleting(function ($model) {
            $model->details()->delete();
        });
    }

    public function details()
    {
        return $this->hasMany(RequisitionDetail::class, 'requisition_no', 'requisition_no');
    }
}