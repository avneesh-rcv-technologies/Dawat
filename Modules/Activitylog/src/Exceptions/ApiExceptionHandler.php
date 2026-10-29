<?php

namespace Modules\Activitylog\Exceptions;

use Throwable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Activitylog\Services\ActivityLoggerService;

class ApiExceptionHandler
{
    public static function handle(Throwable $e, $redirect = '', $isJson = true)
    {

        $request = request();

        // Validation errors
        if ($e instanceof ValidationException) {
            self::log('validation_exception', json_encode($e->errors()), $e, $request);
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $e->errors(),
                ], 200);
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        // Invalid argument exception
        if ($e instanceof \InvalidArgumentException) {
            self::log('invalid_argument_exception', $e->getMessage(), $e, $request);
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $e->getMessage(),
                ], 200);
            }

            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }

        // Generic exception
        if ($e instanceof \Exception) {
            self::log('exception', $e->getMessage(), $e, $request);
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $e->getMessage(),
                ], 200);
            }

            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }

        // Catch-all fallback
        self::log('other', $e->getMessage(), $e, $request);

        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'errors'  => 'Something went wrong',
            ], 200);
        }

        return redirect()->back()
            ->with('error', 'Something went wrong')
            ->withInput();
    }

    private static function log(string $event, string $message, $content, $request): void
    {
        $guard = Auth::getDefaultDriver();
        ActivityLoggerService::log(
            $message,
            Auth::guard($guard)->user(),
            [
                'user_id'    => Auth::guard($guard)->id(),
                'email'      => Auth::guard($guard)->user()->email ?? null,
                'errors'     => $content,
                'attempt_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'event'      => $event,
                'severity'   => 'critical',
            ],
            $guard
        );
    }
}
