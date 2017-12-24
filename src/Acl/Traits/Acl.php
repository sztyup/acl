<?php

namespace Sztyup\Acl\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Sztyup\Acl\AclManager;

trait Acl
{
    /** @var  AclManager */
    protected static $aclManager;

    private $roles;

    private $permissions;

    protected static function bootAcl()
    {
        self::$aclManager = app(AclManager::class);
    }

    public function getPermissions(): array
    {
        if ($this->permissions == null) {
            $this->permissions = self::$aclManager->getPermissionsForUser($this);
        }

        return $this->permissions;
    }

    public function getRoles(): array
    {
        if ($this->roles == null) {
            $this->roles = self::$aclManager->getRolesForUser($this);
        }

        return $this->roles;
    }

    public function hasPermission($permissions, bool $all = false)
    {
        $this->hasElementsInCollection($this->getPermissions(), Arr::wrap($permissions), $all);
    }

    public function hasRole($roles, bool $all = false)
    {
        $this->hasElementsInCollection($this->getRoles(), Arr::wrap($roles), $all);
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
