<?php

namespace Codewithathis\PaperlessNgx\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class PaperlessApiAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $config = config('paperless.api_auth');

        // Check if authentication is enabled
        if (!$config['enabled']) {
            return $next($request);
        }

        // Check IP whitelist first
        if (!$this->checkIpWhitelist($request, $config)) {
            return $this->unauthorizedResponse('IP address not allowed');
        }

        // Apply rate limiting
        if ($config['rate_limit']['enabled']) {
            $rateLimitResult = $this->checkRateLimit($request, $config);
            if ($rateLimitResult !== true) {
                return $rateLimitResult;
            }
        }

        // Apply authentication based on method
        $authMethod = $config['method'];
        
        switch ($authMethod) {
            case 'sanctum':
                return $this->handleSanctumAuth($request, $next, $config);
            case 'token':
                return $this->handleTokenAuth($request, $next, $config);
            case 'basic':
                return $this->handleBasicAuth($request, $next, $config);
            case 'none':
                return $next($request);
            default:
                return $this->unauthorizedResponse('Invalid authentication method');
        }
    }

    /**
     * Handle Sanctum authentication
     */
    private function handleSanctumAuth(Request $request, Closure $next, array $config): mixed
    {
        try {
            // Check if user is authenticated via Sanctum
            if (!Auth::guard('sanctum')->check()) {
                return $this->unauthorizedResponse('Sanctum authentication required');
            }

            $user = Auth::guard('sanctum')->user();
            
            // Log successful authentication
            Log::channel(config('paperless.logging.channel'))->info('Paperless API accessed', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
            ]);

            return $next($request);
        } catch (\Exception $e) {
            Log::channel(config('paperless.logging.channel'))->error('Sanctum authentication error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            
            return $this->unauthorizedResponse('Authentication failed');
        }
    }

    /**
     * Handle token-based authentication
     */
    private function handleTokenAuth(Request $request, Closure $next, array $config): mixed
    {
        $headerName = $config['token']['header_name'];
        $providedToken = $request->header($headerName);
        
        if (!$providedToken) {
            return $this->unauthorizedResponse("Missing {$headerName} header");
        }

        $allowedTokens = array_filter(explode(',', $config['token']['tokens']));
        
        if (empty($allowedTokens)) {
            Log::channel(config('paperless.logging.channel'))->warning('No API tokens configured');
            return $this->unauthorizedResponse('API tokens not configured');
        }

        if (!in_array($providedToken, $allowedTokens)) {
            Log::channel(config('paperless.logging.channel'))->warning('Invalid API token used', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
            ]);
            
            return $this->unauthorizedResponse('Invalid API token');
        }

        // Log successful token authentication
        Log::channel(config('paperless.logging.channel'))->info('Paperless API accessed with token', [
            'ip' => $request->ip(),
            'endpoint' => $request->path(),
        ]);

        return $next($request);
    }

    /**
     * Handle basic authentication
     */
    private function handleBasicAuth(Request $request, Closure $next, array $config): mixed
    {
        $username = $config['basic']['username'];
        $password = $config['basic']['password'];

        if (!$username || !$password) {
            Log::channel(config('paperless.logging.channel'))->warning('Basic auth credentials not configured');
            return $this->unauthorizedResponse('Basic authentication not configured');
        }

        $credentials = $request->getUser() . ':' . $request->getPassword();
        $expectedCredentials = $username . ':' . $password;

        if ($credentials !== $expectedCredentials) {
            Log::channel(config('paperless.logging.channel'))->warning('Invalid basic auth credentials', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
            ]);
            
            return $this->unauthorizedResponse('Invalid credentials', 401, [
                'WWW-Authenticate' => 'Basic realm="Paperless API"'
            ]);
        }

        // Log successful basic authentication
        Log::channel(config('paperless.logging.channel'))->info('Paperless API accessed with basic auth', [
            'ip' => $request->ip(),
            'endpoint' => $request->path(),
        ]);

        return $next($request);
    }

    /**
     * Check IP whitelist
     */
    private function checkIpWhitelist(Request $request, array $config): bool
    {
        $whitelist = $config['ip_whitelist'];
        
        if (empty($whitelist)) {
            return true; // No whitelist configured, allow all
        }

        $clientIp = $request->ip();
        $allowedIps = array_filter(explode(',', $whitelist));

        foreach ($allowedIps as $allowedIp) {
            $allowedIp = trim($allowedIp);
            
            // Check if it's a CIDR range
            if (strpos($allowedIp, '/') !== false) {
                if ($this->ipInCidrRange($clientIp, $allowedIp)) {
                    return true;
                }
            } else {
                // Direct IP match
                if ($clientIp === $allowedIp) {
                    return true;
                }
            }
        }

        Log::channel(config('paperless.logging.channel'))->warning('IP not in whitelist', [
            'ip' => $clientIp,
            'endpoint' => $request->path(),
        ]);

        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    private function ipInCidrRange(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);
        
        $ipBinary = ip2long($ip);
        $subnetBinary = ip2long($subnet);
        $maskBinary = ~((1 << (32 - $mask)) - 1);
        
        return ($ipBinary & $maskBinary) === ($subnetBinary & $maskBinary);
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit(Request $request, array $config): mixed
    {
        $key = 'paperless-api:' . $request->ip();
        $maxAttempts = $config['rate_limit']['max_attempts'];
        $decayMinutes = $config['rate_limit']['decay_minutes'];

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            Log::channel(config('paperless.logging.channel'))->warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429, [
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => time() + $retryAfter,
            ]);
        }

        RateLimiter::hit($key, $decayMinutes * 60);
        
        return true;
    }

    /**
     * Return unauthorized response
     */
    private function unauthorizedResponse(string $message, int $status = 401, array $headers = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => 'authentication_required',
        ], $status, $headers);
    }
}
