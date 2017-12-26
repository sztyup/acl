<?php

namespace Sztyup\Acl\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Sztyup\Acl\AclManager;
use Sztyup\Acl\Contracts\HasAcl;
use Sztyup\Acl\Exception\NotAuthorizedException;

class Acl
{
    protected $acl;

    public function __construct(AclManager $acl)
    {
        $this->acl = $acl;
    }

    public function handle(Request $request, Closure $next)
    {
        if ($auth = $this->parseAuth($request)) {
            return $auth;
        };

        list($roles, $permissions) = $this->parseAcl($request);

        $user = $this->getUser($request);

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

    private function parseAuth(Request $request)
    {
        $auth = $request->route()->getAction('auth');

        if ($auth == null) { // Auth is not required
            return false;
        }

        if ($user = $this->getUser($request)) { // If authenticated we are done
            return false;
        }

        if ($request->ajax() || $request->wantsJson()) { // Dont redirect json
            return response('Unauthorized.', 401);
        }

        if ($auth['target']) {
            return redirect()->route('main.auth.redirect', $auth['target']);
        }

        if ($auth['route']) {
            return redirect()->route($auth['route']);
        }

        throw new AuthenticationException();
    }

    private function parseAcl(Request $request)
    {
        $action = $request->route()->getAction();
        return [
            Arr::wrap($action['is'] ?? []),
            Arr::wrap($action['can'] ?? [])
        ];
    }

    private function getUser(Request $request): HasAcl
    {
        $user = $request->user();
        if ($user == null) {
            throw new AuthenticationException();
        }

        $reflection = new \ReflectionClass($user);

        if (!$reflection->implementsInterface(HasAcl::class)) {
            throw new \Exception('User doesnt implement Sztyup\Acl\Contracts\UsesAcl interface');
        }

        return $user;
    }
}
