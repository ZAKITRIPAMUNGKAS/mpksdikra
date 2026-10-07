<?php

use App\Http\Middleware\EnsureEventAccess;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => EnsureRole::class,
            'event_access' => EnsureEventAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $handleSessionExpired = function ($request) {
            $target = $request->headers->get('referer') ?: ($request->is('login') ? route('login') : route('landing'));

            return redirect($target)
                ->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with('error', 'Sesi halaman telah kedaluwarsa karena didiamkan terlalu lama. Halaman telah disegarkan otomatis, silakan coba kembali.');
        };

        $exceptions->render(function (HttpException $e, $request) use ($handleSessionExpired) {
            if ($e->getStatusCode() === 419) {
                return $handleSessionExpired($request);
            }
        });

        $exceptions->render(function (TokenMismatchException $e, $request) use ($handleSessionExpired) {
            return $handleSessionExpired($request);
        });
    })->create();
