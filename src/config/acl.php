<?php

return [
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
    | Pivot classes
    |--------------------------------------------------------------------------
    |
    | Here you should list all the permission used by your application
    |
    */
    'permission_to_role' => \Sztyup\Acl\Contracts\PermissionsToRole::class,
    'role_to_user' => \Sztyup\Acl\Contracts\RoleToUser::class,

    /*
    |--------------------------------------------------------------------------
    | Inheritances
    |--------------------------------------------------------------------------
    |
    | You can enable/disable permission inheritance
    |
    */
    'role_inheritance' => false,
    'permission_inheritance' => true,

    /*
    |--------------------------------------------------------------------------
    | Dynamics
    |--------------------------------------------------------------------------
    |
    | You can enable/disable dynamic role/permission behaviour
    |
    */
    'dynamic_roles' => true,
    'dynamic_permissions' => true,
];
