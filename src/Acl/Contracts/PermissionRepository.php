<?php

namespace Sztyup\Acl\Contracts;

use Sztyup\Acl\Node;
use Sztyup\Acl\NodeCollection;
use Sztyup\Acl\Permission;
use Sztyup\Acl\Role;

interface PermissionRepository
{
    /**
     * @return NodeCollection
     */
    public function getPermissions(): NodeCollection;

    /**
     * @return Node
     */
    public function getPermissionsAsTree(): Node;

    /**
     * @param string $name
     * @return Permission
     */
    public function getPermissionByName(string $name);

    /**
     * @param Role $role
     * @return NodeCollection
     */
    public function getPermissionsForRole(Role $role): NodeCollection;
}
