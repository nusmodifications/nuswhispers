<?php

namespace NUSWhispers\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->user()->role !== 'Administrator') {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->back()->withMessage('You are not authorized to visit this page.')->with('alert-class', 'alert-warning');
        }

        return $next($request);
    }
}
