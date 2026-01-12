<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReturnedItem extends Model
{
    protected $table = 'returned_items';

    protected $fillable = [
        'uuid',
        'returned_id',
        'date',
        'department',
        'returned_by',
        'note',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Boot to auto generate uuid and returned_id
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            // return id
            if (empty($model->returned_id)) {

                $today = now()->setTimezone('Asia/Kolkata')->format('ymd'); // yymmdd
                $prefix = "RET-$today-";
                $last = self::where('returned_id', 'like', "$prefix%")
                    ->orderBy('returned_id', 'desc')
                    ->lockForUpdate()
                    ->first();
    
                if ($last) {
                    $lastNumber = intval(substr($last->returned_id, -3));
                    $next = $lastNumber + 1;
                } else {
                    $next = 1;
                }
    
                $model->returned_id = $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relationship to details
    public function details()
    {
        return $this->hasMany(ReturnedItemDetail::class, 'returned_id', 'returned_id');
    }
}
