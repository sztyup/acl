<?php

namespace Sztyup\Acl\Contracts;

use Sztyup\Acl\Permission;
use Sztyup\Acl\Role;

interface PermissionsToRole
{
    /**
     * @param Role $role
     * @return array An array of permission name string
     */
    public function getPermissionsForRole(Role $role): array;

    public function addPermissionToRole(Permission $permission, Role $role);
    public function removePermissionFromRole(Permission $permission, Role $role);
}
