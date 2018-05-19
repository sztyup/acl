<?php

namespace Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as Base;
use Sztyup\Acl\AclServiceProvider;

class TestCase extends Base
{
    protected function getPackageProviders($app)
    {
        return [AclServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        /** @var Repository $config */
        $config = $app->make('config');

        $config->set('acl.role_repository', TestRoleRepository::class);
        $config->set('acl.permission_repository', TestPermissionRepository::class);
        $config->set('acl.role_inheritance', true);
    }

    protected function setUp()
    {
        $this->refreshApplication();

        /** @var Router $router */
        $router = $this->app->make('router');

        $router->get('login', function () {
            return new RedirectResponse('/login');
        })->name('login');

        $router->group([
            'middleware' => ['web', 'acl']
        ], function ($router) {
            $router->get('asd', [
                'can' => 'admin-foo',
                'uses' => function () {
                    return response('asd');
                }
            ]);

            $router->get('foo', [
                'auth' => ['target' => '/logintester'],
                'uses' => function () {
                    return response('foo');
                }
            ]);
        });
    }
}
