<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        
        // check if user is authenticated
        if (!$request->user()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to access this page.');  
        }

        // check if user has the required role
        if ($request->user()->role !== $role) { 
            abort(403, 'Unauthorized action. You do not have the required role to access this resource.');
        }

        // check if user is active
        if (!$request->user()->isActive()) { 
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Your account is not active. Please contact the administrator.');
        }

        return $next($request);
    }
}
