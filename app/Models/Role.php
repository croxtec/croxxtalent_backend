<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name',
        'description',
        'is_custom',
        'is_owner',
        'is_admin',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'archived_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_permissions', 'permissions',
    ];

    /**
     * Get all of the owning roleable models.
     */
    public function roleable()
    {
        return $this->morphTo();
    }

    // Model relationships

    public function permissions()
    {
        return $this->belongsToMany('App\Models\Permission', 'permission_role', 'role_id', 'permission_id');
    }
    
    public function createdByUser()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'id');
    }

    // Set Custom Model Attributes

    public function getPermissionsAttribute()
    {
        return $this->permissions()->get();
    }

    public function getTotalPermissionsAttribute()
    {
        return $this->permissions()->count();
    }

    public function hasPermission($permission_name)
    {
        $permissionCount = $this->permissions()->wherePivot("name", $permission_name)->count();
        if ($permission) {
            return true;
        }
        return false;
    }
}
