<?php

namespace Sztyup\Acl\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Sztyup\Acl\AclManager;
use Sztyup\Acl\Exception\NotAuthorizedException;

class Acl
{
    /** @var AclManager */
    protected $acl;

    /** @var Authenticatable */
    protected $user;

    public function __construct(AclManager $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws NotAuthorizedException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        $this->acl->setUser($user);

        list($roles, $permissions) = $this->parseAcl($request);

        $aclAction = count($roles) + count($permissions) > 0;

        $authAction = $request->route()->getAction('auth');

        if ($authAction === null && $aclAction == 0) { // Auth is not required
            return $next($request);
        }

        if (!$user) {
            throw new AuthenticationException();
        }

        if ($aclAction == 0) { // No permission / role required
            return $next($request);
        }

        $missingRoles = [];
        $missingPermissions = [];

        foreach ($roles as $role) {
            if (!$this->acl->hasRole($role)) {
                $missingRoles[] = $role;
            }
        }

        foreach ($permissions as $permission) {
            if (!$this->acl->hasPermission($permission)) {
                $missingPermissions[] = $permission;
            }
        }

        if (count($missingRoles) > 0 || count($missingPermissions) > 0) {
            throw new NotAuthorizedException($missingRoles, $missingPermissions);
        }

        return $next($request);
    }

    private function parseAcl(Request $request)
    {
        $action = $request->route()->getAction();

        return [
            Arr::wrap($action['is'] ?? []),
            Arr::wrap($action['can'] ?? [])
        ];
    }
}
