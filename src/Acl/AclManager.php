<?php

namespace Sztyup\Acl;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Sztyup\Acl\Contracts\HasAclContract;
use Sztyup\Acl\Contracts\Permissions;
use Sztyup\Acl\Contracts\PermissionToRole;
use Sztyup\Acl\Contracts\RoleToUser;
use Sztyup\Acl\Exception\InvalidConfigurationException;
use Sztyup\Acl\Traits\TreeHelpers;
use Tree\Node\NodeInterface;

class AclManager
{
    use TreeHelpers;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|HasAclContract
     */
    protected $user;

    /**
     * @var Collection
     */
    protected $permissions;

    /**
     * @var Collection
     */
    protected $roles;

    /**
     * @var bool
     */
    protected $inherits;

    public function __construct(Guard $guard, Container $container)
    {
        $this->user = $guard->user();
        $this->inherits = config('acl.inheritance');

        $this->checkSubclassOf($class = config('acl.permissions'), Permissions::class);
        $permissions = $container->make($class);

        $this->checkSubclassOf($class = config('acl.permission_to_role_class'), PermissionToRole::class);
        $permissionsToRole = $container->make($class);

        $this->checkSubclassOf($class = config('acl.role_to_user_class'), RoleToUser::class);
        $roleToUser = $container->make($class);

        $this->parseRoles($roleToUser);
        $this->parsePermissions($permissions, $permissionsToRole);

        dd($this->roles, $this->permissions);
    }

    protected function checkSubclassOf($class, $interface)
    {
        $reflection = new \ReflectionClass($class);
        if (!$reflection->isSubclassOf($interface)) {
            throw new InvalidConfigurationException($class);
        }
    }

    protected function parseRoles(RoleToUser $roleToUser)
    {
        $this->roles = $roleToUser->getRolesForUser($this->user);
    }

    protected function parsePermissions(Permissions $permissions, PermissionToRole $permissionToRole)
    {
        $this->permissions = new Collection();

        $permissionTree = $this->addPermissionsToTree(null, $permissions->getPermissions());

        foreach ($this->roles as $role) {
            $this->permissions->merge(
                $this->getPermissionsFromTree($permissionTree, $permissionToRole->getPermissionForRole($role))
            );
        }

        $this->permissions->merge(
            $this->getDynamicPermissions($permissionTree)
        );

        $this->permissions->mapWithKeys(function (Permission $permission) {
            return [
                $permission->getName() => $permission->getTitle()
            ];
        });
    }

    /**
     * Returns all permission node (and theyre accendants if inheritance is enabled) who are listed in the values array
     * @param NodeInterface $tree The tree to traverse
     * @param array $values The permissions who are needed
     * @return array
     */
    protected function getPermissionsFromTree(NodeInterface $tree, array $values)
    {
        return $this->filterTree($tree, function (Permission $permission) use ($values) {
            return in_array($permission->getName(), $values);
        });
    }

    protected function getDynamicPermissions(NodeInterface $tree)
    {
        return $this->filterTree($tree, function (Permission $permission) {
            return $permission->apply($this->user);
        });
    }

    public function hasRole($role)
    {
        return $this->roles->has($role);
    }

    public function hasPermission($name)
    {
        return $this->permissions->has($name);
    }
}
