<?php

namespace Sztyup\Acl\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Sztyup\Acl\Node;
use Sztyup\Acl\NodeCollection;
use Sztyup\Acl\Role;

interface RoleRepository
{
    /**
     * @return NodeCollection
     */
    public function getRoles(): NodeCollection;

    /**
     * @return Node
     */
    public function getRolesAsTree(): Node;

    /**
     * @param string $name
     * @return Role
     */
    public function getRoleByName(string $name);

    /**
     * @param Authenticatable $user
     * @return NodeCollection
     */
    public function getRolesForUser(Authenticatable $user): NodeCollection;

    /**
     * @param $role string|Role
     * @param Authenticatable $user
     */
    public function addRoleToUser($role, Authenticatable $user);

    /**
     * @param $role string|Role
     * @param Authenticatable $user
     */
    public function removeRoleFromUser($role, Authenticatable $user);

    /**
     * @param Role|string $role
     */
    public function addRole(Role $role);

    /**
     * @param Role|string $role
     */
    public function removeRole($role);
}
