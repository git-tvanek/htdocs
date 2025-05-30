<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (! $request->user() || ! $request->user()->hasRole($role)) {
            abort(Response::HTTP_FORBIDDEN, 'Nemáte potřebná práva.');
        }
        return $next($request);
    }
}
