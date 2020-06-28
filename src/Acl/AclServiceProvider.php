<?php

namespace Sztyup\Acl;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Sztyup\Acl\Middleware\Acl;

class AclServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/acl.php' => config_path('acl.php'),
        ], 'config');

        if (!$this->app->runningInConsole()) {
            $this->registerBlade();
        }

        /** @var Router $router */
        $router = $this->app->make('router');

        $router->aliasMiddleware('acl', Acl::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/acl.php',
            'acl'
        );

        $this->app->singleton(AclManager::class);

        $this->app->alias(AclManager::class, 'acl');
    }

    protected function registerBlade()
    {
        $blade = $this->app->make(BladeCompiler::class);
        // role
        $blade->directive('role', function ($expression) {
            return "<?php if (\$__env->getContainer()['acl']->hasRole({$expression})): ?>";
        });

        $blade->directive('elserole', function () {
            return "<?php else: ?>";
        });

        $blade->directive('endrole', function () {
            return "<?php endif; ?>";
        });

        // permission
        $blade->directive('permission', function ($expression) {
            return "<?php if (\$__env->getContainer()['acl']->hasPermission({$expression})): ?>";
        });

        $blade->directive('elsepermission', function () {
            return "<?php else: ?>";
        });

        $blade->directive('endpermission', function () {
            return "<?php endif; ?>";
        });
    }
}
