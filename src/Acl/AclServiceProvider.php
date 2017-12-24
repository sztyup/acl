<?php

namespace Sztyup\Acl;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

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

        $this->registerBlade();
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

        $this->app->singleton(AclManager::class, function (Container $container) {
            return new AclManager(
                $container->make(Guard::class),
                $container->make(Repository::class),
                $container
            );
        });
    }

    protected function registerBlade()
    {
        // role
        \Blade::directive('role', function ($expression) {
            return "<?php if (Auth::check() && Auth::user()->hasRole({$expression})): ?>";
        });

        \Blade::directive('endrole', function () {
            return "<?php endif; ?>";
        });

        // permission
        \Blade::directive('permission', function ($expression) {
            return "<?php if (Auth::check() && Auth::user()->can({$expression})): ?>";
        });

        \Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });
    }
}
