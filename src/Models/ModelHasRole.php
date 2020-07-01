<?php


namespace Elliot9\laravelPermissionHelper\Models;
use Illuminate\Database\Eloquent\Model;

class ModelHasRole extends Model
{
    protected $guarded = [];

    public function Roles()
    {
        return $this->hasMany('Elliot9\laravelPermissionHelper\Models\Role','role_id');
    }
}
