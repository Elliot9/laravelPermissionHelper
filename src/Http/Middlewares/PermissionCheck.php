<?php

namespace Elliot9\laravelPermissionHelper\Http\Middlewares;

use App\User;
use Closure;
use Elliot9\laravelPermissionHelper\Http\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Auth;
use Elliot9\laravelPermissionHelper\PermissionHelperFacade;
class PermissionCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $names)
    {
//        Auth::login(User::first());

        if (Auth::guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $names = is_array($names)
            ? $names
            : explode('|', $names);

        $PermissionHelper = PermissionHelperFacade::SetInstance(Auth::user());
        if( ! $PermissionHelper->HasPermission($names))
        {
            throw UnauthorizedException::forPermissions($names);
        }

        return $next($request);
    }
}
