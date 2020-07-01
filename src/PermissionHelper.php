<?php

namespace Elliot9\laravelPermissionHelper;

use Elliot9\laravelPermissionHelper\Http\Exceptions\UnauthorizedException;
use Elliot9\laravelPermissionHelper\Models\ModelHasRole;
use Elliot9\laravelPermissionHelper\Models\Permission;
use Elliot9\laravelPermissionHelper\Models\Role;
use Elliot9\laravelPermissionHelper\Models\RoleHasPermission;
use Illuminate\Support\Collection;
use mysql_xdevapi\Exception;

class PermissionHelper
{
    private $collects;
    private $model;
    private $type;
    private $roles;

    public function __construct($collects)
    {
        $this->collects = $collects;
    }


    /**
     * 綁定 Model
     * @param $model
     * @return $this
     */
    public function SetInstance($model)
    {
        $this->model = $model;
        $this->type =  $this->collects->search(function($item){
           return class_basename($item) == class_basename($this->model);
        });

        return $this;
    }


    /**
     * 設定腳色
     * @param $name
     * @return $this|bool
     */
    public function SetRole($names)
    {
        if($this->Is_Enable())
        {
            $Roles = $this->CreateRole($names);
            $this->roles = $Roles;

            if(isset($this->model->id))
            {
                foreach ($Roles as $Role)
                {
                    ModelHasRole::firstOrCreate([
                        'model_type' => $this->type,
                        'model_id' => $this->model->id,
                        'role_id' => $Role->id
                    ]);
                }
            }

            return $this;
        }
        return false;
    }


    /**
     * 設定腳色權限
     * @param array $names
     * @return $this|bool
     */
    public function SetRolePermission($names)
    {
        if($this->roles)
        {
            $Permissions = $this->CreatePermission($names);
            foreach ($this->roles as $role)
            {
                foreach ($Permissions as $Permission)
                {
                    $role->RoleHasPermissions()->firstOrCreate([
                        'permission_id' => $Permission->id
                    ]);
                }
            }
            return $this;
        }
        return false;
    }


    /**
     * 獲取 此 Instance全部腳色
     * @return mixed Exception|Role
     */
    public function GetRole()
    {
        if($this->Is_Enable() && isset($this->model->id))
        {
            return Role::whereIn('id',ModelHasRole::where('model_type',$this->type)
                ->where('model_id',$this->model->id)
                ->get('role_id')->pluck('role_id'))->get();
        }
        throw UnauthorizedException::notLoggedIn();
    }


    /**
     * 獲取 此 Instance 所有權限 或 當前腳色權限
     * @return Permission
     */
    public function GetPermission()
    {
        $roles = $this->roles ? $this->roles : $this->GetRole();
        if($roles)
        {
            $collect = collect();
            foreach ($roles as $role)
            {
                $collect->push($role->permissions);
            }
            return $collect->flatten()->unique('id');
        }
        throw UnauthorizedException::forRoles($roles);
    }


    /**
     * 拔除 Model 腳色
     * @param $name
     * @return $this|bool
     */
    public function RemoveRole($names)
    {
        if($this->Is_Enable())
        {
            $Roles = $this->CreateRole($names);

            if($this->roles)
            {
                $this->roles = $this->roles->filter(function($item) use ($Roles){
                    return !in_array($item->id,$Roles->pluck('id')->toArray());
                });
            }

            if(isset($this->model->id))
            {
                ModelHasRole::where('model_type',$this->type)
                    ->where('model_id', $this->model->id)
                    ->whereIn('role_id', $Roles->pluck('id'))
                    ->delete();
            }
            return $this;
        }
        return false;
    }


    /**
     * 拔除腳色權限
     * @param array $names
     * @return $this|bool
     */
    public function RemoveRolePermission($names)
    {
        if($this->roles)
        {
            $Permissions = $this->CreatePermission($names);
            foreach ($this->roles as $role)
            {
                $role->RoleHasPermissions()->whereIn('permission_id',$Permissions->pluck('id'))->delete();
            }
            return $this;
        }
        return false;
    }


    /**
     * 新增 權限
     * @param $name
     * @return mixed
     */
    public function CreatePermission($names)
    {
        return $this->CreateToInstance(new Permission,$this->TransToArray($names));
    }


    /**
     * 新增 腳色
     * @param $name
     * @return mixed
     */
    public function CreateRole($names)
    {
        return $this->CreateToInstance(new Role,$this->TransToArray($names));
    }


    /**
     * 刪除腳色
     * @param $name
     * @return $this
     */
    public function DeleteRole($names)
    {
        if($this->roles)
        {
            $this->roles = $this->roles->filter(function($item) use ($names){
                return !in_array($item->id,Role::whereIn('name',$this->TransToArray($names))->pluck('id')->toArray());
            });
        }
        $this->DeleteToInstance(new Role, $this->TransToArray($names));
        return $this;
    }


    /**
     * 刪除權限
     * @param $name
     * @return $this
     */
    public function DeletePermission($names)
    {
        $this->DeleteToInstance(new Permission, $this->TransToArray($names));
        return $this;
    }


    /**
     * 檢查是否有權限
     * @param $name
     * @return bool
     */
    public function HasPermission($names):bool
    {
        try {
            $Permissions = $this->CreatePermission($names);
            return (array_intersect($Permissions->pluck('id')->all(),$this->GetPermission()->pluck('id')->all()) == $Permissions->pluck('id')->all());
        }
        catch (\Exception $exception)
        {
            return false;
        }

    }


    /**
     * 檢查是否有腳色
     * @param $names
     * @return bool
     */
    public function HasRole($names):bool
    {
        try{
            $Roles = $this->CreateRole($names);
            if(isset($this->model->id))
            {
                return (ModelHasRole::where('model_type',$this->type)
                        ->where('model_id',$this->model->id)
                        ->pluck('role_id')->all() == $Roles->pluck('id')->all());
            }
            return false;
        }
        catch (\Exception $exception)
        {
            return false;
        }
    }

    /**
     * 檢查合法
     * @return bool
     */
    private function Is_Enable():bool
    {
        if($this->type && $this->collects)
        {
            return true;
        }
        return false;
    }


    /** Develop 功能區 */

    /**
     * 將字串或陣列 輸出成陣列
     * @param $string
     * @param string $explode
     * @return array
     */
    private function TransToArray($string, $explode = '|'):array
    {
        return is_array($string) ? $string : explode($explode, $string);
    }


    /**
     * 將陣列轉換成 Instance
     * @param $Instance
     * @param array $array
     * @return Collection
     */
    private function CreateToInstance($Instance, array $array):Collection
    {
        return collect($array)
            ->transform(function($value) use ($Instance){
                return $Instance->firstOrCreate([
                    'name' => $value
                ]);
            });
    }


    /**
     * 將陣列於 Instance刪除
     * @param $Instance
     * @param array $array
     */
    private function DeleteToInstance($Instance, array $array):void
    {
        $Instance->whereIn('name',$array)->delete();
    }
}
