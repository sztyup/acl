<?php

namespace Sztyup\Acl\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Sztyup\Acl\Role;

interface RoleToUser
{
    /**
     * @param Authenticatable $user
     * @return Role[] An array of Role objects
     */
    public function getRolesForUser(Authenticatable $user): array;
}
