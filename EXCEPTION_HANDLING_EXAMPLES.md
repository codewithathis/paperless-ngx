# Paperless-ngx Exception Handling Guide

This guide demonstrates how to use the custom exception classes for better error handling in your Laravel application.

## Overview

The package now provides several custom exception classes that extend the base `PaperlessException`:

- **`PaperlessApiException`** - For API-related errors (HTTP status codes, response data)
- **`PaperlessConnectionException`** - For connection issues (timeout, DNS, SSL, network)
- **`PaperlessValidationException`** - For validation failures (metadata, field validation)
- **`PaperlessFileException`** - For file-related errors (size, type, permissions, corruption)

## Basic Exception Handling

### Catching Specific Exceptions

```php
use Codewithathis\PaperlessNgx\PaperlessService;
use Codewithathis\PaperlessNgx\Exceptions\PaperlessApiException;
use Codewithathis\PaperlessNgx\Exceptions\PaperlessConnectionException;
use Codewithathis\PaperlessNgx\Exceptions\PaperlessValidationException;
use Codewithathis\PaperlessNgx\Exceptions\PaperlessFileException;

$paperlessService = app(PaperlessService::class);

try {
    $documents = $paperlessService->getDocuments();
    return response()->json(['success' => true, 'data' => $documents]);
    
} catch (PaperlessApiException $e) {
    // Handle API errors (4xx, 5xx status codes)
    $statusCode = $e->getStatusCode();
    $responseData = $e->getResponseData();
    
    if ($e->isAuthenticationError()) {
        return response()->json(['error' => 'Authentication failed'], 401);
    }
    
    if ($e->isNotFoundError()) {
        return response()->json(['error' => 'Resource not found'], 404);
    }
    
    if ($e->isUniqueConstraintViolation()) {
        return response()->json([
            'error' => 'Resource already exists',
            'message' => 'A resource with this name already exists. Please use a different name.',
            'suggestion' => 'Try using a different name or check if the resource already exists.'
        ], 409); // Conflict status code
    }
    
    if ($e->isBadRequestError()) {
        return response()->json(['error' => 'Invalid request'], 400);
    }
    
    return response()->json(['error' => $e->getMessage()], $statusCode);
    
} catch (PaperlessConnectionException $e) {
    // Handle connection issues
    if ($e->isTimeoutError()) {
        return response()->json(['error' => 'Request timed out'], 408);
    }
    
    if ($e->isSslError()) {
        return response()->json(['error' => 'SSL connection failed'], 502);
    }
    
    return response()->json(['error' => 'Service unavailable'], 503);
    
} catch (PaperlessValidationException $e) {
    // Handle validation errors
    return response()->json([
        'error' => 'Validation failed',
        'field' => $e->getField(),
        'errors' => $e->getErrors()
    ], 422);
    
} catch (PaperlessFileException $e) {
    // Handle file-related errors
    if ($e->isFileSizeError()) {
        return response()->json(['error' => 'File too large'], 413);
    }
    
    if ($e->isFileTypeError()) {
        return response()->json(['error' => 'Unsupported file type'], 415);
    }
    
    return response()->json(['error' => 'File processing failed'], 400);
}
```

## Using the Exception Handler

The `PaperlessExceptionHandler` class provides utility methods for consistent error handling:

### Basic Error Handling

```php
use Codewithathis\PaperlessNgx\Exceptions\PaperlessExceptionHandler;

try {
    $result = $paperlessService->uploadDocument($file, $metadata);
    return response()->json(['success' => true, 'data' => $result]);
    
} catch (PaperlessException $e) {
    $errorResponse = PaperlessExceptionHandler::handle($e);
    $userMessage = PaperlessExceptionHandler::getUserFriendlyMessage($e);
    $httpStatusCode = PaperlessExceptionHandler::getHttpStatusCode($e);
    
    return response()->json($errorResponse, $httpStatusCode);
}
```

### Advanced Error Handling with Context

```php
try {
    $result = $paperlessService->uploadDocument($file, $metadata);
    return response()->json(['success' => true, 'data' => $result]);
    
} catch (PaperlessException $e) {
    // Add custom context to the exception
    $e->setContext([
        'user_id' => auth()->id(),
        'file_name' => $file->getClientOriginalName(),
        'file_size' => $file->getSize(),
        'timestamp' => now()->toISOString(),
    ]);
    
    $errorResponse = PaperlessExceptionHandler::handle($e);
    $httpStatusCode = PaperlessExceptionHandler::getHttpStatusCode($e);
    
    // Check if the error is retryable
    if (PaperlessExceptionHandler::isRetryable($e)) {
        $errorResponse['retryable'] = true;
        $errorResponse['retry_after'] = 30; // seconds
    }
    
    return response()->json($errorResponse, $httpStatusCode);
}
```

## Exception-Specific Features

### PaperlessApiException

```php
catch (PaperlessApiException $e) {
    // Get HTTP status code
    $statusCode = $e->getStatusCode();
    
    // Get response data from the API
    $responseData = $e->getResponseData();
    
    // Check error types
    if ($e->isClientError()) {
        // 4xx errors - client should fix the request
        Log::warning('Client error from Paperless API', [
            'status' => $statusCode,
            'response' => $responseData
        ]);
    }
    
    if ($e->isServerError()) {
        // 5xx errors - server issue, might be retryable
        Log::error('Server error from Paperless API', [
            'status' => $statusCode,
            'response' => $responseData
        ]);
    }
    
    // Specific error checks
    if ($e->isAuthenticationError()) {
        // Handle 401 errors
        event(new AuthenticationFailed($e));
    }
    
    if ($e->isAuthorizationError()) {
        // Handle 403 errors
        event(new AuthorizationFailed($e));
    }
    
    if ($e->isValidationError()) {
        // Handle 422 errors
        $validationErrors = $responseData['errors'] ?? [];
        // Process validation errors
    }
    
    // Handle unique constraint violations specifically
    if ($e->isUniqueConstraintViolation()) {
        Log::warning('Unique constraint violation', [
            'status' => $statusCode,
            'response' => $responseData
        ]);
        
        // Extract field name from error message if possible
        $fieldName = 'name'; // Default field
        if (preg_match('/violates owner \/ (\w+) unique constraint/', $e->getMessage(), $matches)) {
            $fieldName = $matches[1];
        }
        
        // Return user-friendly error with suggestions
        return response()->json([
            'error' => 'Resource already exists',
            'field' => $fieldName,
            'message' => "A resource with this {$fieldName} already exists.",
            'suggestion' => "Please use a different {$fieldName} or check if the resource already exists.",
            'status_code' => 409
        ], 409);
    }
}
```

### PaperlessConnectionException

```php
catch (PaperlessConnectionException $e) {
    $baseUrl = $e->getBaseUrl();
    $reason = $e->getReason();
    
    // Check specific connection issues
    if ($e->isTimeoutError()) {
        // Implement exponential backoff
        $retryDelay = $this->calculateRetryDelay();
        Log::warning("Connection timeout to {$baseUrl}, retrying in {$retryDelay}s");
    }
    
    if ($e->isDnsError()) {
        // DNS resolution failed
        Log::error("DNS resolution failed for {$baseUrl}");
        // Maybe try alternative DNS servers
    }
    
    if ($e->isSslError()) {
        // SSL/TLS connection failed
        Log::error("SSL connection failed to {$baseUrl}: {$reason}");
        // Check SSL configuration
    }
    
    if ($e->isNetworkUnreachable()) {
        // Network connectivity issue
        Log::error("Network unreachable: {$reason}");
        // Check network configuration
    }
}
```

### PaperlessValidationException

```php
catch (PaperlessValidationException $e) {
    $field = $e->getField();
    $errors = $e->getErrors();
    $firstError = $e->getFirstError();
    
    // Log validation errors
    Log::warning('Validation failed', [
        'field' => $field,
        'errors' => $errors,
        'first_error' => $firstError
    ]);
    
    // Check specific field errors
    if ($e->hasError('tags')) {
        $tagErrors = $e->getFieldErrors('tags');
        // Handle tag validation errors
    }
    
    // Add errors to validation bag for forms
    $validator->errors()->add($field, $firstError);
    
    // Return validation response
    return response()->json([
        'error' => 'Validation failed',
        'field' => $field,
        'errors' => $errors
    ], 422);
}
```

### PaperlessFileException

```php
catch (PaperlessFileException $e) {
    $filePath = $e->getFilePath();
    $fileName = $e->getFileName();
    $fileSize = $e->getFileSize();
    $operation = $e->getOperation();
    
    // Log file operation details
    Log::error('File operation failed', [
        'file_path' => $filePath,
        'file_name' => $fileName,
        'file_size' => $fileSize,
        'operation' => $operation,
        'error' => $e->getMessage()
    ]);
    
    // Check specific file issues
    if ($e->isFileSizeError()) {
        // File too large
        $maxSize = config('paperless.max_file_size');
        $maxSizeMB = round($maxSize / 1024 / 1024, 2);
        
        return response()->json([
            'error' => "File size exceeds maximum allowed size of {$maxSizeMB}MB",
            'current_size' => $fileSize,
            'max_size' => $maxSize
        ], 413);
    }
    
    if ($e->isFileTypeError()) {
        // Unsupported file type
        $allowedTypes = config('paperless.allowed_mime_types', []);
        
        return response()->json([
            'error' => 'File type not supported',
            'file_name' => $fileName,
            'allowed_types' => $allowedTypes
        ], 415);
    }
    
    if ($e->isPermissionError()) {
        // File permission issue
        return response()->json([
            'error' => 'Unable to access file',
            'file_name' => $fileName,
            'suggestion' => 'Check file permissions'
        ], 403);
    }
}
```

## Error Response Structure

All exceptions processed through `PaperlessExceptionHandler::handle()` return a consistent structure:

```json
{
    "success": false,
    "error": {
        "message": "Original error message",
        "code": 0,
        "type": "Codewithathis\\PaperlessNgx\\Exceptions\\PaperlessApiException",
        "status_code": 422,
        "response_data": {
            "detail": "Validation failed",
            "errors": {
                "title": ["This field is required."]
            }
        },
        "is_client_error": true,
        "is_server_error": false,
        "is_validation_error": true,
        "field": "title",
        "validation_errors": {
            "title": ["This field is required."]
        },
        "first_error": "This field is required."
    }
}
```

## Best Practices

### 1. Always Catch Specific Exceptions

```php
// Good - Catch specific exceptions
try {
    $result = $paperlessService->uploadDocument($file, $metadata);
} catch (PaperlessApiException $e) {
    // Handle API errors
} catch (PaperlessFileException $e) {
    // Handle file errors
} catch (PaperlessException $e) {
    // Catch any other Paperless exceptions
} catch (Exception $e) {
    // Catch unexpected errors
}
```

### 2. Use the Exception Handler for Consistency

```php
// Good - Use the exception handler
try {
    $result = $paperlessService->uploadDocument($file, $metadata);
} catch (PaperlessException $e) {
    $errorResponse = PaperlessExceptionHandler::handle($e);
    $httpStatusCode = PaperlessExceptionHandler::getHttpStatusCode($e);
    
    return response()->json($errorResponse, $httpStatusCode);
}
```

### 3. Add Context for Better Debugging

```php
try {
    $result = $paperlessService->uploadDocument($file, $metadata);
} catch (PaperlessException $e) {
    $e->setContext([
        'user_id' => auth()->id(),
        'operation' => 'document_upload',
        'timestamp' => now()->toISOString(),
    ]);
    
    // The context will be included in logs and error responses
    throw $e;
}
```

### 4. Implement Retry Logic for Retryable Errors

```php
$maxRetries = 3;
$retryCount = 0;

while ($retryCount < $maxRetries) {
    try {
        $result = $paperlessService->uploadDocument($file, $metadata);
        break; // Success, exit retry loop
    } catch (PaperlessException $e) {
        if (!PaperlessExceptionHandler::isRetryable($e)) {
            throw $e; // Not retryable, re-throw
        }
        
        $retryCount++;
        if ($retryCount >= $maxRetries) {
            throw $e; // Max retries reached
        }
        
        // Wait before retrying (exponential backoff)
        $delay = pow(2, $retryCount) * 1000; // milliseconds
        usleep($delay * 1000);
    }
}
```

### 5. Log Errors with Context

```php
try {
    $result = $paperlessService->uploadDocument($file, $metadata);
} catch (PaperlessException $e) {
    Log::error('Paperless operation failed', [
        'operation' => 'upload_document',
        'file_name' => $file->getClientOriginalName(),
        'user_id' => auth()->id(),
        'exception' => $e->getMessage(),
        'context' => $e->getContext(),
        'trace' => $e->getTraceAsString()
    ]);
    
    throw $e;
}
```

## Migration from Generic Exception Handling

If you're updating existing code that catches generic `Exception` classes:

### Before (Generic Exception Handling)

```php
try {
    $result = $paperlessService->uploadDocument($file, $metadata);
} catch (Exception $e) {
    Log::error('Upload failed: ' . $e->getMessage());
    return response()->json(['error' => 'Upload failed'], 500);
}
```

### After (Specific Exception Handling)

```php
try {
    $result = $paperlessService->uploadDocument($file, $metadata);
} catch (PaperlessException $e) {
    $errorResponse = PaperlessExceptionHandler::handle($e);
    $httpStatusCode = PaperlessExceptionHandler::getHttpStatusCode($e);
    
    return response()->json($errorResponse, $httpStatusCode);
} catch (Exception $e) {
    // Handle any other unexpected errors
    Log::error('Unexpected error during upload: ' . $e->getMessage());
    return response()->json(['error' => 'An unexpected error occurred'], 500);
}
```

This approach provides much better error handling, debugging information, and user experience while maintaining backward compatibility.
