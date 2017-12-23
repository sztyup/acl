<?php

namespace Sztyup\Acl;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Sztyup\Acl\Contracts\HasAclContract;
use Sztyup\Acl\Contracts\PermissionToRole;
use Sztyup\Acl\Contracts\RoleToUser;
use Sztyup\Acl\Exception\InvalidConfigurationException;
use Tree\Node\NodeInterface;

class AclManager
{
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

    public function __construct(Guard $guard, Container $container)
    {
        $this->user = $guard->user();

        $this->checkSubclassOf($class = config('acl.permissions_class'), Permissions::class);
        $permissions = $container->make($class);

        $this->checkSubclassOf($class = config('acl.permission_to_role_class'), PermissionToRole::class);
        $permissionsToRole = $container->make($class);

        $this->checkSubclassOf($class = config('acl.role_to_user_class'), RoleToUser::class);
        $roleToUser = $container->make($class);

        $this->parseRoles($roleToUser);
        $this->parsePermissions($permissions, $permissionsToRole);
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

        foreach ($this->roles as $role) {
            $perms = $permissionToRole->getPermissionForRole($role);
        }

        $this->permissions->merge($this->getPermissionsByUser($permissions));
    }

    protected function getPermissionsByUser(Permissions $permissions)
    {
        $result = [];

        $permissions->traverse(function (NodeInterface $permission) use ($result) {
            if ($permission->getValue()->apply($this->user)) {
                $result[] = $permission->getAncestorsAndSelf();
            }
        });

        return $result;
    }

    public function getPermissionsForRole(RoleToUser $roleToUser)
    {
        $permissions = $roleToUser->getRolesForUser($this->user);

        $result = [];
        foreach ($permissions as $permission) {
            $result[] = $permission;
        }

        return $result;
    }

    public function hasRole($role)
    {
        return $this->roles->has($role);
    }
}
