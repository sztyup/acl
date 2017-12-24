<?php

namespace Sztyup\Acl\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Sztyup\Acl\Permission;

interface RoleToUser
{
    /**
     * @param Authenticatable|UsesAcl $user
     * @return array An array of Role objects
     */
    public function getRolesForUser(UsesAcl $user): array;

    public function addRoleToUser(UsesAcl $user, Permission $permission);
    public function removeRoleFromUser(UsesAcl $user, Permission $permission);
}
