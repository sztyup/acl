<?php

namespace Sztyup\Acl\Contracts;

interface RoleRepository extends NodeRepository
{
    public function getRoles(): array;
}
