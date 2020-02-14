<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedException;
use Closure;

class CheckIsCommentMine {
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
        $comment = $request->route('comment');
        if ($comment->user_id !== (int)(auth()->user()->id)) {
            throw new UnauthorizedException('Cannot delete comment that you haven\'t left');
        }

        return $next($request);
    }
}
