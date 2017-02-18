<?php

namespace NUSWhispers\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $urlFragments = parse_url(url()->current());

        $scheme = empty($urlFragments['scheme']) ? 'https' : $urlFragments['scheme'];
        $port = ! empty($urlFragments['port']) ? ':' . $urlFragments['port'] : '';

        return $next($request)
            ->header('Access-Control-Allow-Origin', $scheme . '://' . $urlFragments['host'] . $port)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }
}
