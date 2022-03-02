<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $roles)
    {
        $roles = explode('|', $roles);
        $userRole = $request->user()->rol->name;

        if (!in_array($userRole, $roles)) {
            return response(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
