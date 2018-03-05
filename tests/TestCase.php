<?php

namespace Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as Base;
use Sztyup\Acl\AclServiceProvider;

class TestCase extends Base
{
    protected function getPackageProviders($app)
    {
        return [AclServiceProvider::class];
    }

    protected function setUp()
    {
        $this->refreshApplication();

        /** @var Repository $config */
        $config = $this->app->make('config');

        $config->set('acl.role_repository', TestRoleRepository::class);
        $config->set('acl.permission_repository', TestPermissionRepository::class);

        /** @var Router $router */
        $router = $this->app->make('router');

        $router->group([
            'middleware' => ['web', 'acl']
        ], function ($router) {
            $router->get('asd', [
                'can' => 'perm1',
                'uses' => function () {
                    return response('asd');
                }
            ]);
        });
    }
}
