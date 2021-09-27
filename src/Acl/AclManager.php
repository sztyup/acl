<?php

namespace Sztyup\Acl;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Config\Repository as Config;
use Sztyup\Acl\Contracts\PermissionRepository;
use Sztyup\Acl\Contracts\RoleRepository;
use Sztyup\Acl\Exception\NotAuthorizedException;

class AclManager
{
    private const CACHE_KEY_MAP = '__acl_permission_to_role_mapping';
    private const CACHE_KEY_ROLES = '__acl_role_tree';

    private const CACHE_MINUTES = 60 * 24;

    /** @var PermissionRepository */
    protected $permissionRepository;

    /** @var RoleRepository */
    protected $roleRepository;

    /** @var Authenticatable */
    protected $user;

    /** @var NodeCollection */
    protected $roles;

    /** @var string[]|Collection */
    protected $roleNames;

    /** @var NodeCollection */
    protected $permissions;

    /** @var string[]|Collection */
    protected $permissionNames;

    /** @var array Cached mapping of permissions to roles */
    protected $map;

    /** @var Cache Cache implementation */
    protected $cache;

    /** @var array Configuration */
    protected $config;

    /** @var boolean */
    protected $booted;

    /**
     * AclManager constructor.
     * @param Cache $cache
     * @param Container $container
     * @param Config $config
     */
    public function __construct(Cache $cache, Container $container, Config $config)
    {
        $this->cache = $cache;
        $this->config = $config->get('acl');

        $this->permissionRepository = $container->make($this->config['permission_repository']);
        $this->roleRepository = $container->make($this->config['role_repository']);

        $this->permissions = new NodeCollection();
        $this->roles = new NodeCollection();

        $this->init();
    }

    protected function init(): void
    {
        /** @var Role $roleTree */
        $roleTree = $this->roleRepository->getRolesAsTree();

        $this->map = $roleTree->mapWithKeys(function (Role $role) {
            return [
                $role->getName() => $this->permissionRepository->getPermissionsForRole($role)
            ];
        })->toArray();
    }

    /**
     * @param Authenticatable $user
     * @return $this
     */
    public function setUser(?Authenticatable $user): self
    {
        $this->booted = true;

        $this->user = $user;

        $this->permissions = NodeCollection::make();

        if ($user) {
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
                $this->permissions = $this->permissions->merge($this->map[$role->getName()] ?? []);
            }

            if ($this->config['dynamic_permissions']) {
                $this->permissions = $this->permissions->merge(
                    $this->permissionRepository->getPermissionsAsTree()->getNodesByDynamic($user)
                );
            }

            $this->permissions->setInheritance(
                $this->config['permission_inheritance']
            );
        } else {
            $this->roles = NodeCollection::make();
        }

        $this->permissionNames = $this->permissions->withInherited()->map->getName();
        $this->roleNames = $this->roles->withInherited()->map->getName();

        return $this;
    }

    /**
     * This exception usally means that the AclManager is used too early in the laravel boot process
     * @throws AuthenticationException
     */
    protected function checkInitialized(): void
    {
        if (!$this->booted) {
            throw new AuthenticationException('AclManager not initialized');
        }
    }

    /**
     * @return Collection
     */
    public function getPermissionsForUser(): Collection
    {
        if (!$this->booted) {
            return Collection::make();
        }

        return $this->permissions;
    }

    /**
     * @return Collection
     */
    public function getRolesForUser(): Collection
    {
        if (!$this->booted) {
            return Collection::make();
        }

        return $this->roles;
    }

    /**
     * @param $permissions
     * @param bool $all
     *
     * @throws NotAuthorizedException
     */
    public function requirePermission($permissions, bool $all = false): void
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                $missingPermissions[] = $permission;
            }
        }

        if (!empty($missingPermissions)) {
            throw new NotAuthorizedException([], $missingPermissions);
        }
    }

    /**
     * @param $roles
     * @param bool $all
     *
     * @throws NotAuthorizedException
     */
    public function requireRoles($roles, bool $all = false): void
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                $missingRoles[] = $role;
            }
        }

        if (!empty($missingRoles)) {
            throw new NotAuthorizedException([], $missingRoles);
        }
    }

    /**
     * @param $permissions
     * @param bool $all
     *
     * @return bool
     */
    public function hasPermission($permissions, bool $all = false): bool
    {
        if (!$this->booted) {
            return false;
        }

        return $this->hasElementsInCollection($this->permissionNames, Arr::wrap($permissions), $all);
    }

    /**
     * @param $roles
     * @param bool $all
     *
     * @return bool
     */
    public function hasRole($roles, bool $all = false): bool
    {
        if (!$this->booted) {
            return false;
        }

        return $this->hasElementsInCollection($this->roleNames, Arr::wrap($roles), $all);
    }

    private function hasElementsInCollection(Collection $collection, array $items, $all): bool
    {
        $items = Collection::wrap($items);

        $result = $items->intersect($collection);

        if ($all && $result->count() === $items->count()) {
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

    public function clearCache(): void
    {
        $this->cache->forget(self::CACHE_KEY_MAP);
        $this->cache->forget(self::CACHE_KEY_ROLES);

        $this->init();
    }

    public function getRedirectUrl()
    {
        return $this->config['login_page'];
    }

    public function getMap(): array
    {
        return $this->map;
    }

    public function getUser(): ?Authenticatable
    {
        return $this->user;
    }

    public function requestUser(): Authenticatable
    {
        if ($this->user === null) {
            throw new AuthenticationException();
        }

        return $this->user;
    }
}
