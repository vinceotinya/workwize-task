<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',

        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'jwt.auth' => \PHPOpenSourceSaver\JWTAuth\Middleware\GetUserFromToken::class,
            'check.role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $exception, Request $request) {
            // If the request is not an API request, return false
            if (!$request->is('api/*')) {
                return false;
            }

            // Get the status code from the exception
            $statusCode = method_exists($exception, 'getStatusCode')
                ? $exception->getStatusCode()
                : 500;

            // Get the errors from the exception
            $errors = null;
            if ($exception instanceof ValidationException) {
                $errors = $exception->errors();
                $statusCode = 422;
            }

            // Return the JSON response with common structure
            return response()->json([
                'success' => false,
                'status' => $statusCode,
                'message' => $exception->getMessage(),
                'errors' => $errors,
                'stack' => config('app.debug') ? $exception->getTrace() : null
            ], $statusCode);
        });
    })->create();
