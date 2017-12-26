<?php

namespace Sztyup\Acl\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Sztyup\Acl\AclManager;
use Sztyup\Acl\Models\Role;

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
            $this->aclPermissions = $cache->rememberForever(
                '__acl_permission_user_' . $this->getAuthIdentifier(),
                function () {
                    return $this->aclManager->getPermissionsForUser($this);
                }
            );

            $this->aclRoles = $cache->rememberForever(
                '__acl_role_user_' . $this->getAuthIdentifier(),
                function () {
                    return $this->roles->merge(
                        $this->aclManager->getDynamicRolesForUser($this)
                    );
                }
            );
        } else {
            throw new \Exception('User object must implement Authenticable');
        }

        $this->aclManager = $aclManager;
    }

    public function hasPermission($permissions, bool $all = false): bool
    {
        return $this->hasElementsInCollection($this->aclPermissions, Arr::wrap($permissions), $all);
    }

    public function hasRole($roles, bool $all = false): bool
    {
        return $this->hasElementsInCollection($this->aclRoles, Arr::wrap($roles), $all);
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
