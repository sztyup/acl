<?php

namespace Sztyup\Acl\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
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
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next)
    {
        if ($user = $request->user()) {
            $this->acl->setUser($user);
        }

        list($roles, $permissions) = $this->parseAcl($request);

        $aclAction = count($roles) + count($permissions) > 0;

        $authAction = $request->route()->getAction('auth');

        if ($authAction == null && $aclAction == 0) { // Auth is not required
            return $next($request);
        }

        if (!$user) {
            return $this->unauthenticated($request, $authAction);
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

    private function unauthenticated(Request $request, $action = [])
    {
        if ($request->ajax() || $request->wantsJson()) { // Dont redirect json
            return response('Unauthorized.', 401);
        }

        $request->session()->put('url.intended', $request->getUri());

        if (isset($auth['target'])) {
            return redirect()->route('main.auth.redirect', [
                'provider' => $action['target'],
                'from' => $request->getUri()
            ]);
        }

        if (isset($auth['route'])) {
            return redirect()->route($action['route']);
        }

        return redirect()->to(
            $this->acl->getRedirectUrl()
        );
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
