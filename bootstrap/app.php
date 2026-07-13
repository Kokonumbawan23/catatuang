<?php

use App\Services\ActivityLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $logger = app(ActivityLogger::class);

                if ($e instanceof ValidationException) {
                    $userId = auth()->id() ?? 0;
                    $logger->validationFailure(
                        $userId,
                        $request->path(),
                        $e->errors()
                    );

                    return null;
                }

                if ($e instanceof AuthenticationException) {
                    $logger->serverError($e, ['type' => 'authentication']);

                    return null;
                }

                if ($e instanceof AuthorizationException) {
                    $logger->unauthorizedAccess(
                        auth()->id() ?? 0,
                        $request->path(),
                        $request->method()
                    );

                    return null;
                }

                if ($e instanceof NotFoundHttpException) {
                    return null;
                }

                if ($e instanceof HttpException && $e->getStatusCode() < 500) {
                    return null;
                }

                $logger->serverError($e, [
                    'path' => $request->path(),
                    'method' => $request->method(),
                ]);

                return null;
            }
        });
    })->create();
