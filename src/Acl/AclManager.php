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

    public function __construct(Guard $guard, Repository $cache, Container $container)
    {
        $this->user = $guard->user();
        $this->cache = $cache;
        $this->config = config('acl');

        $permissionRepository = $this->getClass('permission_repository', PermissionRepository::class, $container);
        $roleRepository = $this->getClass('role_repository', RoleRepository::class, $container);
        $permissionsToRole = $this->getClass('permission_to_role', PermissionsToRole::class, $container);
        $this->roleToUser = $this->getClass('role_to_user', RoleToUser::class, $container);

        $this->parseRoles($roleRepository);
        $this->parsePermissions($permissionRepository);
        $this->buildMap($permissionsToRole);
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

    protected function parseRoles(RoleRepository $roleRepository)
    {
        $this->roleTree = Node::buildTree(
            null,
            $roleRepository->getRoles(),
            $roleRepository
        );
    }

    protected function parsePermissions(PermissionRepository $permissionRepository)
    {
        $this->permissionTree = Node::buildTree(
            null,
            $permissionRepository->getPermissions(),
            $permissionRepository
        );
    }

    protected function buildMap(PermissionsToRole $permissionsToRole)
    {
        if ($this->cache->has(self::CACHE_KEY_MAP)) {
            $this->map = $this->cache->get(self::CACHE_KEY_MAP);
        } else {
            $this->map = $this->roleTree->mapWithKeys(function (Role $role) use ($permissionsToRole) {
                return [
                    $role->getName() => $permissionsToRole->getPermissionsForRole($role)
                ];
            });
            $this->cache->forever(self::CACHE_KEY_MAP, $this->map);
        }
    }

    public function getPermissionsForUser(UsesAcl $user)
    {
        $permissions = new Collection();

        foreach ($user->getRoles() as $role) {
            $permissions->merge(
                $this->getNodesFromTree($this->permissionTree, $this->map[$role->getName()])
            );
        }

        $permissions->merge(
            $this->getDynamicNodes($this->permissionTree, $user)
        );

        return $permissions->map(function (Permission $permission) {
            return $permission->getName();
        })->toArray();
    }

    public function getRolesForUser(UsesAcl $user)
    {
        $roles = new Collection();

        $roles->merge($this->roleToUser->getRolesForUser($user));

        $roles->merge($this->getDynamicNodes($this->roleTree, $user));

        return $roles->map(function (Role $role) {
            return $role->getName();
        })->toArray();
    }

    /**
     * Returns all permission node (and theyre accendants if inheritance is enabled) who are listed in the values array
     * @param Node $tree The tree to operate on
     * @param array $values The permissions who are needed
     * @return array
     */
    protected function getNodesFromTree(Node $tree, array $values)
    {
        return $tree->filterTree(function (Node $node) use ($values) {
            return in_array($node->getName(), $values);
        });
    }

    /**
     * Gives back all nodes applicable to the given user
     *
     * @param Node $tree The tree to operate on
     * @param UsesAcl $user The user requesting nodes
     * @return array The applicable nodes
     */
    protected function getDynamicNodes(Node $tree, UsesAcl $user)
    {
        return $tree->filterTree(function (Node $node) use ($user) {
            return $node->apply($user);
        });
    }
}
