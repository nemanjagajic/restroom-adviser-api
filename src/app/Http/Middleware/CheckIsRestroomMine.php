<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedException;
use Closure;

class CheckIsRestroomMine {
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
        $restroom = $request->route('restroom');
        if ($restroom->user_id !== (int)(auth()->user()->id)) {
            throw new UnauthorizedException('Cannot delete restroom that you didn\'t create');
        }

        return $next($request);
    }
}
