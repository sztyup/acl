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
    'permissions_class' => \Sztyup\Acl\Contracts\Permissions::class,

    'permissions_to_role_class' => \Sztyup\Acl\Contracts\PermissionsToRole::class,

    'role_to_user_class' => \Sztyup\Acl\Contracts\RoleToUser::class,

    /*
    |--------------------------------------------------------------------------
    | Inheritance
    |--------------------------------------------------------------------------
    |
    | You can enable/disable permission inheritance
    |
    */

    'inheritance' => true,
];
