<?php


namespace Elliot9\laravelPermissionHelper;
use Illuminate\Support\Facades\Facade;

class PermissionHelperFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'PermissionHelper';
    }


}
