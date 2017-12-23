<?php

namespace Sztyup\Acl\Contracts;

use Sztyup\Acl\Role;

interface PermissionToRole
{
    public function getPermissionForRole(Role $role): array;
}
