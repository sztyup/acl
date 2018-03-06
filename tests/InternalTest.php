<?php

namespace Tests;

use Sztyup\Acl\AclManager;

class InternalTest extends TestCase
{
    public function testPermissions()
    {
        /** @var AclManager $manager */
        $manager = $this->app->make(AclManager::class);

        $this->assertSame([
                'admin',
                'admin-foo',
                'admin-foo-lol',
                'frontend',
                'frontend-foo',
                'frontend-foo-lol',
                'frontend-bar',
        ], $manager->getPermissionRepository()->getPermissions()->flatten()->map->getName()->toArray());
    }
}
