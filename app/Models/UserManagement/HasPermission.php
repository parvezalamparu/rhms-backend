<?php

namespace App\Models\UserManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasPermission extends Model
{
    use HasFactory;

    // Explicitly define table name
    protected $table = 'has_permissions';

    // Define fillable columns
    protected $fillable = [
        'role_id',
        'permission_id',
    ];

    // Disable timestamps if not used in the table
    public $timestamps = false;

    
    // Get the role associated with this permission link
    
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    
    // Get the permission associated with this role link
    
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
}
