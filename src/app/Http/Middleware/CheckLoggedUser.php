<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedException;
use Closure;

class CheckLoggedUser {
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws UnauthorizedException
     */
    public function handle($request, Closure $next)
    {
        $routeId = is_object($request->route('user'))
            ? (int)$request->route('user')->id
            : (int)$request->route('user');
        if ($routeId !== (int)(auth()->user()->id)) {
            throw new UnauthorizedException('User id in url didn\'t match the logged user');
        }
        return $next($request);
    }
}
