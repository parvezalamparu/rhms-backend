<?php

namespace App\Models\UserManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\UserManagement\User;
use App\Models\UserManagement\Permission;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name',
        'slug',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            $role->slug = Str::slug($role->role_name);
        });
    }

    // One role has many users
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    // One role can have many permissions (many-to-many)
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'has_permissions', 'role_id', 'permission_id');
    }
}
