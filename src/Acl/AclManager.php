<?php

namespace Sztyup\Acl;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Sztyup\Acl\Contracts\PermissionRepository;
use Sztyup\Acl\Contracts\PermissionsToRole;
use Sztyup\Acl\Contracts\RoleRepository;
use Sztyup\Acl\Contracts\RoleToUser;
use Sztyup\Acl\Contracts\UsesAcl;
use Sztyup\Acl\Exception\InvalidConfigurationException;

class AclManager
{
    const CACHE_KEY_MAP = '__permission_to_role_mapping';

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $user;

    /**
     * @var Node Tree of all available roles
     */
    protected $roleTree;

    /**
     * @var Node Tree of all available permissions
     */
    protected $permissionTree;

    /**
     * @var array Cached mapping of permissions to roles
     */
    protected $map;

    /**
     * @var Repository Cache implementation
     */
    protected $cache;

    /**
     * @var array Configuration
     */
    protected $config;

    /**
     * @var RoleToUser User supplied storage class
     */
    protected $roleToUser;

    /**
     * @var PermissionsToRole
     */
    protected $permissionsToRole;

    /**
     * @var PermissionRepository
     */
    protected $permissionRepository;

    /**
     * @var RoleRepository
     */
    protected $roleRepository;

    public function __construct(Guard $guard, Repository $cache, Container $container)
    {
        $this->user = $guard->user();
        $this->cache = $cache;
        $this->config = config('acl');

        $this->permissionRepository = $this->getClass('permission_repository', PermissionRepository::class, $container);
        $this->roleRepository = $this->getClass('role_repository', RoleRepository::class, $container);
        $this->permissionsToRole = $this->getClass('permission_to_role', PermissionsToRole::class, $container);
        $this->roleToUser = $this->getClass('role_to_user', RoleToUser::class, $container);

        $this->load();
    }

    private function load()
    {
        $this->parseRoles();
        $this->parsePermissions();
        $this->buildMap();
    }

    protected function getClass($config, $interface, $container)
    {
        $class = $this->config[$config];

        $reflection = new \ReflectionClass($class);
        if (!$reflection->isSubclassOf($interface)) {
            throw new InvalidConfigurationException($config);
        }

        return $container->make($class);
    }

    protected function parseRoles()
    {
        $this->roleTree = Node::buildTree(
            null,
            $this->roleRepository->getRoles(),
            $this->roleRepository
        )->getNode();
    }

    protected function parsePermissions()
    {
        $this->permissionTree = Node::buildTree(
            null,
            $this->permissionRepository->getPermissions(),
            $this->permissionRepository
        )->getNode();
    }

    protected function buildMap()
    {
        if ($this->cache->has(self::CACHE_KEY_MAP)) {
            $this->map = $this->cache->get(self::CACHE_KEY_MAP);
        } else {
            $this->map = $this->roleTree->mapWithKeys(function (Node $role) {
                if (!$role instanceof Role) {
                    return [];
                }
                return [
                    $role->getName() => $this->permissionsToRole->getPermissionsForRole($role)
                ];
            });

            $this->cache->forever(self::CACHE_KEY_MAP, $this->map);
        }
    }

    public function getPermissionsForUser(UsesAcl $user)
    {
        $permissions = new Collection();

        foreach ($user->getRoles() as $role) {
            $permissions = $permissions->merge(
                $this->permissionTree->getNodesByNames($this->map[$role])
            );
        }

        $permissions = $permissions->merge(
            $this->permissionTree->getNodesByDynamic($user)
        );

        return $permissions->map(function (Permission $permission) {
            return $permission->getName();
        })->toArray();
    }

    public function getRolesForUser(UsesAcl $user)
    {
        $roles = new Collection();

        $roles = $roles->merge(
            $this->roleToUser->getRolesForUser($user)
        );

        $roles = $roles->merge(
            $this->roleTree->getNodesByDynamic($user)
        );

        return $roles->map(function (Role $role) {
            return $role->getName();
        })->toArray();
    }

    public function getPermissions()
    {
        return $this->permissionTree->flatten();
    }

    public function getRoles()
    {
        return $this->roleTree->flatten();
    }

    public function clearCache()
    {
        $this->roleTree = null;
        $this->permissionTree = null;
        $this->map = null;

        $this->load();
    }
}
