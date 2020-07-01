<?php

namespace Elliot9\laravelPermissionHelper;

use Elliot9\laravelPermissionHelper\Http\Middlewares\PermissionCheck;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;


class PermissionHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php', 'permission');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            if (! class_exists('CreatePermissionsTable')) {
                $this->publishes([
                    __DIR__.DIRECTORY_SEPARATOR .'database'.DIRECTORY_SEPARATOR .'migrations'.DIRECTORY_SEPARATOR.'create_permissions_table.php.stub' => database_path('migrations'. DIRECTORY_SEPARATOR . date('Y_m_d_His', time()) . '_create_permissions_table.php'),
                ], 'migrations');
            }
        }

        $this->registerModelBindings();
        $this->registerMiddleware();
        $this->registerBladeExtensions();
    }

    /**
     * 註冊 Model Binding
     */
    private function registerModelBindings()
    {
        $config = $this->app->config['permission.PermissionSetting.types'];
        if (! $config) {
            return;
        }

        $collects = collect($config)->transform(function ($item,$key){
            return \App::make($item);
        });

        $this->app->singleton('PermissionHelper', function($app) use ($collects){
            return new PermissionHelper($collects);
        });
    }

    /**
     * 註冊 Middleware
     */
    private function registerMiddleware()
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('PermissionCheck', PermissionCheck::class);
    }


    /**
     * 註冊 Blade
     */
    private function registerBladeExtensions()
    {
        Blade::directive('HasPermission', function ($args) {
            return "<?php if(PermissionHelper::SetInstance(Auth::user())->HasPermission($args)): ?>";
        });

        Blade::directive('HasRole', function ($args) {
            return "<?php if(PermissionHelper::SetInstance(Auth::user())->HasRole($args)): ?>";
        });

        Blade::directive('endHas', function ($args) {
            return "<?php endif; ?>";
        });

    }
}
