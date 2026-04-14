<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    // public function render($request, Throwable $exception)
    // {
    //     if ($exception instanceof UnauthorizedHttpException) {
    //         return response()->json([
    //             'status' => 0,
    //             'error' => 'Unauthorized. Invalid or expired token.',
    //         ], 401);
    //     }

    //     if ($exception instanceof AuthenticationException) {
    //         return response()->json([
    //             'status' => 0,
    //             'error' => 'Unauthenticated. Please log in again.',
    //         ], 401);
    //     }

    //     return parent::render($request, $exception);
    // }
    
    //  public function render($request, Throwable $exception)
    // {
    //     if ($request->expectsJson()) {
    //         if ($exception instanceof UnauthorizedHttpException) {
    //             return response()->json([
    //                 'status' => 0,
    //                 'error' => 'Unauthorized. Invalid or expired token.',
    //             ], 401);
    //         }

    //         if ($exception instanceof AuthenticationException) {
    //             return response()->json([
    //                 'status' => 0,
    //                 'error' => 'Unauthenticated. Please log in again.',
    //             ], 401);
    //         }
    //     }

    //     return parent::render($request, $exception);
    // }
    public function render($request, Throwable $exception)
{
    // Check if the request is for an API route
    if ($request->is('api/*')) {
        if ($exception instanceof UnauthorizedHttpException) {
            return response()->json([
                'status' => 0,
                'error' => 'Unauthorized. Invalid or expired token.',
            ], 401);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'status' => 0,
                'error' => 'Unauthenticated. Please log in again.',
            ], 401);
        }
    }

    return parent::render($request, $exception);
}

}
