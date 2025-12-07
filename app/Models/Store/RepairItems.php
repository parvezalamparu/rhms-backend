<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RepairItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'return_id',
        'date',
        'sent_by',
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

    public function details()
    {
        return $this->hasMany(RepairItemDetails::class, 'return_id', 'return_id');
    }
}
