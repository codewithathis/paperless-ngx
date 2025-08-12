<?php

namespace Codewithathis\PaperlessNgx\Exceptions;

use Exception;

/**
 * Base exception class for all Paperless-ngx related exceptions
 */
class PaperlessException extends Exception
{
    protected array $context = [];

    public function __construct(string $message = "", int $code = 0, ?Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get additional context information about the exception
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set additional context information
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }
}
