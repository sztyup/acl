<?php

namespace Sztyup\Acl\Exception;

use Exception;

class NotAuthorizedException extends Exception
{
    protected $missionRoles;
    protected $missionPermissions;

    public function __construct($missingRoles = [], $missingPermissions = [])
    {
        $this->missionRoles = $missingRoles;
        $this->missionPermissions = $missingPermissions;

        parent::__construct("You are not authorized", 403, null);
    }

}