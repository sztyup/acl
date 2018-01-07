<?php

namespace Sztyup\Acl\Contracts;

interface StaticRoleRepository
{
    public function getRoles(): array;
}
