<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  The roles allowed to access the route
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Check if user has a role
        if (!$request->user()->role) {
            return response()->json([
                'message' => 'User has no role assigned.'
            ], 403);
        }

        // Check if user's role is in the allowed roles
        $userRole = $request->user()->role->name;
        
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'Unauthorized. This action requires one of the following roles: ' . implode(', ', $roles),
                'required_roles' => $roles,
                'your_role' => $userRole,
            ], 403);
        }

        return $next($request);
    }
}
