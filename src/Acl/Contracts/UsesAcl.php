<?php

namespace Sztyup\Acl\Contracts;

use Sztyup\Acl\Role;

interface UsesAcl
{
    /**
     * @return array|Role[]
     */
    public function getRoles(): array;
    public function hasRole($role, bool $all = false): bool;

    public function getPermissions(): array;
    public function hasPermission($permissions, bool $all = false): bool;
}
