<?php

namespace Sztyup\Acl\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface RoleToUser
{
    /**
     * @param Authenticatable|UsesAcl $user
     * @return array An array of Role objects
     */
    public function getRolesForUser(UsesAcl $user): array;
}
