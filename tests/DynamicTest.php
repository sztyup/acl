<?php

namespace Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Sztyup\Acl\AclManager;
use Sztyup\Acl\Role;

class DynamicTest extends TestCase
{
    /**
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function testRole()
    {
        $user1 = new FakeUser(1, '1');
        $user2 = new FakeUser(2, '2');

        /** @var AclManager $manager */
        $manager = $this->app->make(AclManager::class);

        $manager->getRoleRepository()->addRole(
            new Role('dynamic1', 'Dynamic #1', 'A dynamic role', function (Authenticatable $user) {
                return $user->getAuthIdentifier() == 1;
            })
        );

        $manager->getRoleRepository()->addRole(
            new Role('dynamic2', 'Dynamic #2', 'Another dynamic role', function (Authenticatable $user) {
                return $user->getAuthIdentifier() == 2;
            })
        );

        $manager->setUser($user1);

        $this->assertTrue($manager->hasRole('dynamic1'));
        $this->assertFalse($manager->hasRole('dynamic2'));

        $manager->setUser($user2);

        $this->assertFalse($manager->hasRole('dynamic1'));
        $this->assertTrue($manager->hasRole('dynamic2'));
    }
}
