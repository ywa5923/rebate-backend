<?php

use App\Exceptions\ApiException;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,

        ]);

        $middleware->alias([
            // 'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            // 'set.is_admin' => \App\Http\Middleware\SetIsAdminFlag::class,
            'superadmin-only' => \App\Http\Middleware\RequireSuperAdmin::class,
            'can-admin' => \App\Http\Middleware\CanAdmin::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $shouldRenderJson = static fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->render(function (ApiException $e, Request $request) use ($shouldRenderJson) {
            if (! $shouldRenderJson($request)) {
                return null;
            }

            $response = [
                'success' => false,
                'message' => $e->getMessage(),
            ];

            if ($e->getErrors() !== []) {
                $response['errors'] = $e->getErrors();
            }

            return response()->json($response, $e->getStatusCode());
        });

        $exceptions->render(function (ValidationException $e, Request $request) use ($shouldRenderJson) {
            if (! $shouldRenderJson($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) use ($shouldRenderJson) {
            if (! $shouldRenderJson($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
            ], 404);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) use ($shouldRenderJson) {
            if (! $shouldRenderJson($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'This action is unauthorized.',
            ], 403);
        });

        $exceptions->render(function (\Throwable $e, Request $request) use ($shouldRenderJson) {
            if (! $shouldRenderJson($request)) {
                return null;
            }

            $response = [
                'success' => false,
                'message' => 'Server custom error',
            ];

            if (config('app.debug')) {
                $response['error'] = $e->getMessage();
            }

            return response()->json($response, 500);
        });
    })->create();
