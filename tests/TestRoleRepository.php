<?php

namespace Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Sztyup\Acl\Contracts\RoleRepository;
use Sztyup\Acl\Node;
use Sztyup\Acl\NodeCollection;
use Sztyup\Acl\Role;

class TestRoleRepository implements RoleRepository
{
    /** @var array|Collection[] */
    private $userToRole = [];

    /**
     * @var Collection|Role[]
     */
    private $roles;

    public function __construct()
    {
        $this->roles = Collection::make([
            new Role('foo'),
            new Role('bar', 'Bar', 'Bar role', function (Authenticatable $user) {
                return $user->getAuthIdentifier() == 1;
            })
        ]);
    }

    /**
     * @return NodeCollection|Role[]
     */
    public function getRoles(): NodeCollection
    {
        return NodeCollection::make($this->roles);
    }

    /**
     * @param Authenticatable $user
     * @return NodeCollection
     */
    public function getRolesForUser(Authenticatable $user): NodeCollection
    {
        return NodeCollection::make(
            $this->userToRole[ $user->getAuthIdentifier() ] ?? []
        )->withInherited();
    }

    /**
     * @param string $name
     * @return null|Role
     * @throws \Exception
     */
    public function getRoleByName(string $name)
    {
        foreach ($this->getRoles() as $role) {
            if ($role->getName() === $name) {
                return $role;
            }
        }

        throw new \Exception('Nonexisting role: ' . $name);
    }

    /**
     * @param string|Role $role
     * @param Authenticatable $user
     * @throws \Exception
     */
    public function addRoleToUser($role, Authenticatable $user)
    {
        if (is_string($role)) {
            $role = $this->getRoleByName($role);
        }

        if (!isset($this->userToRole[$user->getAuthIdentifier()])) {
            $this->userToRole[$user->getAuthIdentifier()] = new NodeCollection();
        }

        $this->userToRole[$user->getAuthIdentifier()][] = $role;
    }

    /**
     * @param string|Role $role
     * @param Authenticatable $user
     * @throws \Exception
     */
    public function removeRoleFromUser($role, Authenticatable $user)
    {
        if (is_string($role)) {
            $role = $this->getRoleByName($role);
        }

        if (isset($this->userToRole[$user->getAuthIdentifier()])) {
            $key = $this->userToRole[$user->getAuthIdentifier()]->search($role);

            unset($this->userToRole[$user->getAuthIdentifier()][$key]);
        }
    }

    public function getRolesAsTree(): Node
    {
        $root = new Node('dummy');

        foreach ($this->roles as $role) {
            $root->addChildren($role);
        }

        return $root;
    }

    /**
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        $this->roles[] = $role;
    }

    /**
     * @param Role|string $role
     * @throws \Exception
     */
    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = $this->getRoleByName($role);
        }

        $key = $this->roles->search($role);

        if ($key !== false) {
            $this->roles->forget($key);
        }
    }
}
