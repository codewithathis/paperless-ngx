<?php

namespace Codewithathis\PaperlessNgx\Http;

use Codewithathis\PaperlessNgx\Exceptions\PaperlessApiException;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @internal
 */
final class PaperlessApiClient
{
    private ?string $forcedAuth = null;

    public function __construct(
        private string $baseUrl,
        private ?string $token = null,
        private ?string $username = null,
        private ?string $password = null,
        private string $authMethod = 'auto'
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
        $this->forcedAuth = 'token';
    }

    public function setBasicAuth(string $username, string $password): void
    {
        $this->username = $username;
        $this->password = $password;
        $this->forcedAuth = 'basic';
    }

    public function pendingRequest(): PendingRequest
    {
        $client = Http::baseUrl($this->baseUrl);
        $this->applyAuthentication($client);

        return $this->configureRequest($client);
    }

    public function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            $jsonResponse = $response->json();

            if (is_array($jsonResponse)) {
                return $jsonResponse;
            }

            if (is_string($jsonResponse)) {
                return ['id' => $jsonResponse];
            }

            if (is_int($jsonResponse) || is_float($jsonResponse)) {
                return ['next_asn' => $jsonResponse];
            }

            return [];
        }

        $errorMessage = "Paperless API Error: {$response->status()}";
        $errorData = [];

        if ($response->body()) {
            try {
                $errorData = $response->json() ?? [];
                $errorParts = [];

                if (isset($errorData['detail'])) {
                    $errorParts[] = $errorData['detail'];
                }
                if (isset($errorData['message'])) {
                    $errorParts[] = $errorData['message'];
                }
                if (isset($errorData['error'])) {
                    $errorParts[] = $errorData['error'];
                }
                if (isset($errorData['non_field_errors'])) {
                    $errorParts[] = implode(', ', $errorData['non_field_errors']);
                }

                foreach ($errorData as $key => $value) {
                    if (is_array($value) && $key !== 'non_field_errors') {
                        $errorParts[] = "{$key}: " . implode(', ', $value);
                    }
                }

                if (! empty($errorParts)) {
                    $errorMessage .= ' - '.implode('; ', $errorParts);
                }
            } catch (Exception $e) {
                $bodyText = $response->body();
                if (is_string($bodyText) && ! empty(trim($bodyText))) {
                    $errorMessage .= ' - '.trim($bodyText);
                    $errorData = ['raw_response' => $bodyText];
                }
            }
        }

        $this->logApiError($errorMessage, $response->status(), $errorData);

        throw new PaperlessApiException($errorMessage, $response->status(), $errorData, $response->status());
    }

    public function jsonGet(string $uri, array $query = []): array
    {
        return $this->handleResponse($this->pendingRequest()->get($uri, $query));
    }

    public function jsonPost(string $uri, array $data = []): array
    {
        return $this->handleResponse($this->pendingRequest()->post($uri, $data));
    }

    public function jsonPut(string $uri, array $data = []): array
    {
        return $this->handleResponse($this->pendingRequest()->put($uri, $data));
    }

    public function jsonPatch(string $uri, array $data = []): array
    {
        return $this->handleResponse($this->pendingRequest()->patch($uri, $data));
    }

    public function successfulDelete(string $uri): bool
    {
        return $this->pendingRequest()->delete($uri)->successful();
    }

    public function bodyGet(string $uri, array $query = []): string
    {
        $response = $this->pendingRequest()->get($uri, $query);

        if ($response->successful()) {
            return $response->body();
        }

        throw new PaperlessApiException("Failed to fetch resource: {$response->status()}", $response->status(), [], $response->status());
    }

    public function multipartPost(string $uri, array $multipart): array
    {
        return $this->handleResponse(
            $this->pendingRequest()->asMultipart()->post($uri, $multipart)
        );
    }

    private function applyAuthentication(PendingRequest $client): void
    {
        $useBasic = false;
        $useToken = false;

        if ($this->forcedAuth === 'basic') {
            $useBasic = ! empty($this->username) && ! empty($this->password);
        } elseif ($this->forcedAuth === 'token') {
            $useToken = ! empty($this->token);
        } elseif ($this->authMethod === 'token') {
            $useToken = ! empty($this->token);
        } elseif ($this->authMethod === 'basic') {
            $useBasic = ! empty($this->username) && ! empty($this->password);
        } else {
            // auto — legacy: basic when both username and password are set, otherwise token
            if (! empty($this->username) && ! empty($this->password)) {
                $useBasic = true;
            } else {
                $useToken = ! empty($this->token);
            }
        }

        if ($useBasic && $this->username && $this->password) {
            $client->withBasicAuth($this->username, $this->password);
        } elseif ($useToken && $this->token) {
            $client->withHeaders([
                'Authorization' => "Token {$this->token}",
            ]);
        }
    }

    private function configureRequest(PendingRequest $pending): PendingRequest
    {
        $defaults = config('paperless.defaults', []);
        $security = config('paperless.security', []);

        $timeout = (int) ($security['timeout'] ?? $defaults['timeout'] ?? 30);
        $retries = (int) ($defaults['retry_attempts'] ?? 0);
        $verify = (bool) ($security['verify_ssl'] ?? true);

        if (! empty($security['allow_self_signed'])) {
            $verify = false;
        }

        $pending = $pending->timeout($timeout)->withOptions(['verify' => $verify]);

        if ($retries > 0) {
            $pending = $pending->retry($retries, 100);
        }

        return $pending;
    }

    private function logApiError(string $message, int $status, array $data): void
    {
        $logging = config('paperless.logging', []);
        if (empty($logging['enabled'])) {
            return;
        }

        $level = $logging['level'] ?? 'error';
        $context = [
            'status' => $status,
            'base_url' => $this->baseUrl,
            'response_data' => $data,
        ];

        Log::log($level, $message, $context);
    }
}
