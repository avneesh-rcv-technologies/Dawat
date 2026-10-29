<?php

namespace Modules\Acl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SanitizeRequest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {

        try {
            // Check if sanitization is enabled
            if (!Config::get('acl::sanitization.enabled', true)) {
                return $next($request);
            }


            // Check if route is excluded
            if ($this->isRouteExcluded($request)) {
                return $next($request);
            }

            // Check rate limiting for suspicious requests
            if ($this->isRateLimited($request)) {
                Log::warning('Request rate limited due to suspicious activity', [
                    'ip' => $request->ip(),
                    'url' => $request->url(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.'
                ], 429);
            }

            // Log suspicious activity if enabled
            if (Config::get('acl::sanitization.logging.log_suspicious_requests', true)) {
                $this->logSuspiciousActivity($request);
            }

            // Sanitize all request data
            $sanitizedData = $this->sanitizeArray($request->all());

            // Replace request data with sanitized version
            $request->replace($sanitizedData);

            // Sanitize headers if needed
            $this->sanitizeHeaders($request);

            // Sanitize file uploads
            $this->sanitizeFiles($request);

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Request sanitization failed', [
                'error' => $e->getMessage(),
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Request processing failed'
            ], 400);
        }
    }

    /**
     * Check if the current route should be excluded from sanitization
     */
    private function isRouteExcluded(Request $request): bool
    {
        $excludedRoutes = Config::get('acl::sanitization.excluded_routes', []);
        $currentRoute = $request->route() ? $request->route()->getName() : '';

        foreach ($excludedRoutes as $pattern) {
            if (fnmatch($pattern, $currentRoute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if request should be rate limited
     */
    private function isRateLimited(Request $request): bool
    {
        if (!Config::get('acl::sanitization.rate_limiting.enabled', false)) {
            return false;
        }

        $ip = $request->ip();
        $key = "suspicious_requests:{$ip}";
        $maxRequests = Config::get('acl::sanitization.rate_limiting.max_requests_per_minute', 10);

        $requestCount = Cache::get($key, 0);

        if ($requestCount >= $maxRequests) {
            return true;
        }

        // Check if request looks suspicious
        if ($this->isSuspiciousRequest($request)) {
            Cache::put($key, $requestCount + 1, now()->addMinutes(
                Config::get('acl::sanitization.rate_limiting.block_duration_minutes', 15)
            ));
        }

        return false;
    }

    /**
     * Determine if a request looks suspicious
     */
    private function isSuspiciousRequest(Request $request): bool
    {
        $requestData = $request->all();
        $dangerousPatterns = Config::get('acl::sanitization.dangerous_patterns', []);

        // Check for dangerous patterns in request data
        foreach ($requestData as $value) {
            if (is_string($value)) {
                foreach ($dangerousPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return true;
                    }
                }
            }
        }

        // Check request characteristics
        $maxQueryLength = Config::get('acl::sanitization.limits.max_query_string_length', 2000);
        $maxParamCount = Config::get('acl::sanitization.limits.max_parameters_count', 100);

        if (strlen($request->getQueryString()) > $maxQueryLength) {
            return true;
        }

        if (count($requestData) > $maxParamCount) {
            return true;
        }

        return false;
    }

    /**
     * Recursively sanitize array data
     */
    private function sanitizeArray(array $data): array
    {
        $sanitized = [];
        $excludedParams = Config::get('acl::sanitization.excluded_parameters', []);

        foreach ($data as $key => $value) {
            // Sanitize the key itself
            $cleanKey = $this->sanitizeString($key);

            // Skip sanitization for excluded parameters
            if (in_array($key, $excludedParams)) {
                $sanitized[$cleanKey] = $value;
                continue;
            }

            if (is_array($value)) {
                $sanitized[$cleanKey] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                // Check if this is a datetime string - if so, preserve it
                if ($this->isDateTimeString($value)) {
                    $sanitized[$cleanKey] = $value; // Preserve datetime strings as-is
                }
                // Check if this looks like JSON - if so, preserve it
                elseif ($this->isJsonString($value)) {
                    try {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            // It's valid JSON, sanitize the decoded data and re-encode
                            $sanitizedDecoded = is_array($decoded) ? $this->sanitizeArray($decoded) : $this->sanitizeString((string) $decoded);
                            $sanitized[$cleanKey] = json_encode($sanitizedDecoded);
                        } else {
                            // Invalid JSON, treat as regular string
                            $sanitized[$cleanKey] = $this->sanitizeString($value);
                        }
                    } catch (\Exception $e) {
                        // JSON decode failed, treat as regular string
                        $sanitized[$cleanKey] = $this->sanitizeString($value);
                    }
                } else {
                    // All strings go through standard sanitization (HTML encoding)
                    $sanitized[$cleanKey] = $this->sanitizeString($value);
                }
            } elseif (is_numeric($value)) {
                $sanitized[$cleanKey] = $this->sanitizeNumeric($value);
            } elseif (is_bool($value)) {
                $sanitized[$cleanKey] = $value;
            } elseif (is_null($value)) {
                $sanitized[$cleanKey] = null;
            } else {
                // For other types, convert to string and sanitize
                $sanitized[$cleanKey] = $this->sanitizeString((string) $value);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize string values by HTML encoding all potentially dangerous characters
     */
    private function sanitizeString(string $value): string
    {
        $rules = Config::get('acl::sanitization.rules', []);
        $dangerousPatterns = Config::get('acl::sanitization.dangerous_patterns', []);
        $maxLength = Config::get('acl::sanitization.limits.max_parameter_length', 10000);

        // Truncate if too long
        if (strlen($value) > $maxLength) {
            $value = substr($value, 0, $maxLength);
            Log::warning('Parameter truncated due to excessive length', [
                'original_length' => strlen($value),
                'truncated_to' => $maxLength
            ]);
        }

        // Remove null bytes
        if ($rules['remove_null_bytes'] ?? true) {
            $value = str_replace(chr(0), '', $value);
        }

        // Check for dangerous patterns and log them
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                if (Config::get('acl::sanitization.logging.log_blocked_patterns', true)) {
                    // Log::warning('Dangerous pattern detected in input', [
                    //     'pattern' => $pattern,
                    //     'value' => substr($value, 0, 100), // Log first 100 chars only
                    //     'ip' => request()->ip(),
                    //     'user_agent' => request()->userAgent()
                    // ]);
                }
                // Continue processing - we'll encode it below
            }
        }


        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);

        // Additional cleaning for special cases and encoding bypasses
        if ($rules['decode_entities'] ?? true) {
            $value = $this->cleanSpecialCases($value);
        }

        // Trim whitespace
        if ($rules['trim_whitespace'] ?? true) {
            $value = trim($value);
        }

        return $value;
    }

    /**
     * Sanitize numeric values
     */
    private function sanitizeNumeric($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        // If it's a string that should be numeric
        $cleaned = preg_replace('/[^0-9.-]/', '', (string) $value);

        if (is_numeric($cleaned)) {
            return strpos($cleaned, '.') !== false ? (float) $cleaned : (int) $cleaned;
        }

        return 0; // Default to 0 if not numeric
    }

    /**
     * Clean special cases that might bypass normal sanitization
     */
    private function cleanSpecialCases(string $value): string
    {
        // Remove Unicode control characters
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

        // Remove potential URL encoding bypasses for dangerous patterns
        $dangerousEncodings = [
            '%3Cscript%3E' => '',
            '%3C/script%3E' => '',
            '%3Cimg%20src%3D' => '',
            '%3Ciframe%20src%3D' => '',
            'javascript%3A' => '',
            'vbscript%3A' => '',
            '%3C' => '&lt;',  // Convert encoded < to safe HTML entity
            '%3E' => '&gt;',  // Convert encoded > to safe HTML entity
            '%22' => '&quot;', // Convert encoded " to safe HTML entity
            '%27' => '&#039;'  // Convert encoded ' to safe HTML entity
        ];

        foreach ($dangerousEncodings as $encoded => $replacement) {
            $value = str_ireplace($encoded, $replacement, $value);
        }

        // Handle double URL decoding attempts
        $value = urldecode($value);

        // After decoding, re-encode any HTML characters that might have been revealed
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);

        return $value;
    }

    /**
     * Sanitize request headers
     */
    private function sanitizeHeaders(Request $request): void
    {
        $dangerousHeaders = [
            'X-Forwarded-For',
            'X-Real-IP',
            'X-Originating-IP',
            'User-Agent',
            'Referer'
        ];

        foreach ($dangerousHeaders as $header) {
            if ($request->hasHeader($header)) {
                $value = $request->header($header);
                if (is_string($value)) {
                    $sanitized = $this->sanitizeString($value);
                    $request->headers->set($header, $sanitized);
                }
            }
        }
    }

    /**
     * Sanitize file uploads
     */
    private function sanitizeFiles(Request $request): void
    {
        if (!$request->hasFile('*')) {
            return;
        }

        foreach ($request->allFiles() as $key => $file) {
            if (is_array($file)) {
                foreach ($file as $singleFile) {
                    $this->validateSingleFile($singleFile);
                }
            } else {
                $this->validateSingleFile($file);
            }
        }
    }

    /**
     * Validate individual file upload
     */
    private function validateSingleFile($file): void
    {
        if (!$file || !$file->isValid()) {
            return;
        }

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $blockedExtensions = Config::get('acl::sanitization.blocked_extensions', []);

        // Block dangerous file extensions
        if (in_array(strtolower($extension), $blockedExtensions)) {
            if (Config::get('acl::sanitization.logging.log_file_uploads', true)) {
                Log::warning('Dangerous file upload attempt blocked', [
                    'filename' => $originalName,
                    'extension' => $extension,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            throw new \Exception('File type not allowed');
        }
    }

    /**
     * Log suspicious activity for security monitoring
     */
    private function logSuspiciousActivity(Request $request): void
    {
        if (!$this->isSuspiciousRequest($request)) {
            return;
        }

        $requestData = $request->all();
        $reasons = [];

        // Check for suspicious patterns in request data
        $dangerousPatterns = Config::get('acl::sanitization.dangerous_patterns', []);
        foreach ($requestData as $key => $value) {
            if (is_string($value)) {
                foreach ($dangerousPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $reasons[] = "Dangerous pattern in field: {$key}";
                        break;
                    }
                }
            }
        }

        // Check for suspicious request characteristics
        $maxQueryLength = Config::get('acl::sanitization.limits.max_query_string_length', 2000);
        $maxParamCount = Config::get('acl::sanitization.limits.max_parameters_count', 100);

        if (strlen($request->getQueryString()) > $maxQueryLength) {
            $reasons[] = 'Unusually long query string';
        }

        if (count($requestData) > $maxParamCount) {
            $reasons[] = 'Too many request parameters';
        }

        Log::warning('Suspicious request detected', [
            'reasons' => $reasons,
            'url' => $request->url(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data_sample' => array_slice($requestData, 0, 5) // Log first 5 parameters only
        ]);
    }

    /**
     * Check if a string looks like JSON
     */
    private function isJsonString(string $value): bool
    {
        // Basic checks for JSON-like strings
        $trimmed = trim($value);

        // Must start and end with appropriate JSON characters
        if (
            (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) ||
            (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if a string looks like a datetime
     */
    private function isDateTimeString(string $value): bool
    {
        $trimmed = trim($value);

        // Common datetime patterns
        $dateTimePatterns = [
            // ISO 8601 formats
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}([+-]\d{2}:\d{2}|Z)?$/',           // 2026-04-03T09:00:00+05:30
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}([+-]\d{2}:\d{2}|Z)?$/',     // 2026-04-03T09:00:00.000+05:30
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',                                 // 2026-04-03 09:00:00
            '/^\d{4}-\d{2}-\d{2}$/',                                                   // 2026-04-03
            '/^\d{2}:\d{2}:\d{2}$/',                                                   // 09:00:00
            '/^\d{2}:\d{2}$/',                                                         // 09:00
            // RFC 2822 format
            '/^[A-Za-z]{3}, \d{1,2} [A-Za-z]{3} \d{4} \d{2}:\d{2}:\d{2} [+-]\d{4}$/', // Thu, 03 Apr 2026 09:00:00 +0530
        ];

        foreach ($dateTimePatterns as $pattern) {
            if (preg_match($pattern, $trimmed)) {
                return true;
            }
        }

        return false;
    }
}
