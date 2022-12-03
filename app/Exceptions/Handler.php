<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // ======= Start CUSTOM 
        // Redirect back to form with error message when CSRF session expires
        if ($exception instanceof \Illuminate\Session\TokenMismatchException)
        {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false, 
                    'message' => "Token timed out.",
                ], 403);
            }
            return redirect()
                    ->back()
                    ->withInput($request->except('password', '_token'))
                    ->with(['alert_danger' => 'Your session timed out. Please try again']);
        }   

        // Catch undeclared route method
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) 
        {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false, 
                    'message' => "Unsupported request method.",
                ], 405);
            }
            return redirect()
                ->back()
                ->with(['alert_danger' => 'Page Not Found']);
        }

        // Catch authorization exception
        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) 
        {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false, 
                    'message' => "You do not have enough permissions to perform this action.", // $exception->getMessage()
                ], 403);
            }
        }

        // Catch validation exception
        if ($exception instanceof \Illuminate\Validation\ValidationException) 
        {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false, 
                    'message' => "One or more of the given data has an error.", // $exception->getMessage()
                    'errors' => $exception->errors()
                ], 422);
            }
        }

        // Catch query exception
        if ($exception instanceof \Illuminate\Database\QueryException) 
        {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false, 
                    'message' => $exception->getMessage(),
                ], 500);
            }
        }

        // Catch model not found exception
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) 
        {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Resource item not found.',
                ], 404);
            }
        }

        // Catch model not found exception
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) 
        {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Resource item not found.',
                ], 404);
            }
        }

        // Catch no query results exception
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) 
        {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'No results found.',
                ], 404);
            }
        }

        // Catch do-space AWS exception
        if ($exception instanceof \Aws\S3\Exception\S3Exception) 
        {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Internal Error: could not upload file to cloud space.',
                ], 500);
            }
        }
        // ========================= End CUSTOM

        return parent::render($request, $exception);
    }

    /**
     * MY CUSTOM METHOD
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, \Illuminate\Auth\AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => false, 
                'message' => "Unauthenticated.", // $exception->getMessage()
            ], 401);
        }
        return redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}
