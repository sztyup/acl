<?php

namespace Sztyup\Acl;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

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

        $this->app->singleton(AclManager::class);
    }

    protected function registerBlade()
    {
        $blade = $this->app->make(BladeCompiler::class);

        // role
        $blade->directive('role', function ($expression) {
            return "<?php if (Auth::check() && Auth::user()->getAcl()->hasRole({$expression})): ?>";
        });

        $blade->directive('endrole', function () {
            return "<?php endif; ?>";
        });

        // permission
        $blade->directive('permission', function ($expression) {
            return "<?php if (Auth::check() && Auth::user()->getAcl()->can({$expression})): ?>";
        });

        $blade->directive('endpermission', function () {
            return "<?php endif; ?>";
        });
    }
}
