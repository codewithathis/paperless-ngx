<?php

namespace Codewithathis\PaperlessNgx\Exceptions;

use Exception;

/**
 * Exception thrown when validation fails for documents, metadata, or other inputs
 */
class PaperlessValidationException extends PaperlessException
{
    protected array $errors = [];
    protected string $field;

    public function __construct(
        string $message = "",
        string $field = "",
        array $errors = [],
        int $code = 0,
        ?Exception $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous, $context);
        $this->field = $field;
        $this->errors = $errors;
    }

    /**
     * Get the field that failed validation
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get all validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Add a validation error for a specific field
     */
    public function addError(string $field, string $message): self
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
        return $this;
    }

    /**
     * Check if a specific field has validation errors
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    /**
     * Get errors for a specific field
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Get the first error message
     */
    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return reset($fieldErrors);
            }
        }
        return null;
    }
}
