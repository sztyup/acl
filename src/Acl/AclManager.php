<?php

namespace Sztyup\Acl;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Config\Repository as Config;
use Sztyup\Acl\Contracts\PermissionRepository;
use Sztyup\Acl\Contracts\RoleRepository;

class AclManager
{
    const CACHE_KEY_MAP = '__acl_permission_to_role_mapping';
    const CACHE_KEY_ROLES = '__acl_role_tree';
    const CACHE_KEY_PERMISSIONS = '__acl_permission_tree';

    const CACHE_MINUTES = 60 * 24;

    /** @var PermissionRepository */
    protected $permissionRepository;

    /** @var RoleRepository */
    protected $roleRepository;

    /** @var Authenticatable */
    protected $user;

    /** @var Collection */
    protected $roles;

    /** @var Collection */
    protected $permissions;

    /** @var array Cached mapping of permissions to roles */
    protected $map;

    /** @var Cache Cache implementation */
    protected $cache;

    /** @var array Configuration */
    protected $config;

    /**
     * AclManager constructor.
     * @param Guard $guard
     * @param Cache $cache
     * @param Container $container
     * @param Config $config
     */
    public function __construct(Guard $guard, Cache $cache, Container $container, Config $config)
    {
        $this->cache = $cache;
        $this->config = $config->get('acl');

        $this->permissionRepository = $container->make($this->config['permission_repository']);
        $this->roleRepository = $container->make($this->config['role_repository']);

        $this->permissions = new Collection();
        $this->roles = new Collection();

        $this->init();
    }

    protected function init()
    {
        /** @var Role $roleTree */
        $roleTree = $this->cache->remember(
            self::CACHE_KEY_ROLES,
            self::CACHE_MINUTES,
            function () {
                return $this->roleRepository->getRolesAsTree();
            }
        );

        /** @var Permission $permissionTree */
        $permissionTree = $this->cache->remember(
            self::CACHE_KEY_PERMISSIONS,
            self::CACHE_MINUTES,
            function () {
                return $this->permissionRepository->getPermissionsAsTree();
            }
        );

        $this->map = $this->cache->remember(
            self::CACHE_KEY_MAP,
            self::CACHE_MINUTES,
            function () use ($roleTree, $permissionTree) {
                return $roleTree->mapWithKeys(function (Role $role) {
                    return [
                        $role->getName() => $this->permissionRepository->getPermissionsForRole($role)
                    ];
                });
            }
        );
    }

    /**
     * @param Authenticatable $user
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;

        $this->roles = $this->roleRepository->getRolesForUser($user);
        foreach ($this->roles as $role) {
            $this->permissions = $this->permissions->merge($this->permissionRepository->getPermissionsForRole($role));
        }

        return $this;
    }

    public function getPermissionsForUser(): Collection
    {
        return $this->permissions;
    }

    public function getRolesForUser(): Collection
    {
        return $this->roles;
    }

    /**
     * @param $permissions
     * @param bool $all
     *
     * @return bool
     *
     * @throws AuthenticationException
     */
    public function hasPermission($permissions, bool $all = false): bool
    {
        if (!$this->user) {
            throw new AuthenticationException();
        }

        return $this->hasElementsInCollection($this->permissions, Arr::wrap($permissions), $all);
    }

    /**
     * @param $roles
     * @param bool $all
     *
     * @return bool
     *
     * @throws AuthenticationException
     */
    public function hasRole($roles, bool $all = false): bool
    {
        if (!$this->user) {
            throw new AuthenticationException();
        }

        return $this->hasElementsInCollection($this->roles, Arr::wrap($roles), $all);
    }

    private function hasElementsInCollection(Collection $collection, array $items, $all)
    {
        $items = new Collection($items);

        $collection = $collection->map->getName();

        $result = $items->intersect($collection);

        if ($all && $result->count() == $items->count()) {
            return true;
        }

        if (!$all && $result->isNotEmpty()) {
            return true;
        }

        return false;
    }

    public function getRoleRepository()
    {
        return $this->roleRepository;
    }

    public function getPermissionRepository()
    {
        return $this->permissionRepository;
    }

    public function clearCache()
    {
        $this->cache->forget(self::CACHE_KEY_MAP);
        $this->cache->forget(self::CACHE_KEY_PERMISSIONS);
        $this->cache->forget(self::CACHE_KEY_ROLES);

        $this->init();
    }

    public function getRedirectUrl()
    {
        return $this->config['login_page'];
    }
}
