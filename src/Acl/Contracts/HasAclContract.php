<?php

namespace Sztyup\Acl\Contracts;

interface HasAclContract
{
    /**
     * Gets all roles assigned to the user
     *
     * @return array
     */
    public function getRoles(): array;
}
