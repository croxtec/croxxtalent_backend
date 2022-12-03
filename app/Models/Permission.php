<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name',
        'module',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'archived_at' => 'datetime',
    ];

    public function setModuleAttribute($value)
    {
        $this->attributes['module'] = strtolower($value);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', 'permission_role', 'permission_id', 'role_id');
    }

    public static function defaultPermissions() 
    {
        return [
            // ...self::generateModulePermissionList('roles'),
            // ...self::generateModulePermissionList('countries'),
            // ...self::generateModulePermissionList('states'),
            // ...self::generateModulePermissionList('users'),
            // ...self::generateModulePermissionList('employers'),
            // ...self::generateModulePermissionList('talents'),
            // ...self::generateModulePermissionList('affiliates'),
            // ...self::generateModulePermissionList('admins'),
            // ...self::generateModulePermissionList('cvs'),
            // ...self::generateModulePermissionList('campaigns'),
        ];
    }

    private static function generateModulePermissionList($module)
    {
        $name_singular = Str::singular($module);
        $name_plural = Str::plural($module);
        $module = Str::slug(Str::plural($module));
        return [
            [
                'module' =>$module, 
                'name' => Str::slug("view-all-{$name_singular}"),
                'description' => "View list of {$name_plural}." 
            ],
            [ 
                'module' => $module, 
                'name' => Str::slug("view-{$name_singular}"), 
                'description' => "View {$name_singular} information." 
            ],
            [ 
                'module' => $module, 
                'name' => Str::slug("create-{$name_singular}"), 
                'description' => "Create {$name_singular}." 
            ],
            [ 
                'module' => $module, 
                'name' => Str::slug("update-{$name_singular}"), 
                'description' => "Update {$name_singular}." 
            ],
            [ 
                'module' => $module, 
                'name' => Str::slug("delete-{$name_singular}"), 
                'description' => "Delete {$name_singular}." 
            ],
        ];
    }
}
