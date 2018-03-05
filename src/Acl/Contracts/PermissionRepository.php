<?php

namespace Sztyup\Acl\Contracts;

use Illuminate\Support\Collection;
use Sztyup\Acl\Node;
use Sztyup\Acl\Permission;
use Sztyup\Acl\Role;

interface PermissionRepository
{
    /**
     * @return Collection
     */
    public function getPermissions(): Collection;

    /**
     * @return Node
     */
    public function getPermissionsAsTree(): Node;

    /**
     * @param Role $role
     * @return Collection
     */
    public function getPermissionsForRole(Role $role): Collection;

    /**
     * @param Permission|string $permission
     * @param Role $role
     */
    public function addPermissionToRole($permission, Role $role);

    /**
     * @param Permission|string $permission
     * @param Role $role
     */
    public function removePermissionFromRole($permission, Role $role);
}
