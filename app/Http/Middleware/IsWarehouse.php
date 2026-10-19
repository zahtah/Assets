<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * دسترسی به بخش انبار: فقط نقش «انبار» و «ادمین»
 */
class IsWarehouse
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->user()?->role;

        if (!in_array($role, ['warehouse', 'admin'], true)) {
            abort(403);
        }

        return $next($request);
    }
}
