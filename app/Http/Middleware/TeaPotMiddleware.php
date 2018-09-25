<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;

class TeaPotMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        abort(418);

        return $next($request);
    }
}
