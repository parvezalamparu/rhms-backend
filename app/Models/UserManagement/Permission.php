<?php

namespace App\Models\UserManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'permission_name',
        'slug',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($permission) {
            $permission->slug = Str::slug($permission->permission_name);
        });
    }

    // Each permission can belong to many roles
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'has_permissions', 'permission_id', 'role_id');
    }
}
