<?php

namespace Sztyup\Acl;

interface UsesAcl
{
    public function hasPermission($permissions, $all): bool;

    public function hasRole($role, $all): bool;
}
