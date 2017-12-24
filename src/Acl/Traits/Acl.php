<?php

namespace Sztyup\Acl\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Sztyup\Acl\AclManager;

trait Acl
{
    /** @var  AclManager */
    private $aclManager;

    private $roles;

    private $permissions;

    private function initAcl()
    {
        if (!app()->bound(AclManager::class)) {
            throw new \Exception('Trying to get ACL without being initialized');
        }
        $this->aclManager = app(AclManager::class);
    }

    public function getPermissions(): array
    {
        $this->initAcl();
        if ($this->permissions == null) {
            $this->permissions = $this->aclManager->getPermissionsForUser($this);
        }

        return $this->permissions;
    }

    public function getRoles(): array
    {
        $this->initAcl();
        if ($this->roles == null) {
            $this->roles = $this->aclManager->getRolesForUser($this);
        }

        return $this->roles;
    }

    public function hasPermission($permissions, bool $all = false): bool
    {
        return $this->hasElementsInCollection($this->getPermissions(), Arr::wrap($permissions), $all);
    }

    public function hasRole($roles, bool $all = false): bool
    {
        return $this->hasElementsInCollection($this->getRoles(), Arr::wrap($roles), $all);
    }

    private function hasElementsInCollection($collection, array $items, $all)
    {
        $items = new Collection($items);

        $result = $items->intersect($collection);

        if ($all && $result->count() == $items->count()) {
            return true;
        }

        if (!$all && $result->isNotEmpty()) {
            return true;
        }

        return false;
    }
}
