<?php

namespace Sztyup\Acl\Contracts;

interface PermissionRepository
{
    public function getPermissions(): array;
}
