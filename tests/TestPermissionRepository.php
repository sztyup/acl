<?php

namespace Tests;

use Illuminate\Support\Collection;
use Sztyup\Acl\Contracts\PermissionRepository;
use Sztyup\Acl\Node;
use Sztyup\Acl\NodeCollection;
use Sztyup\Acl\Permission;
use Sztyup\Acl\Role;

class TestPermissionRepository implements PermissionRepository
{
    /** @var array|Collection[] */
    private $permissionToRole = [
        'foo' => ['admin-foo']
    ];

    /**
     * TestPermissionRepository constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        foreach ($this->permissionToRole as $role => $permissions) {
            foreach ($permissions ?? [] as $key => $permission) {
                if (is_string($permission)) {
                    $this->permissionToRole[$role][$key] = $this->getPermissionByName($permission);
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getter()
    {
        return [
            'admin' => [
                'Hozzáfér az admin felülethez',
                [
                    'admin-foo' => [
                        'Has access to foo admin',
                        [
                            'admin-foo-lol' => [
                                'Has access to foo/lol administration'
                            ],
                        ]
                    ],
                ]
            ],
            'frontend' => [
                'Weboldalakhoz fér hozzá',
                [
                    'frontend-foo' => [
                        'A foo weboldalhoz fér hozzá',
                        [
                            'frontend-foo-lol' => [
                                'A foo lol parancshoz fér hozzá'
                            ]
                        ]
                    ],
                    'frontend-bar' => [
                        'A bar weboldalhoz fér hozzzá'
                    ]
                ]
            ]
        ];
    }

    /**
     * @return NodeCollection|Permission[]
     */
    public function getPermissions(): NodeCollection
    {
        return $this->getPermissionsAsTree()->flatten();
    }

    /**
     * @param Role $role
     * @return NodeCollection
     */
    public function getPermissionsForRole(Role $role): NodeCollection
    {
        return NodeCollection::make(
            $this->permissionToRole[$role->getName()] ?? []
        )->withInherited();
    }

    /**
     * @param string $name
     * @return null|Permission
     * @throws \Exception
     */
    public function getPermissionByName(string $name)
    {
        foreach ($this->getPermissions() as $permission) {
            if ($permission->getName() === $name) {
                return $permission;
            }
        }

        throw new \Exception('cant find permission: ' . $name);
    }

    /**
     * @param string|Permission $permission
     * @param string|Role $role
     * @throws \Exception
     */
    public function addPermissionToRole($permission, Role $role)
    {
        if (is_string($permission)) {
            $permission = $this->getPermissionByName($permission);
        }

        if (!$this->permissionToRole[$role->getName()]) {
            $this->permissionToRole[$role->getName()] = new NodeCollection();
        }

        $this->permissionToRole[$role->getName()][] = $permission;
    }

    /**
     * @param string|Permission $permission
     * @param string|Role $role
     * @throws \Exception
     */
    public function removePermissionFromRole($permission, Role $role)
    {
        if (is_string($permission)) {
            $permission = $this->getPermissionByName($permission);
        }

        if ($this->permissionToRole[$role->getName()]) {
            $key = $this->permissionToRole[$role->getName()]->search($permission->getName());

            $this->permissionToRole[$role->getName()]->forget($key);
        }
    }

    /**
     * @return Node
     */
    public function getPermissionsAsTree(): Node
    {
        $root = new Node('dummy');

        foreach ($this->getter() as $key => $permission) {
            $root->addChildren($this->parse($key, $permission));
        }

        return $root;
    }

    public function parse($key, $node): Node
    {
        foreach ($node as $field) {
            if (is_string($field)) {
                $title = $field;
            } elseif (is_callable($field)) {
                $truth = $field;
            } elseif (is_array($field)) {
                $children = $field;
            }
        }

        $final = new Permission($key, $title ?? '', $truth ?? null);

        foreach ($children ?? [] as $key => $permission) {
            $final->addChildren($this->parse($key, $permission));
        }

        return $final;
    }

    /**
     * @param Permission|string $permission
     */
    public function addPermission($permission)
    {
        throw new \LogicException('Cant do that');
    }

    /**
     * @param Permission|string $permission
     */
    public function removePermission($permission)
    {
        throw new \LogicException('Cant do that');
    }
}
