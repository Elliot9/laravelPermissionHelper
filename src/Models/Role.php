<?php


namespace Elliot9\laravelPermissionHelper\Models;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = [];

    public function permissions()
    {
        return $this->hasManyThrough('Elliot9\laravelPermissionHelper\Models\Permission',
            'Elliot9\laravelPermissionHelper\Models\RoleHasPermission'
        ,'role_id','id','id','permission_id');
    }

    public function RoleHasPermissions()
    {
        return $this->hasMany('Elliot9\laravelPermissionHelper\Models\RoleHasPermission');
    }

}
