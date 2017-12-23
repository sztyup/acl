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
    'permissions_class' => \Sztyup\Acl\Permissions::class,

    'permission_to_role_class' => \Sztyup\Acl\Contracts\PermissionToRole::class,

    'role_to_user_class' => \Sztyup\Acl\Contracts\RoleToUser::class,
];
