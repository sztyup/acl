<?php

namespace Tests;

use Sztyup\Acl\AclManager;

class MapTest extends TestCase
{
    /**
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function testInitial()
    {
        /** @var AclManager $manager */
        $manager = $this->app->make(AclManager::class);
        $user = new FakeUser(1, 'Sztyup');

        $manager->setUser($user);

        // Test no permissions
        $this->assertFalse($manager->hasPermission('admin'));
        $this->assertFalse($manager->hasPermission('frontend'));

        // Test unexisting permission
        $this->assertFalse($manager->hasPermission('aaaaaaa'));

        // Test no roles
        $this->assertFalse($manager->hasRole('foo'));
        $this->assertFalse($manager->hasRole('bar'));

        // Test unexisting role
        $this->assertFalse($manager->hasRole('bbbbbb'));
    }

    /**
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function testOneRole()
    {
        /** @var AclManager $manager */
        $manager = $this->app->make(AclManager::class);
        $user = new FakeUser(1, 'Sztyup');

        $manager->setUser($user);

        // Add new Role
        $manager->getRoleRepository()->addRoleToUser('foo', $user);

        // Refresh acl
        $manager->setUser($user);

        // Test new Role
        $this->assertTrue($manager->hasRole('foo'));

        // Test new permissions
        $this->assertTrue($manager->hasPermission('admin-foo'));

        // Test child permission not given
        $this->assertFalse($manager->hasPermission('admin-foo-lol'));

        // Test inherited permission given
        $this->assertTrue($manager->hasPermission('admin'));
    }
}
