<?php

namespace Sztyup\Acl\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sztyup\Acl\Exception\NotAuthorizedException;

class Acl
{
    public function handle(Request $request, Closure $next)
    {
        $action = $request->route()->getAction();
        $roles = $action['is'];
        $permissions = $action['can'];

        $missingRoles = [];
        $missingPermissions = [];

        foreach ($roles as $role) {
            if (!$request->user()->hasRole($role)) {
                $missingRoles[] = $role;
            }
        }

        foreach ($permissions as $permission) {
            if (!$request->user()->hasPermission($permission)) {
                $missingPermissions[] = $permission;
            }
        }

        if (count($missingRoles) > 0 || count($missingPermissions) > 0) {
            throw new NotAuthorizedException($missingRoles, $missingPermissions);
        }

        return $next($request);
    }
}
