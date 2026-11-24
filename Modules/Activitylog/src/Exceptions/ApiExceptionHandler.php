<?php

namespace Modules\Activitylog\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Models\Admin;
use Modules\Filemanager\Exceptions\FileServiceException;

use Modules\Notifications\Services\NotificationService;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    public static function handle(Throwable $e, $redirect = '', $isJson = true)
    {

        $request = request();

        // Validation errors
        if ($e instanceof ValidationException) {
            self::log('validation_exception', json_encode($e->errors()), $e, $request , 'warning');

            $firstError = collect($e->errors())->flatten()->first() ?: 'Validation failed';

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => $firstError,
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        // Filemanager upload errors — always show the concrete message
        if ($e instanceof FileServiceException) {
            self::log('file_upload_exception', $e->getMessage(), $e, $request, 'warning');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => ['file' => [$e->getMessage()]],
                ], 422);
            }

            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }

        // Invalid argument exception
        if ($e instanceof \InvalidArgumentException) {
            self::log('invalid_argument_exception', $e->getMessage(), $e, $request , 'warning');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Resource not found.',
                    'errors' => config('app.debug') ? $e->getMessage() : 'The requested resource was not found.',
                ], 404);
            }

            return redirect()->back()
                ->with('error', config('app.debug') ? $e->getMessage() : 'The requested resource was not found.')
                ->withInput();
        }

        // Not found HTTP exception (404)
        if ($e instanceof NotFoundHttpException) {
            self::log('page_not_found', 'Page not found', $e, $request, 'warning');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Page not found.',
                    'errors' => config('app.debug') ? $e->getMessage() : 'The requested page was not found.',
                ], 404);
            }

            return redirect()->back()
                ->with('error', config('app.debug') ? $e->getMessage() : 'The requested page was not found.');
        }

        // Method not allowed exception (405)
        if ($e instanceof MethodNotAllowedHttpException) {
            self::log('method_not_allowed', 'HTTP method not allowed', $e, $request, 'warning');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Method not allowed.',
                    'errors' => config('app.debug') ? $e->getMessage() : 'The request method is not supported for this route.',
                ], 405);
            }

            return redirect()->back()
                ->with('error', 'The request method is not supported.');
        }

        // Token mismatch exception (419)
        if ($e instanceof TokenMismatchException) {
            self::log('csrf_token_mismatch', 'CSRF token mismatch', $e, $request, 'warning');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Your session has expired. Please refresh the page and try again.',
                    'errors' => config('app.debug') ? $e->getMessage() : 'Your session has expired. Please refresh the page and try again.',
                ], 419);
            }

            return redirect()->back()
                ->with('error', config('app.debug') ? $e->getMessage() : 'Your session has expired. Please try again.')
                ->withInput();
        }

        // Too many requests exception (429)
        if ($e instanceof ThrottleRequestsException) {
            self::log('rate_limit_exceeded', 'Rate limit exceeded', $e, $request, 'warning');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Too many requests.',
                    'errors' => config('app.debug') ? $e->getMessage() : 'You have made too many requests. Please slow down.',
                ], 429);
            }

            return redirect()->back()
                ->with('error', config('app.debug') ? $e->getMessage() : 'Too many requests. Please try again later.');
        }

        // Database query exception (500)
        if ($e instanceof QueryException) {
            self::log('database_error', 'Database query error', $e, $request, 'error');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.',
                    'errors' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.',
                ], 500);
            }

            return redirect()->back()
                ->with('error', config('app.debug') ? $e->getMessage() : 'A database error occurred. Please try again.')
                ->withInput();
        }

        // Model not found exception (404)
        if ($e instanceof ModelNotFoundException) {
            self::log('model_not_found', 'Requested model record was not found', $e, $request, 'warning');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Resource not found.',
                    'errors' => config('app.debug') ? $e->getMessage() : 'The requested resource was not found.',
                ], 404);
            }

            return redirect()->back()
                ->with('error', config('app.debug') ? $e->getMessage() : 'The requested resource was not found.')
                ->withInput();
        }

        // Invalid argument exception
        if ($e instanceof \InvalidArgumentException) {
            self::log('invalid_argument', 'Invalid argument provided', $e, $request, 'warning');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Invalid argument provided',
                    'errors' => config('app.debug') ? $e->getMessage() : 'Invalid argument provided',
                ], 422);
            }

            return redirect()->back()
                ->with('error', config('app.debug') ? $e->getMessage() : 'Invalid argument provided')
                ->withInput();
        }

        // Authentication exceptions
        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            self::log('authentication_failed', 'Authentication required', $e, $request, 'warning');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'You must be logged in to access this resource.',
                    'errors' => config('app.debug') ? $e->getMessage() : 'You must be logged in to access this resource.',
                ], 401);
            }

            return redirect()->route('client.login.form')
                ->with('error', config('app.debug') ? $e->getMessage() : 'You must be logged in to access this resource.');
        }

        // Authorization exceptions
        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            self::log('authorization_failed', 'Access denied', $e, $request, 'warning');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'You do not have permission to perform this action.',
                    'errors' => config('app.debug') ? $e->getMessage() : 'You do not have permission to perform this action.',
                ], 403);
            }

            return redirect()->back()
                ->with('error', config('app.debug') ? $e->getMessage() : 'You do not have permission to perform this action.');
        }

        // Generic exception
        if ($e instanceof \Exception) {
            self::log('system_error', 'System error occurred', $e, $request, 'critical');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.',
                    'errors' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.',
                ], 500);
            }

            return redirect()->back()
                ->with('error', config('app.debug') ? $e->getMessage() : 'An unexpected error occurred. Please try again.')
                ->withInput();
        }

        // Catch-all fallback
        self::log('other', $e->getMessage(), $e, $request);

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'errors' => config('app.debug') ? $e->getMessage() : 'We are experiencing technical difficulties. Please try again later.',
            ], 500);
        }

        return redirect()->back()
            ->with('error', config('app.debug') ? $e->getMessage() : 'We are experiencing technical difficulties. Please try again later.')
            ->withInput();
    }

    private static function log(string $event, string $message, $content, $request, $severity = 'critical'): void
    {
        try {
            [$guard, $user] = self::resolveAuthenticatedContext();

            $defaultErrorRole = fn_get_setting('error.default_role');

            if ($severity == 'critical' || $severity == 'error') {
                $admins = Admin::where('role_id', $defaultErrorRole)->get();

                if (count($admins) > 0) {
                    foreach ($admins as $admin) {

                        app(NotificationService::class)->trigger(
                            'Activitylog',
                            'Error',
                            $admin->email,
                            [
                                'user_id' => $user->id ?? null,
                                'email' => $user->email ?? null,
                                'guard' => $guard,
                                'message' => $content->getMessage() . ' in ' . $content->getFile() . ' at line ' . $content->getLine(),
                                'attempt_ip' => $request->ip(),
                                'user_agent' => $request->userAgent(),
                                'event' => $event,
                                'severity' => $severity,
                            ],
                        );
                    }
                }
            }
        } catch (\Throwable $ex) {
            Log::error('Failed to send error notification: ' . $ex->getMessage());
        }
    }

    /**
     * @return array{0: string, 1: mixed}
     */
    private static function resolveAuthenticatedContext(): array
    {
        foreach (['admin', 'client', 'member', Auth::getDefaultDriver()] as $guard) {
            if (! is_string($guard) || $guard === '') {
                continue;
            }

            if (Auth::guard($guard)->check()) {
                return [$guard, Auth::guard($guard)->user()];
            }
        }

        $guard = Auth::getDefaultDriver();

        return [$guard, Auth::guard($guard)->user()];
    }
}
