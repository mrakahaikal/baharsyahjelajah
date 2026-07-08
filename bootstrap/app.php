<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'set.locale' => SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $statusCode = $exception->getStatusCode();

            if ($statusCode >= 400 && $statusCode < 500) {
                return response()->view('errors.4xx', ['exception' => $exception], $statusCode, $exception->getHeaders());
            }

            if ($statusCode >= 500 && $statusCode < 600) {
                return response()->view('errors.5xx', ['exception' => $exception], $statusCode, $exception->getHeaders());
            }

            return null;
        });
    })->create();
