<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                $redirect = match ($user->role) {
                    'super_admin', 'admin', 'staff' => '/admin',
                    'agent'                          => '/agent',
                    'worker'                         => '/worker',
                    default                          => '/dashboard',
                };

                return redirect($redirect);
            }
        }

        return $next($request);
    }
}