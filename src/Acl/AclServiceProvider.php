<?php

namespace Sztyup\Acl;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Sztyup\Acl\Contracts\HasAcl;

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

        $model = config('acl.user_model');
        $model::retrieved(function (HasAcl $user) {
            $user->initAcl(
                $this->app->make(Repository::class),
                $this->app->make(AclManager::class)
            );
        });
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
    }

    protected function registerBlade()
    {
        $blade = $this->app->make(BladeCompiler::class);

        // role
        $blade->directive('role', function ($expression) {
            return "<?php if (Auth::check() && Auth::user()->hasRole({$expression})): ?>";
        });

        $blade->directive('endrole', function () {
            return "<?php endif; ?>";
        });

        // permission
        $blade->directive('permission', function ($expression) {
            return "<?php if (Auth::check() && Auth::user()->hasPermission({$expression})): ?>";
        });

        $blade->directive('endpermission', function () {
            return "<?php endif; ?>";
        });
    }
}
