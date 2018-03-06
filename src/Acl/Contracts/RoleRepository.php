<?php

namespace Sztyup\Acl\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Sztyup\Acl\Node;
use Sztyup\Acl\Role;

interface RoleRepository
{
    /**
     * @return Collection
     */
    public function getRoles(): Collection;

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
     * @return Collection
     */
    public function getRolesForUser(Authenticatable $user): Collection;

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
