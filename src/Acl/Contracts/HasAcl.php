<?php

namespace Sztyup\Acl\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Sztyup\Acl\AclManager;

interface HasAcl extends Authenticatable
{
    public function initAcl(Repository $cache, AclManager $aclManager);

    public function hasRole($role): bool;
    public function hasPermission($permission): bool;

    public function getRoles(): Collection;
    public function getPermissions(): Collection;
}
