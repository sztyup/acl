<?php

namespace Sztyup\Acl;

interface UsesAcl
{
    /**
     * @return array|Role[]
     */
    public function getRoles(): array;
    public function hasRole($role, $all): bool;

    public function getPermissions(): array;
    public function hasPermission($permissions, $all): bool;
}
