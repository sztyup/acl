<?php

namespace Sztyup\Acl\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Sztyup\Acl\Contracts\UsesAcl;
use Sztyup\Acl\Exception\NotAuthorizedException;

class Acl
{
    public function handle(Request $request, Closure $next)
    {
        $action = $request->route()->getAction();

        $user = $request->user();
        $this->checkUser($user);

        $roles = $action['is'];
        $permissions = $action['can'];

        $missingRoles = [];
        $missingPermissions = [];

        foreach ($roles as $role) {
            if (!$user->hasRole($role)) {
                $missingRoles[] = $role;
            }
        }

        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                $missingPermissions[] = $permission;
            }
        }

        if (count($missingRoles) > 0 || count($missingPermissions) > 0) {
            throw new NotAuthorizedException($missingRoles, $missingPermissions);
        }

        return $next($request);
    }

    private function checkUser($user)
    {
        if ($user == null) {
            throw new AuthenticationException();
        }

        $reflection = new \ReflectionClass($user);

        if (!$reflection->implementsInterface(UsesAcl::class)) {
            throw new \Exception('User doesnt implement Sztyup\Acl\Contracts\UsesAcl interface');
        }
    }
}
