<?php


namespace Elliot9\laravelPermissionHelper\Http\Exceptions;


class UnauthorizedException extends \Symfony\Component\HttpKernel\Exception\HttpException
{
    public static function forPermissions(array $names): self
    {
        $message = '權限不足，需要';
        $message .= implode(', ', $names);
        $exception = new static(403, $message, null, []);
        return $exception;
    }

    public static function notLoggedIn():self
    {
        $message = '尚未登入';
        $exception = new static(403, $message, null, []);
        return $exception;
    }

    public static function forRoles(array $names = null): self
    {
        $message = '無';
        if($names!= null)
        {
            $message .= implode(', ', $names);
        }
        $message .= '身分';
        $exception = new static(403, $message, null, []);
        return $exception;
    }
}
