<?php

namespace Sztyup\Acl\Contracts;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Sztyup\Acl\AclManager;

interface HasAcl extends Authenticatable
{
    public function initAcl(Repository $cache, AclManager $aclManager);

    public function hasRole($role): bool;
    public function hasPermission($permission): bool;

    public function addRole($role);
    public function revokeRole($role);
}
