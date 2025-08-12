<?php

namespace Codewithathis\PaperlessNgx;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Exception;

class PaperlessService
{
    private string $baseUrl;
    private ?string $token = null;
    private ?string $username = null;
    private ?string $password = null;
    private bool $useBasicAuth = false;

    public function __construct(string $baseUrl, ?string $token = null, ?string $username = null, ?string $password = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $token;
        $this->username = $username;
        $this->password = $password;
        $this->useBasicAuth = !empty($username) && !empty($password);
    }

    /**
     * Set authentication token
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
        $this->useBasicAuth = false;
        return $this;
    }

    /**
     * Set basic authentication credentials
     */
    public function setBasicAuth(string $username, string $password): self
    {
        $this->username = $username;
        $this->password = $password;
        $this->useBasicAuth = true;
        return $this;
    }

    /**
     * Get the HTTP client with proper authentication headers
     */
    private function getHttpClient()
    {
        $client = Http::baseUrl($this->baseUrl);

        if ($this->useBasicAuth && $this->username && $this->password) {
            $client->withBasicAuth($this->username, $this->password);
        } elseif ($this->token) {
            $client->withHeaders([
                'Authorization' => "Token {$this->token}",
            ]);
        }

        return $client;
    }

    /**
     * Handle API response and throw exceptions for errors
     */
    private function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            $jsonResponse = $response->json();

            // Ensure we always return an array
            if (is_array($jsonResponse)) {
                return $jsonResponse;
            }

            // Handle case where API returns a string (like document ID)
            if (is_string($jsonResponse)) {
                return ['id' => $jsonResponse];
            }

            // If response is not an array or string, return empty array
            return [];
        }

        $errorMessage = "Paperless API Error: {$response->status()}";

        if ($response->body()) {
            try {
                $errorData = $response->json();
                if (isset($errorData['detail'])) {
                    $errorMessage .= " - {$errorData['detail']}";
                }
                if (isset($errorData['message'])) {
                    $errorMessage .= " - {$errorData['message']}";
                }
                if (isset($errorData['error'])) {
                    $errorMessage .= " - {$errorData['error']}";
                }
            } catch (Exception $e) {
                // Ignore JSON parsing errors
            }
        }

        throw new Exception($errorMessage, $response->status());
    }

    /**
     * Get system status
     */
    public function getStatus(): array
    {
        $response = $this->getHttpClient()->get('/api/status/');
        return $this->handleResponse($response);
    }

    /**
     * Get remote version information
     */
    public function getRemoteVersion(): array
    {
        $response = $this->getHttpClient()->get('/api/remote_version/');
        return $this->handleResponse($response);
    }

    /**
     * Get user profile
     */
    public function getProfile(): array
    {
        $response = $this->getHttpClient()->get('/api/profile/');
        return $this->handleResponse($response);
    }

    /**
     * Generate authentication token
     */
    public function generateAuthToken(): array
    {
        $response = $this->getHttpClient()->post('/api/profile/generate_auth_token/');

        return $this->handleResponse($response);
    }

    /**
     * Get documents with optional filters
     */
    public function getDocuments(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        $params = array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        $response = $this->getHttpClient()->get('/api/documents/', $params);
        return $this->handleResponse($response);
    }

    /**
     * Get a single document by ID
     */
    public function getDocument(int $id): array
    {
        $response = $this->getHttpClient()->get("/api/documents/{$id}/");
        return $this->handleResponse($response);
    }

    /**
     * Validate metadata before processing
     */
    private function validateMetadata(array $metadata): void
    {
        foreach ($metadata as $key => $value) {
            if ($value !== null) {
                if (is_array($value)) {
                    if (empty($value)) {
                        throw new Exception("Metadata field '{$key}' cannot be an empty array");
                    }
                    foreach ($value as $index => $arrayValue) {
                        if ($arrayValue === null) {
                            throw new Exception("Metadata field '{$key}[{$index}]' cannot be null");
                        }
                    }
                }
            }
        }
    }

    /**
     * Format metadata value for multipart form data
     */
    private function formatMetadataValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        // Fallback for any other type
        return (string) $value;
    }

    /**
     * Upload a document
     */
    public function uploadDocument(UploadedFile $file, array $metadata = []): array
    {
        // Validate file
        if (!$file->isValid()) {
            throw new Exception('Invalid file upload: ' . $file->getError());
        }

        // Check if file exists and is readable
        $realPath = $file->getRealPath();
        if (!$realPath || !is_readable($realPath)) {
            throw new Exception('File is not readable or does not exist');
        }

        // Check file size
        $maxSize = config('paperless.max_file_size', 52428800); // 50MB default
        if ($file->getSize() > $maxSize) {
            throw new Exception('File size exceeds maximum allowed size of ' . ($maxSize / 1024 / 1024) . 'MB');
        }

        // Validate metadata
        $this->validateMetadata($metadata);

        // Prepare multipart form data
        $fileStream = fopen($realPath, 'r');
        if (!$fileStream) {
            throw new Exception('Failed to open file for reading');
        }

        $multipart = [
            [
                'name' => 'document',
                'contents' => $fileStream,
                'filename' => $file->getClientOriginalName(),
            ]
        ];

        // Add metadata fields
        foreach ($metadata as $key => $value) {
            if ($value !== null) {
                if (is_array($value)) {
                    // Handle array values (like tags, correspondents, etc.)
                    foreach ($value as $arrayValue) {
                        if ($arrayValue !== null) {
                            $multipart[] = [
                                'name' => $key,
                                'contents' => $this->formatMetadataValue($arrayValue)
                            ];
                        }
                    }
                } else {
                    $multipart[] = [
                        'name' => $key,
                        'contents' => $this->formatMetadataValue($value)
                    ];
                }
            }
        }

        try {
            $response = $this->getHttpClient()
                ->asMultipart()
                ->post('/api/documents/post_document/', $multipart);

            $data = $this->handleResponse($response);

            // Paperless-ngx returns a task ID string for uploads. Normalize to task_id.
            if (isset($data['id']) && count($data) === 1) {
                return ['task_id' => $data['id']];
            }

            return $data;
        } catch (Exception $e) {
            throw new Exception('Failed to upload file to Paperless-ngx: ' . $e->getMessage());
        } finally {
            // Close the file stream if it was opened
            if (isset($fileStream) && is_resource($fileStream)) {
                fclose($fileStream);
            }
        }
    }

    /**
     * Get task status/details by task ID
     */
    public function getTaskByUUID(string $taskId): array
    {
        $response = $this->getHttpClient()->get("/api/tasks/?task_id={$taskId}");
        return $this->handleResponse($response);
    }

    /**
     * Update a document
     */
    public function updateDocument(int $id, array $data): array
    {
        $response = $this->getHttpClient()->put("/api/documents/{$id}/", $data);
        return $this->handleResponse($response);
    }

    /**
     * Patch a document (partial update)
     */
    public function patchDocument(int $id, array $data): array
    {
        $response = $this->getHttpClient()->patch("/api/documents/{$id}/", $data);
        return $this->handleResponse($response);
    }

    /**
     * Delete a document
     */
    public function deleteDocument(int $id): bool
    {
        $response = $this->getHttpClient()->delete("/api/documents/{$id}/");
        return $response->successful();
    }

    /**
     * Download a document
     */
    public function downloadDocument(int $id, bool $original = false): string
    {
        $params = $original ? ['original' => 'true'] : [];
        $response = $this->getHttpClient()->get("/api/documents/{$id}/download/", $params);

        if ($response->successful()) {
            return $response->body();
        }

        throw new Exception("Failed to download document: {$response->status()}");
    }

    /**
     * Get document preview
     */
    public function getDocumentPreview(int $id): string
    {
        $response = $this->getHttpClient()->get("/api/documents/{$id}/preview/");

        if ($response->successful()) {
            return $response->body();
        }

        throw new Exception("Failed to get document preview: {$response->status()}");
    }

    /**
     * Get document thumbnail
     */
    public function getDocumentThumbnail(int $id): string
    {
        $response = $this->getHttpClient()->get("/api/documents/{$id}/thumb/");

        if ($response->successful()) {
            return $response->body();
        }

        throw new Exception("Failed to get document thumbnail: {$response->status()}");
    }

    /**
     * Get document metadata
     */
    public function getDocumentMetadata(int $id): array
    {
        $response = $this->getHttpClient()->get("/api/documents/{$id}/metadata/");
        return $this->handleResponse($response);
    }

    /**
     * Get document suggestions
     */
    public function getDocumentSuggestions(int $id): array
    {
        $response = $this->getHttpClient()->get("/api/documents/{$id}/suggestions/");
        return $this->handleResponse($response);
    }

    /**
     * Get document notes
     */
    public function getDocumentNotes(int $id, int $page = 1, int $pageSize = 25): array
    {
        $params = [
            'page' => $page,
            'page_size' => $pageSize,
        ];

        $response = $this->getHttpClient()->get("/api/documents/{$id}/notes/", $params);
        return $this->handleResponse($response);
    }

    /**
     * Add a note to a document
     */
    public function addDocumentNote(int $id, string $note): array
    {
        $data = ['note' => $note];
        $response = $this->getHttpClient()->post("/api/documents/{$id}/notes/", $data);
        return $this->handleResponse($response);
    }

    /**
     * Delete a note from a document
     */
    public function deleteDocumentNote(int $documentId, int $noteId): bool
    {
        $response = $this->getHttpClient()->delete("/api/documents/{$documentId}/notes/?id={$noteId}");
        return $response->successful();
    }

    /**
     * Get document history
     */
    public function getDocumentHistory(int $id, int $page = 1, int $pageSize = 25): array
    {
        $params = [
            'page' => $page,
            'page_size' => $pageSize,
        ];

        $response = $this->getHttpClient()->get("/api/documents/{$id}/history/", $params);
        return $this->handleResponse($response);
    }

    /**
     * Email a document
     */
    public function emailDocument(int $id, array $emailData): array
    {
        $response = $this->getHttpClient()->post("/api/documents/{$id}/email/", $emailData);
        return $this->handleResponse($response);
    }

    /**
     * Get document share links
     */
    public function getDocumentShareLinks(int $id): array
    {
        $response = $this->getHttpClient()->get("/api/documents/{$id}/share_links/");
        return $this->handleResponse($response);
    }

    /**
     * Bulk download documents
     */
    public function bulkDownloadDocuments(array $documentIds): array
    {
        $data = ['documents' => $documentIds];
        $response = $this->getHttpClient()->post('/api/documents/bulk_download/', $data);
        return $this->handleResponse($response);
    }

    /**
     * Bulk edit documents
     */
    public function bulkEditDocuments(array $documentIds, array $editData): array
    {
        $data = array_merge(['documents' => $documentIds], $editData);
        $response = $this->getHttpClient()->post('/api/documents/bulk_edit/', $data);
        return $this->handleResponse($response);
    }

    /**
     * Get next available Archive Serial Number (ASN)
     */
    public function getNextASN(): int
    {
        $response = $this->getHttpClient()->get('/api/documents/next_asn/');
        return $this->handleResponse($response);
    }

    /**
     * Get document selection data
     */
    public function getDocumentSelectionData(array $documentIds): array
    {
        $data = ['documents' => $documentIds];
        $response = $this->getHttpClient()->post('/api/documents/selection_data/', $data);
        return $this->handleResponse($response);
    }

    /**
     * Search documents
     */
    public function searchDocuments(string $query, bool $dbOnly = false): array
    {
        $params = [
            'query' => $query,
            'db_only' => $dbOnly,
        ];

        $response = $this->getHttpClient()->get('/api/search/', $params);
        return $this->handleResponse($response);
    }

    /**
     * Get search autocomplete suggestions
     */
    public function getSearchAutocomplete(string $term, int $limit = 10): array
    {
        $params = [
            'term' => $term,
            'limit' => $limit,
        ];

        $response = $this->getHttpClient()->get('/api/search/autocomplete/', $params);
        return $this->handleResponse($response);
    }

    /**
     * Get tags
     */
    public function getTags(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        $params = array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        $response = $this->getHttpClient()->get('/api/tags/', $params);
        return $this->handleResponse($response);
    }

    /**
     * Create a tag
     */
    public function createTag(array $tagData): array
    {
        $response = $this->getHttpClient()->post('/api/tags/', $tagData);
        return $this->handleResponse($response);
    }

    /**
     * Update a tag
     */
    public function updateTag(int $id, array $tagData): array
    {
        $response = $this->getHttpClient()->put("/api/tags/{$id}/", $tagData);
        return $this->handleResponse($response);
    }

    /**
     * Delete a tag
     */
    public function deleteTag(int $id): bool
    {
        $response = $this->getHttpClient()->delete("/api/tags/{$id}/");
        return $response->successful();
    }

    /**
     * Get correspondents
     */
    public function getCorrespondents(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        $params = array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        $response = $this->getHttpClient()->get('/api/correspondents/', $params);
        return $this->handleResponse($response);
    }

    /**
     * Create a correspondent
     */
    public function createCorrespondent(array $correspondentData): array
    {
        $response = $this->getHttpClient()->post('/api/correspondents/', $correspondentData);
        return $this->handleResponse($response);
    }

    /**
     * Update a correspondent
     */
    public function updateCorrespondent(int $id, array $correspondentData): array
    {
        $response = $this->getHttpClient()->put("/api/correspondents/{$id}/", $correspondentData);
        return $this->handleResponse($response);
    }

    /**
     * Delete a correspondent
     */
    public function deleteCorrespondent(int $id): bool
    {
        $response = $this->getHttpClient()->delete("/api/correspondents/{$id}/");
        return $response->successful();
    }

    /**
     * Get document types
     */
    public function getDocumentTypes(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        $params = array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        $response = $this->getHttpClient()->get('/api/document_types/', $params);
        return $this->handleResponse($response);
    }

    /**
     * Create a document type
     */
    public function createDocumentType(array $documentTypeData): array
    {
        $response = $this->getHttpClient()->post('/api/document_types/', $documentTypeData);
        return $this->handleResponse($response);
    }

    /**
     * Update a document type
     */
    public function updateDocumentType(int $id, array $documentTypeData): array
    {
        $response = $this->getHttpClient()->put("/api/document_types/{$id}/", $documentTypeData);
        return $this->handleResponse($response);
    }

    /**
     * Delete a document type
     */
    public function deleteDocumentType(int $id): bool
    {
        $response = $this->getHttpClient()->delete("/api/document_types/{$id}/");
        return $response->successful();
    }

    /**
     * Get storage paths
     */
    public function getStoragePaths(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        $params = array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        $response = $this->getHttpClient()->get('/api/storage_paths/', $params);
        return $this->handleResponse($response);
    }

    /**
     * Create a storage path
     */
    public function createStoragePath(array $storagePathData): array
    {
        $response = $this->getHttpClient()->post('/api/storage_paths/', $storagePathData);
        return $this->handleResponse($response);
    }

    /**
     * Update a storage path
     */
    public function updateStoragePath(int $id, array $storagePathData): array
    {
        $response = $this->getHttpClient()->put("/api/storage_paths/{$id}/", $storagePathData);
        return $this->handleResponse($response);
    }

    /**
     * Delete a storage path
     */
    public function deleteStoragePath(int $id): bool
    {
        $response = $this->getHttpClient()->delete("/api/storage_paths/{$id}/");
        return $response->successful();
    }

    /**
     * Get custom fields
     */
    public function getCustomFields(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        $params = array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        $response = $this->getHttpClient()->get('/api/custom_fields/', $params);
        return $this->handleResponse($response);
    }

    /**
     * Create a custom field
     */
    public function createCustomField(array $customFieldData): array
    {
        $response = $this->getHttpClient()->post('/api/custom_fields/', $customFieldData);
        return $this->handleResponse($response);
    }

    /**
     * Update a custom field
     */
    public function updateCustomField(int $id, array $customFieldData): array
    {
        $response = $this->getHttpClient()->put("/api/custom_fields/{$id}/", $customFieldData);
        return $this->handleResponse($response);
    }

    /**
     * Delete a custom field
     */
    public function deleteCustomField(int $id): bool
    {
        $response = $this->getHttpClient()->delete("/api/custom_fields/{$id}/");
        return $response->successful();
    }

    /**
     * Get share links
     */
    public function getShareLinks(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        $params = array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        $response = $this->getHttpClient()->get('/api/share_links/', $params);
        return $this->handleResponse($response);
    }

    /**
     * Create a share link
     */
    public function createShareLink(array $shareLinkData): array
    {
        $response = $this->getHttpClient()->post('/api/share_links/', $shareLinkData);
        return $this->handleResponse($response);
    }

    /**
     * Update a share link
     */
    public function updateShareLink(int $id, array $shareLinkData): array
    {
        $response = $this->getHttpClient()->put("/api/share_links/{$id}/", $shareLinkData);
        return $this->handleResponse($response);
    }

    /**
     * Delete a share link
     */
    public function deleteShareLink(int $id): bool
    {
        $response = $this->getHttpClient()->delete("/api/share_links/{$id}/");
        return $response->successful();
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        $response = $this->getHttpClient()->get('/api/statistics/');
        return $this->handleResponse($response);
    }

    /**
     * Get saved views
     */
    public function getSavedViews(int $page = 1, int $pageSize = 25): array
    {
        $params = [
            'page' => $page,
            'page_size' => $pageSize,
        ];

        $response = $this->getHttpClient()->get('/api/saved_views/', $params);
        return $this->handleResponse($response);
    }

    /**
     * Create a saved view
     */
    public function createSavedView(array $savedViewData): array
    {
        $response = $this->getHttpClient()->post('/api/saved_views/', $savedViewData);
        return $this->handleResponse($response);
    }

    /**
     * Update a saved view
     */
    public function updateSavedView(int $id, array $savedViewData): array
    {
        $response = $this->getHttpClient()->put("/api/saved_views/{$id}/", $savedViewData);
        return $this->handleResponse($response);
    }

    /**
     * Delete a saved view
     */
    public function deleteSavedView(int $id): bool
    {
        $response = $this->getHttpClient()->delete("/api/saved_views/{$id}/");
        return $response->successful();
    }

    /**
     * Test the connection to Paperless-ngx
     */
    public function testConnection(): bool
    {
        try {
            $this->getStatus();
            return true;
        } catch (Exception $e) {
            Log::error('Paperless connection test failed', [
                'error' => $e->getMessage(),
                'base_url' => $this->baseUrl,
            ]);
            return false;
        }
    }
}
