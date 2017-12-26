<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | Its very important to define the user model here
    |
    */
    'user_model' => \Illuminate\Contracts\Auth\Authenticatable::class,

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
