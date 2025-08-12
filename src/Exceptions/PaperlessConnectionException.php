<?php

namespace Codewithathis\PaperlessNgx\Exceptions;

use Exception;

/**
 * Exception thrown when there are connection issues with Paperless-ngx
 */
class PaperlessConnectionException extends PaperlessException
{
    protected string $baseUrl;
    protected ?string $reason;

    public function __construct(
        string $message = "",
        string $baseUrl = "",
        ?string $reason = null,
        int $code = 0,
        ?Exception $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous, $context);
        $this->baseUrl = $baseUrl;
        $this->reason = $reason;
    }

    /**
     * Get the base URL that was being connected to
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get the reason for the connection failure
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Check if this is a timeout error
     */
    public function isTimeoutError(): bool
    {
        return $this->reason && str_contains(strtolower($this->reason), 'timeout');
    }

    /**
     * Check if this is a DNS resolution error
     */
    public function isDnsError(): bool
    {
        return $this->reason && str_contains(strtolower($this->reason), 'dns');
    }

    /**
     * Check if this is an SSL/TLS error
     */
    public function isSslError(): bool
    {
        return $this->reason && str_contains(strtolower($this->reason), 'ssl');
    }

    /**
     * Check if this is a network unreachable error
     */
    public function isNetworkUnreachable(): bool
    {
        return $this->reason && str_contains(strtolower($this->reason), 'network');
    }
}
