<?php

namespace Sztyup\Acl\Contracts;

interface PermissionToRole
{
    public function getPermissionForRole($role): array;
}
