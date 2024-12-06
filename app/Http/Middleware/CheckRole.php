<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $hasRole = match ($role) {
            'admin' => auth()->user()->isAdmin(),
            'supplier' => auth()->user()->isSupplier(),
            default => false
        };

        if (!$hasRole) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
