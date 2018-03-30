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

    const CACHE_MINUTES = 60 * 24;

    /** @var PermissionRepository */
    protected $permissionRepository;

    /** @var RoleRepository */
    protected $roleRepository;

    /** @var Authenticatable */
    protected $user;

    /** @var NodeCollection */
    protected $roles;

    /** @var NodeCollection */
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

        $this->permissions = new NodeCollection();
        $this->roles = new NodeCollection();

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

        $this->map = $this->cache->remember(
            self::CACHE_KEY_MAP,
            self::CACHE_MINUTES,
            function () use ($roleTree) {
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

        $this->permissions = NodeCollection::make();

        $this->roles = $this->roleRepository->getRolesForUser($user);

        if ($this->config['dynamic_roles']) {
            $this->roles = $this->roles->merge(
                $this->roleRepository->getRolesAsTree()->getNodesByDynamic($user)
            );
        }

        $this->roles->setInheritance(
            $this->config['role_inheritance']
        );

        foreach ($this->roles->withInherited() as $role) {
            $this->permissions = $this->permissions->merge($this->permissionRepository->getPermissionsForRole($role));
        }

        if ($this->config['dynamic_permissions']) {
            $this->permissions = $this->permissions->merge(
                $this->permissionRepository->getPermissionsAsTree()->getNodesByDynamic($user)
            );
        }

        $this->permissions->setInheritance(
            $this->config['permission_inheritance']
        );

        return $this;
    }

    protected function checkInitialized()
    {
        if (is_null($this->user)) {
            throw new AuthenticationException('AclManager not initialized');
        }
    }

    public function getPermissionsForUser(): Collection
    {
        $this->checkInitialized();

        return $this->permissions;
    }

    public function getRolesForUser(): Collection
    {
        $this->checkInitialized();

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
        $this->checkInitialized();

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
        $this->checkInitialized();

        return $this->hasElementsInCollection($this->roles, Arr::wrap($roles), $all);
    }

    private function hasElementsInCollection(NodeCollection $collection, array $items, $all)
    {
        $items = new Collection($items);

        $collection = $collection->withInherited()->map->getName();

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
        $this->cache->forget(self::CACHE_KEY_ROLES);

        $this->init();
    }

    public function getRedirectUrl()
    {
        return $this->config['login_page'];
    }
}
