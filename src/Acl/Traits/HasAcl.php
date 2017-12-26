<?php

namespace Sztyup\Acl\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Sztyup\Acl\AclManager;
use Sztyup\Acl\Models\Role;
use Sztyup\Acl\Contracts\Role as RoleContract;

trait HasAcl
{
    /** @var AclManager */
    protected $aclManager;

    /** @var Collection */
    protected $aclPermissions;

    /** @var Collection */
    protected $aclRoles;

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function initAcl(Repository $cache, AclManager $aclManager)
    {
        if ($this instanceof Authenticatable) {
            $this->aclManager = $aclManager;

            $this->aclRoles = $this->roles->merge(
                $this->aclManager->getDynamicRolesForUser($this)
            );

            $this->aclPermissions = $this->aclManager->getPermissionsForUser($this);
        } else {
            throw new \Exception('User object must implement Authenticable');
        }

        $this->aclManager = $aclManager;
    }

    /**
     * @return Collection|RoleContract[]
     */
    public function getRoles(): Collection
    {
        return $this->aclRoles;
    }

    public function getPermissions(): Collection
    {
        return $this->aclPermissions;
    }

    public function hasPermission($permissions, bool $all = false): bool
    {
        return $this->hasElementsInCollection($this->aclPermissions, Arr::wrap($permissions), $all);
    }

    public function hasRole($roles, bool $all = false): bool
    {
        return $this->hasElementsInCollection($this->aclRoles, Arr::wrap($roles), $all);
    }

    private function hasElementsInCollection(Collection $collection, array $items, $all)
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
