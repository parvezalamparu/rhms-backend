<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'item_name',
        'item_code',
        'item_type',
        'item_category',
        'item_subcategory',
        'low_level',
        'high_level',
        'company',
        'stored',
        'hsn_or_sac_no',
        'item_unit',
        'item_subunit',
        'unit_to_subunit',
        'rack_no',
        'shelf_no',
        'image',
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


    // Each item belongs to a type
    public function itemType()
    {
        return $this->belongsTo(ItemType::class, 'item_type', 'id');
    }

    // Each item belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class, 'item_category', 'id');
    }

    // Each item belongs to a company
    public function companyDetails()
    {
        return $this->belongsTo(Company::class, 'company', 'id');
    }

    // Each item belongs to a unit
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'item_unit', 'id');
    }

    // Each item belongs to a subunit
    public function subunit()
    {
        return $this->belongsTo(Unit::class, 'item_unit', 'id');
    }

    // Each item belongs to a store department
    public function storeDepartment()
    {
        return $this->belongsTo(StoreDepartment::class, 'stored', 'id');
    }

    // Each item can have many issue details
    public function issueItemDetails()
    {
        return $this->hasMany(IssueItemDetail::class, 'item_id', 'id');
    }

    // Each item can have many purchase details
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'item_id', 'id');
    }

    // Each item can have many return details
    public function returnItemDetails()
    {
        return $this->hasMany(ReturnItemDetail::class, 'item_id', 'id');
    }

    // Each item can have many repair details
    public function repairItemDetails()
    {
        return $this->hasMany(RepairItemDetail::class, 'item_id', 'id');
    }

    // Each item can have many discard records
    public function discardItems()
    {
        return $this->hasMany(DiscardItem::class, 'item_id', 'id');
    }

}

