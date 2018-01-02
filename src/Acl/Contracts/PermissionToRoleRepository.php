<?php

namespace Sztyup\Acl\Contracts;

interface PermissionToRoleRepository
{
    public function getPermissionsForRole(Role $role): array;
}
