<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Here you should list all the permission used by your application
    |
    */
    'permission_repository' => \Sztyup\Acl\Contracts\PermissionRepository::class,
    'role_repository' => \Sztyup\Acl\Contracts\RoleRepository::class,

    'permissions_to_role_class' => \Sztyup\Acl\Contracts\PermissionsToRole::class,
    'role_to_user_class' => \Sztyup\Acl\Contracts\RoleToUser::class,

    /*
    |--------------------------------------------------------------------------
    | Permission inheritance
    |--------------------------------------------------------------------------
    |
    | You can enable/disable permission inheritance
    |
    */
    'inheritance' => true,
];
