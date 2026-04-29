<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== RoleEnum::ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ admin mới có quyền truy cập',
            ], 403);
        }

        return $next($request);
    }
}
