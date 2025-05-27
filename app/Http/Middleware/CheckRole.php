<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return response()->json([
                'pesan' => 'Unauthorized. Anda belum login.',
            ], 401);
        }

        // Superadmin memiliki akses ke semua role
        if ($request->user()->role === 'superadmin') {
            return $next($request);
        }

        if ($request->user()->role !== $role) {
            return response()->json([
                'pesan' => 'Unauthorized. Anda tidak memiliki akses untuk melakukan tindakan ini.',
            ], 403);
        }

        return $next($request);
    }
}
