<?php

namespace Codewithathis\PaperlessNgx\Exceptions;

use Illuminate\Support\Facades\Log;

/**
 * Utility class for handling Paperless-ngx exceptions
 */
class PaperlessExceptionHandler
{
    /**
     * Handle a Paperless-ngx exception and return a user-friendly response
     */
    public static function handle(PaperlessException $exception): array
    {
        $context = $exception->getContext();
        
        // Log the exception with context
        Log::error('Paperless-ngx Exception', [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'context' => $context,
            'trace' => $exception->getTraceAsString(),
        ]);

        $response = [
            'success' => false,
            'error' => [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'type' => get_class($exception),
            ]
        ];

        // Add specific information based on exception type
        if ($exception instanceof PaperlessApiException) {
            $response['error']['status_code'] = $exception->getStatusCode();
            $response['error']['response_data'] = $exception->getResponseData();
            $response['error']['is_client_error'] = $exception->isClientError();
            $response['error']['is_server_error'] = $exception->isServerError();
            $response['error']['is_authentication_error'] = $exception->isAuthenticationError();
            $response['error']['is_authorization_error'] = $exception->isAuthorizationError();
            $response['error']['is_not_found_error'] = $exception->isNotFoundError();
            $response['error']['is_validation_error'] = $exception->isValidationError();
            $response['error']['is_unique_constraint_violation'] = $exception->isUniqueConstraintViolation();
            $response['error']['is_bad_request_error'] = $exception->isBadRequestError();
        }

        if ($exception instanceof PaperlessConnectionException) {
            $response['error']['base_url'] = $exception->getBaseUrl();
            $response['error']['reason'] = $exception->getReason();
            $response['error']['is_timeout_error'] = $exception->isTimeoutError();
            $response['error']['is_dns_error'] = $exception->isDnsError();
            $response['error']['is_ssl_error'] = $exception->isSslError();
            $response['error']['is_network_unreachable'] = $exception->isNetworkUnreachable();
        }

        if ($exception instanceof PaperlessValidationException) {
            $response['error']['field'] = $exception->getField();
            $response['error']['validation_errors'] = $exception->getErrors();
            $response['error']['first_error'] = $exception->getFirstError();
        }

        if ($exception instanceof PaperlessFileException) {
            $response['error']['file_path'] = $exception->getFilePath();
            $response['error']['file_name'] = $exception->getFileName();
            $response['error']['file_size'] = $exception->getFileSize();
            $response['error']['operation'] = $exception->getOperation();
            $response['error']['is_file_size_error'] = $exception->isFileSizeError();
            $response['error']['is_file_type_error'] = $exception->isFileTypeError();
            $response['error']['is_permission_error'] = $exception->isPermissionError();
            $response['error']['is_file_not_found_error'] = $exception->isFileNotFoundError();
            $response['error']['is_file_corruption_error'] = $exception->isFileCorruptionError();
        }

        // Add context if available
        if (!empty($context)) {
            $response['error']['context'] = $context;
        }

        return $response;
    }

    /**
     * Check if an exception is retryable
     */
    public static function isRetryable(PaperlessException $exception): bool
    {
        if ($exception instanceof PaperlessApiException) {
            // Retry on server errors (5xx) but not client errors (4xx)
            return $exception->isServerError();
        }

        if ($exception instanceof PaperlessConnectionException) {
            // Retry on timeout and network issues
            return $exception->isTimeoutError() || $exception->isNetworkUnreachable();
        }

        // Don't retry validation or file errors
        return false;
    }

    /**
     * Get a user-friendly error message
     */
    public static function getUserFriendlyMessage(PaperlessException $exception): string
    {
        if ($exception instanceof PaperlessApiException) {
            if ($exception->isAuthenticationError()) {
                return 'Authentication failed. Please check your credentials.';
            }
            if ($exception->isAuthorizationError()) {
                return 'Access denied. You do not have permission to perform this action.';
            }
            if ($exception->isNotFoundError()) {
                return 'The requested resource was not found.';
            }
            if ($exception->isValidationError()) {
                return 'The request data is invalid. Please check your input.';
            }
            if ($exception->isUniqueConstraintViolation()) {
                return 'A resource with this name already exists. Please use a different name.';
            }
            if ($exception->isBadRequestError()) {
                return 'The request is invalid. Please check your input and try again.';
            }
            if ($exception->isServerError()) {
                return 'The server encountered an error. Please try again later.';
            }
            return 'An error occurred while processing your request.';
        }

        if ($exception instanceof PaperlessConnectionException) {
            if ($exception->isTimeoutError()) {
                return 'The request timed out. Please check your connection and try again.';
            }
            if ($exception->isDnsError()) {
                return 'Unable to resolve the server address. Please check your configuration.';
            }
            if ($exception->isSslError()) {
                return 'SSL connection failed. Please check your SSL configuration.';
            }
            if ($exception->isNetworkUnreachable()) {
                return 'Network connection failed. Please check your network settings.';
            }
            return 'Unable to connect to the server. Please check your connection.';
        }

        if ($exception instanceof PaperlessValidationException) {
            return 'Validation failed: ' . $exception->getFirstError();
        }

        if ($exception instanceof PaperlessFileException) {
            if ($exception->isFileSizeError()) {
                return 'File size exceeds the maximum allowed limit.';
            }
            if ($exception->isFileTypeError()) {
                return 'File type is not supported.';
            }
            if ($exception->isPermissionError()) {
                return 'Unable to access the file. Please check file permissions.';
            }
            if ($exception->isFileNotFoundError()) {
                return 'File not found or inaccessible.';
            }
            if ($exception->isFileCorruptionError()) {
                return 'File appears to be corrupted or invalid.';
            }
            return 'File operation failed: ' . $exception->getMessage();
        }

        return 'An unexpected error occurred.';
    }

    /**
     * Get HTTP status code for the exception
     */
    public static function getHttpStatusCode(PaperlessException $exception): int
    {
        if ($exception instanceof PaperlessApiException) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof PaperlessConnectionException) {
            return 503; // Service Unavailable
        }

        if ($exception instanceof PaperlessValidationException) {
            return 422; // Unprocessable Entity
        }

        if ($exception instanceof PaperlessFileException) {
            return 400; // Bad Request
        }

        return 500; // Internal Server Error
    }
}
