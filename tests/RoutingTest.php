<?php

namespace Tests;

use Illuminate\Auth\AuthenticationException;
use Sztyup\Acl\AclManager;

class RoutingTest extends TestCase
{
    public function testAuthenticationRedirectAnd403()
    {
        $user = new FakeUser(1, 'Sztyup');

        /** @var AclManager $manager */
        $manager = $this->app->make(AclManager::class);

        $manager->setUser($user);

        $this
            ->get('/asd')
            ->assertRedirect('/login')
        ;

        $this->actingAs($user)
            ->get('/asd')
            ->assertStatus(403)
        ;

        $manager->getRoleRepository()->addRoleToUser('foo', $user);

        $this->actingAs($user)
            ->get('/asd')
            ->assertSuccessful()
        ;
    }

    /**
     * @throws AuthenticationException
     */
    public function testUnauthenticated()
    {
        /** @var AclManager $manager */
        $manager = $this->app->make(AclManager::class);

        $this->expectException(AuthenticationException::class);

        $manager->hasPermission('frontend');
    }

    public function testExplicitAuth()
    {
        $this
            ->get('/foo')
            ->assertRedirect('/logintester')
        ;

        $user = new FakeUser(1, 'Sztyup');

        $this->actingAs($user)
            ->get('/foo')
            ->assertSuccessful()
            ->assertSee('foo')
        ;
    }
}
