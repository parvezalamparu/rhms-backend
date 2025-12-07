<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ItemStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'store_name',
        'is_active',
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


    // In a store can have many items
    public function items()
    {
        return $this->hasMany(Item::class, 'item_store', 'id');
    }
}
