<?php

namespace Sztyup\Acl\Exception;

use Exception;

class NotAuthorizedException extends Exception
{
    public function __construct($missingRoles = [], $missingPermissions = [])
    {
        $message = "You are not authorized\n";

        foreach ($missingRoles as $role) {
            $message .= 'role: ' . $role . "\n";
        }

        foreach ($missingPermissions as $permission) {
            $message .= 'perm: ' . $permission . "\n";
        }

        parent::__construct($message, 403, null);
    }
}
