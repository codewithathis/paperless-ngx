<?php

namespace Codewithathis\PaperlessNgx\Exceptions;

use Exception;

/**
 * Exception thrown when there are file-related errors during document operations
 */
class PaperlessFileException extends PaperlessException
{
    protected string $filePath;
    protected string $fileName;
    protected ?int $fileSize;
    protected string $operation;

    public function __construct(
        string $message = "",
        string $filePath = "",
        string $fileName = "",
        ?int $fileSize = null,
        string $operation = "",
        int $code = 0,
        ?Exception $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous, $context);
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->fileSize = $fileSize;
        $this->operation = $operation;
    }

    /**
     * Get the file path that caused the error
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Get the file name that caused the error
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Get the file size (if available)
     */
    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    /**
     * Get the operation that was being performed
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * Check if this is a file size error
     */
    public function isFileSizeError(): bool
    {
        return str_contains(strtolower($this->getMessage()), 'size');
    }

    /**
     * Check if this is a file type error
     */
    public function isFileTypeError(): bool
    {
        return str_contains(strtolower($this->getMessage()), 'type') || 
               str_contains(strtolower($this->getMessage()), 'mime');
    }

    /**
     * Check if this is a file permission error
     */
    public function isPermissionError(): bool
    {
        return str_contains(strtolower($this->getMessage()), 'permission') ||
               str_contains(strtolower($this->getMessage()), 'readable');
    }

    /**
     * Check if this is a file not found error
     */
    public function isFileNotFoundError(): bool
    {
        return str_contains(strtolower($this->getMessage()), 'not found') ||
               str_contains(strtolower($this->getMessage()), 'does not exist');
    }

    /**
     * Check if this is a file corruption error
     */
    public function isFileCorruptionError(): bool
    {
        return str_contains(strtolower($this->getMessage()), 'corrupt') ||
               str_contains(strtolower($this->getMessage()), 'invalid');
    }
}
