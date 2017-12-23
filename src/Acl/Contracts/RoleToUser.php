<?php

namespace Sztyup\Acl\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface RoleToUser
{
    public function getRolesForUser(Authenticatable $user): array;
}
