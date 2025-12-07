<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'unit_name',
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

     // A unit or subunit can have many items
     public function itemsAsMainUnit()
     {
         return $this->hasMany(Item::class, 'item_unit', 'id');
     }

     public function itemsAsSubUnit()
     {
         return $this->hasMany(Item::class, 'item_subunit', 'id');
     }
}
