<?php

namespace Sztyup\Acl\Contracts;

interface UsesAcl
{
    /**
     * @return array
     */
    public function getRoles(): array;
    public function hasRole($role, bool $all = false): bool;

    public function getPermissions(): array;
    public function hasPermission($permissions, bool $all = false): bool;
}
