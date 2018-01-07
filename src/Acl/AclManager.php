<?php

namespace Sztyup\Acl;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Sztyup\Acl\Contracts\HasAcl;
use Sztyup\Acl\Contracts\PermissionRepository;
use Sztyup\Acl\Contracts\PermissionToRoleRepository;
use Sztyup\Acl\Contracts\RoleRepository;
use Sztyup\Acl\Exception\InvalidConfigurationException;
use Sztyup\Acl\Role as RoleNode;

class AclManager
{
    const CACHE_KEY_MAP = '__acl_permission_to_role_mapping';
    const CACHE_KEY_ROLES = '__acl_role_tree';
    const CACHE_KEY_PERMISSIONS = '__acl_permission_tree';

    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    protected $user;

    /** @var PermissionRepository */
    protected $permissionRepository;

    /**
     * @var Node Tree of all available permissions
     */
    protected $permissionTree;

    /** @var  RoleRepository */
    protected $roleRepository;

    /** @var  Node */
    protected $roleTree;

    /**
     * @var array Cached mapping of permissions to roles
     */
    protected $map;

    /**
     * @var Repository Cache implementation
     */
    protected $cache;

    /** @var Collection */
    protected $staticRoles;

    /** @var PermissionToRoleRepository */
    protected $permissionToRoleRepository;

    /**
     * @var array Configuration
     */
    protected $config;

    public function __construct(Guard $guard, Repository $cache, Container $container)
    {
        $this->cache = $cache;
        $this->config = config('acl');

        $this->permissionRepository = $this->getClass('permission_repository', PermissionRepository::class, $container);
        $this->permissionToRoleRepository = $this->getClass(
            'permission_to_role_repository',
            PermissionToRoleRepository::class,
            $container
        );
        $this->roleRepository = $this->getClass('role_repository', RoleRepository::class, $container);
        $this->staticRoles = new Collection($this->roleRepository->getRoles());

        $this->parseRoles();
        $this->parsePermissions();
        $this->buildMap();
    }


    protected function getClass($config, $interface, Container $container)
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
        $this->roleTree =  Role::buildTree(
            null,
            $this->roleRepository->getRoles()
        )->getNode();
    }

    protected function parsePermissions()
    {
        $this->permissionTree = Permission::buildTree(
            null,
            $this->permissionRepository->getPermissions()
        )->getNode();
    }

    protected function buildMap()
    {
        $this->map = $this->cache->rememberForever(self::CACHE_KEY_MAP, function () {
            return $this->staticRoles->toBase()
                ->mapWithKeys(function ($role) {
                    return [
                        $role->getName() => $this->permissionToRoleRepository->getPermissionsForRole($role)
                    ];
                })
                ->merge(
                    $this->roleTree->mapWithKeys(function (RoleNode $role) {
                        return [
                            $role->getName() => $role->getPermissions()
                        ];
                    })
                )->toArray();
        });
    }

    public function getPermissionsForUser(HasAcl $user)
    {
        $permissions = new Collection();

        foreach ($user->getRoles() as $role) {
            $permissions = $permissions->merge(
                $this->permissionTree->getNodesByNames($this->map[$role->getName()])
            );
        }

        $permissions = $permissions->merge(
            $this->permissionTree->getNodesByDynamic($user)
        );

        return $permissions;
    }

    public function getDynamicRolesForUser(HasAcl $user): Collection
    {
        return $this->roleTree->getNodesByDynamic($user)->toBase();
    }

    public function getPermissions()
    {
        return $this->permissionTree->flatten();
    }

    public function getRoles()
    {
        return $this->roleTree->flatten()->merge($this->staticRoles);
    }

    public function clearCache()
    {
        $this->cache->forget(self::CACHE_KEY_MAP);

        $this->buildMap();
    }
}
