<?php

namespace Codewithathis\PaperlessNgx\Exceptions;

use Exception;

/**
 * Exception thrown when Paperless-ngx API returns an error response
 */
class PaperlessApiException extends PaperlessException
{
    protected int $statusCode;
    protected array $responseData;

    public function __construct(
        string $message = "",
        int $statusCode = 0,
        array $responseData = [],
        int $code = 0,
        ?Exception $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous, $context);
        $this->statusCode = $statusCode;
        $this->responseData = $responseData;
    }

    /**
     * Get the HTTP status code from the API response
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the response data from the API error
     */
    public function getResponseData(): array
    {
        return $this->responseData;
    }

    /**
     * Check if this is a client error (4xx status codes)
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Check if this is a server error (5xx status codes)
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500;
    }

    /**
     * Check if this is an authentication error (401 status code)
     */
    public function isAuthenticationError(): bool
    {
        return $this->statusCode === 401;
    }

    /**
     * Check if this is an authorization error (403 status code)
     */
    public function isAuthorizationError(): bool
    {
        return $this->statusCode === 403;
    }

    /**
     * Check if this is a not found error (404 status code)
     */
    public function isNotFoundError(): bool
    {
        return $this->statusCode === 404;
    }

    /**
     * Check if this is a validation error (422 status code)
     */
    public function isValidationError(): bool
    {
        return $this->statusCode === 422;
    }

    /**
     * Check if this is a unique constraint violation (400 status code with specific message)
     */
    public function isUniqueConstraintViolation(): bool
    {
        if ($this->statusCode !== 400) {
            return false;
        }
        
        $message = strtolower($this->getMessage());
        $responseData = $this->getResponseData();
        
        // Check message for unique constraint indicators
        if (str_contains($message, 'unique constraint') || 
            str_contains($message, 'already exists') ||
            str_contains($message, 'duplicate') ||
            str_contains($message, 'violates owner / name unique constraint')) {
            return true;
        }
        
        // Check response data for field-specific unique constraint errors
        foreach ($responseData as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $error) {
                    if (is_string($error) && (
                        str_contains(strtolower($error), 'unique') ||
                        str_contains(strtolower($error), 'already exists') ||
                        str_contains(strtolower($error), 'duplicate')
                    )) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Check if this is a bad request error (400 status code)
     */
    public function isBadRequestError(): bool
    {
        return $this->statusCode === 400;
    }
}
