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

    /** @var HasAcl */
    protected $user;

    public function __construct(AclManager $acl)
    {
        $this->acl = $acl;
    }

    public function handle(Request $request, Closure $next)
    {
        list($roles, $permissions) = $this->parseAcl($request);

        if ($auth = $this->parseAuth($request, count($roles) + count($permissions) > 0)) {
            return $auth;
        };

        $missingRoles = [];
        $missingPermissions = [];

        foreach ($roles as $role) {
            if (!$this->user->hasRole($role)) {
                $missingRoles[] = $role;
            }
        }

        foreach ($permissions as $permission) {
            if (!$this->user->hasPermission($permission)) {
                $missingPermissions[] = $permission;
            }
        }

        if (count($missingRoles) > 0 || count($missingPermissions) > 0) {
            throw new NotAuthorizedException($missingRoles, $missingPermissions);
        }

        return $next($request);
    }

    private function parseAuth(Request $request, bool $acl)
    {
        $auth = $request->route()->getAction('auth');

        if ($auth == null && $acl == false) { // Auth is not required
            return false;
        }

        if ($this->user = $this->getUser($request)) { // If authenticated we are done
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

        return redirect()->route('main.auth');
    }

    private function parseAcl(Request $request)
    {
        $action = $request->route()->getAction();

        return [
            Arr::wrap($action['is'] ?? []),
            Arr::wrap($action['can'] ?? [])
        ];
    }

    private function getUser(Request $request)
    {
        $user = $request->user();
        if ($user == null) {
            return null;
        }

        $reflection = new \ReflectionClass($user);

        if (!$reflection->implementsInterface(HasAcl::class)) {
            throw new \Exception('User doesnt implement Sztyup\Acl\Contracts\UsesAcl interface');
        }

        return $user;
    }
}
