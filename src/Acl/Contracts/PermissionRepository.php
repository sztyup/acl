<?php

namespace Sztyup\Acl\Contracts;

interface PermissionRepository extends NodeRepository
{
    public function getPermissions(): array;
}
