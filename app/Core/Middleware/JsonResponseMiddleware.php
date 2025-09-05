<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class JsonResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\JsonResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Force JSON Accept header for API routes
        if ($this->shouldForceJsonResponse($request)) {
            $request->headers->set('Accept', 'application/json');
        }

        try {
            $response = $next($request);
            
            // Transform response to consistent JSON format if needed
            return $this->transformResponse($request, $response);
            
        } catch (Throwable $exception) {
            // Handle exceptions and return consistent JSON error responses
            return $this->handleException($request, $exception);
        }
    }

    /**
     * Determine if the request should force JSON response
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldForceJsonResponse(Request $request): bool
    {
        // Force JSON for API routes
        if ($request->is('api/*')) {
            return true;
        }

        // Force JSON if request already expects JSON
        if ($request->expectsJson()) {
            return true;
        }

        // Force JSON for AJAX requests
        if ($request->ajax()) {
            return true;
        }

        // Force JSON if Content-Type is application/json
        if ($request->header('Content-Type') === 'application/json') {
            return true;
        }

        return false;
    }

    /**
     * Transform the response to consistent JSON format
     *
     * @param Request $request
     * @param $response
     * @return JsonResponse|mixed
     */
    protected function transformResponse(Request $request, $response)
    {
        // Only transform if we should return JSON
        if (!$this->shouldForceJsonResponse($request)) {
            return $response;
        }

        // If it's already a JsonResponse, check if it needs formatting
        if ($response instanceof JsonResponse) {
            return $this->formatJsonResponse($response);
        }

        // If it's a regular Response with JSON content, convert it
        if ($response instanceof Response) {
            $content = $response->getContent();
            
            // Try to decode JSON content
            $jsonContent = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonContent)) {
                return $this->createSuccessResponse($jsonContent, $response->getStatusCode());
            }
            
            // If it's not JSON, wrap the content
            return $this->createSuccessResponse([
                'message' => $content ?: 'Request completed successfully',
                'data' => null
            ], $response->getStatusCode());
        }

        return $response;
    }

    /**
     * Format existing JSON response to consistent structure
     *
     * @param JsonResponse $response
     * @return JsonResponse
     */
    protected function formatJsonResponse(JsonResponse $response): JsonResponse
    {
        $data = $response->getData(true);
        $statusCode = $response->getStatusCode();

        // If already in our standard format, return as is
        if (isset($data['success']) && (isset($data['data']) || isset($data['error']))) {
            return $response;
        }

        // If it's a Laravel pagination response, handle specially
        if (isset($data['data']) && isset($data['meta']) && isset($data['links'])) {
            return $this->createSuccessResponse($data, $statusCode);
        }

        // If it's a Laravel resource response
        if (isset($data['data']) && !isset($data['success'])) {
            return $this->createSuccessResponse($data, $statusCode);
        }

        // Wrap other responses in our standard format
        return $this->createSuccessResponse($data, $statusCode);
    }

    /**
     * Handle exceptions and return consistent JSON error responses
     *
     * @param Request $request
     * @param Throwable $exception
     * @return JsonResponse
     */
    protected function handleException(Request $request, Throwable $exception): JsonResponse
    {
        // Only handle exceptions for requests that should return JSON
        if (!$this->shouldForceJsonResponse($request)) {
            throw $exception;
        }

        $statusCode = 500;
        $message = 'An error occurred while processing your request';
        $errors = null;
        $code = null;

        // Handle specific exception types
        switch (true) {
            case $exception instanceof ValidationException:
                $statusCode = 422;
                $message = 'The given data was invalid';
                $errors = $exception->errors();
                break;

            case $exception instanceof ModelNotFoundException:
                $statusCode = 404;
                $message = 'The requested resource was not found';
                $code = 'MODEL_NOT_FOUND';
                break;

            case $exception instanceof NotFoundHttpException:
                $statusCode = 404;
                $message = 'The requested endpoint was not found';
                $code = 'ROUTE_NOT_FOUND';
                break;

            case $exception instanceof MethodNotAllowedHttpException:
                $statusCode = 405;
                $message = 'The HTTP method is not allowed for this endpoint';
                $code = 'METHOD_NOT_ALLOWED';
                break;

            case $exception instanceof UnauthorizedHttpException:
                $statusCode = 401;
                $message = 'Authentication is required to access this resource';
                $code = 'UNAUTHORIZED';
                break;

            case $exception instanceof HttpException:
                $statusCode = $exception->getStatusCode();
                $message = $exception->getMessage() ?: $this->getDefaultErrorMessage($statusCode);
                break;

            default:
                // For other exceptions, check if we should expose the message
                if (app()->environment(['local', 'testing'])) {
                    $message = $exception->getMessage();
                }
                break;
        }

        return $this->createErrorResponse($message, $statusCode, $errors, $code, $exception);
    }

    /**
     * Create a consistent success response
     *
     * @param mixed $data
     * @param int $statusCode
     * @param string|null $message
     * @return JsonResponse
     */
    protected function createSuccessResponse($data = null, int $statusCode = 200, string $message = null): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
        ];

        // Add message if provided
        if ($message) {
            $response['message'] = $message;
        }

        // Add meta information for successful responses
        $response['meta'] = [
            'timestamp' => now()->toISOString(),
            'request_id' => $this->getRequestId(),
        ];

        return response()->json($response, $statusCode)
                        ->header('X-Request-ID', $response['meta']['request_id']);
    }

    /**
     * Create a consistent error response
     *
     * @param string $message
     * @param int $statusCode
     * @param array|null $errors
     * @param string|null $code
     * @param Throwable|null $exception
     * @return JsonResponse
     */
    protected function createErrorResponse(
        string $message, 
        int $statusCode = 500, 
        array $errors = null, 
        string $code = null,
        Throwable $exception = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code ?: $this->getErrorCode($statusCode),
            ],
        ];

        // Add validation errors if present
        if ($errors) {
            $response['error']['errors'] = $errors;
        }

        // Add debug information in development
        if (app()->environment(['local', 'testing']) && $exception) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        // Add meta information
        $response['meta'] = [
            'timestamp' => now()->toISOString(),
            'request_id' => $this->getRequestId(),
        ];

        // Log the error
        $this->logError($exception ?: new \Exception($message), $statusCode);

        return response()->json($response, $statusCode)
                        ->header('X-Request-ID', $response['meta']['request_id']);
    }

    /**
     * Get default error message for status code
     *
     * @param int $statusCode
     * @return string
     */
    protected function getDefaultErrorMessage(int $statusCode): string
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
        ];

        return $messages[$statusCode] ?? 'Unknown Error';
    }

    /**
     * Get error code for status code
     *
     * @param int $statusCode
     * @return string
     */
    protected function getErrorCode(int $statusCode): string
    {
        $codes = [
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            406 => 'NOT_ACCEPTABLE',
            408 => 'REQUEST_TIMEOUT',
            409 => 'CONFLICT',
            410 => 'GONE',
            422 => 'UNPROCESSABLE_ENTITY',
            429 => 'TOO_MANY_REQUESTS',
            500 => 'INTERNAL_SERVER_ERROR',
            501 => 'NOT_IMPLEMENTED',
            502 => 'BAD_GATEWAY',
            503 => 'SERVICE_UNAVAILABLE',
            504 => 'GATEWAY_TIMEOUT',
        ];

        return $codes[$statusCode] ?? 'UNKNOWN_ERROR';
    }

    /**
     * Generate or get request ID for tracing
     *
     * @return string
     */
    protected function getRequestId(): string
    {
        // Try to get existing request ID from header
        $requestId = request()->header('X-Request-ID');
        
        if (!$requestId) {
            // Generate new request ID
            $requestId = uniqid('req_', true);
        }

        return $requestId;
    }

    /**
     * Log error with context
     *
     * @param Throwable $exception
     * @param int $statusCode
     * @return void
     */
    protected function logError(Throwable $exception, int $statusCode): void
    {
        // Only log server errors (5xx) and some client errors
        if ($statusCode >= 500 || in_array($statusCode, [401, 403, 422])) {
            $logLevel = $statusCode >= 500 ? 'error' : 'warning';
            
            \Log::$logLevel($exception->getMessage(), [
                'exception' => get_class($exception),
                'status_code' => $statusCode,
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => auth()->id(),
                'request_id' => $this->getRequestId(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle CORS preflight requests
     *
     * @param Request $request
     * @param JsonResponse $response
     * @return JsonResponse
     */
    protected function handleCors(Request $request, JsonResponse $response): JsonResponse
    {
        // Add CORS headers for API requests
        if ($request->is('api/*')) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-Request-ID');
            $response->headers->set('Access-Control-Expose-Headers', 'X-Request-ID');
        }

        return $response;
    }

    /**
     * Add security headers
     *
     * @param JsonResponse $response
     * @return JsonResponse
     */
    protected function addSecurityHeaders(JsonResponse $response): JsonResponse
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }

    /**
     * Transform the response after it's been created
     *
     * @param Request $request
     * @param JsonResponse $response
     * @return JsonResponse
     */
    public function terminate(Request $request, $response)
    {
        // Add any final transformations or logging here
        if ($response instanceof JsonResponse && $this->shouldForceJsonResponse($request)) {
            $response = $this->handleCors($request, $response);
            $response = $this->addSecurityHeaders($response);
        }
    }
}