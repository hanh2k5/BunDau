<?php

use App\Exceptions\OrderAlreadyProcessedException;
use App\Exceptions\OrderNotDeletableException;
use App\Exceptions\ProductInUseException;
use App\Exceptions\ProductNotAvailableException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);

        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 404 — Model not found (Concurrency/Deleted)
        $exceptions->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không còn tồn tại hoặc đã bị thay đổi bởi phiên làm việc khác.',
                ], 404);
            }
        });

        // 403 — Authorization
        $exceptions->renderable(function (AuthorizationException $e, Request $request) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quyền truy cập',
                ], 403);
            }
        });

        // 422 — Validation
        $exceptions->renderable(function (ValidationException $e, Request $request) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // 422 — Product not available
        $exceptions->renderable(function (ProductNotAvailableException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        });

        // 422 — Product in use
        $exceptions->renderable(function (ProductInUseException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        });

        // 409 — Order already processed
        $exceptions->renderable(function (OrderAlreadyProcessedException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        });

        // 422 — Order not deletable
        $exceptions->renderable(function (OrderNotDeletableException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        });
    })->create();
