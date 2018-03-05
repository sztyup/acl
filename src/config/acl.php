<?php

return [
    'login_page' => '/login',
    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | Here you should list all the permission used by your application
    |
    */
    'permission_repository' => \Sztyup\Acl\Contracts\PermissionRepository::class,
    'role_repository' => \Sztyup\Acl\Contracts\RoleRepository::class,

    /*
    |--------------------------------------------------------------------------
    | Inheritance
    |--------------------------------------------------------------------------
    |
    | You can enable/disable inheritance
    |
    */
    'role_inheritance' => false,
    'permission_inheritance' => true,

    /*
    |--------------------------------------------------------------------------
    | Dynamic
    |--------------------------------------------------------------------------
    |
    | You can enable/disable dynamic role/permission behaviour
    |
    */
    'dynamic_roles' => true,
    'dynamic_permissions' => true,
];
