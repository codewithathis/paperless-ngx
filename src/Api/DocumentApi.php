<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Exceptions\PaperlessApiException;
use Codewithathis\PaperlessNgx\Exceptions\PaperlessConnectionException;
use Codewithathis\PaperlessNgx\Exceptions\PaperlessFileException;
use Codewithathis\PaperlessNgx\Exceptions\PaperlessValidationException;
use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;
use Exception;
use Illuminate\Http\UploadedFile;

/**
 * @internal
 */
final class DocumentApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getDocuments(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/documents/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function getDocument(int $id): array
    {
        return $this->client->jsonGet("/api/documents/{$id}/");
    }

    public function uploadDocument(UploadedFile $file, array $metadata = []): array
    {
        if (! $file->isValid()) {
            throw new PaperlessFileException(
                'Invalid file upload: '.$file->getError(),
                $file->getPathname(),
                $file->getClientOriginalName(),
                $file->getSize(),
                'upload_validation'
            );
        }

        $realPath = $file->getRealPath();
        if (! $realPath || ! is_readable($realPath)) {
            throw new PaperlessFileException(
                'File is not readable or does not exist',
                $file->getPathname(),
                $file->getClientOriginalName(),
                $file->getSize(),
                'file_readability_check'
            );
        }

        $maxSize = (int) config(
            'paperless.upload.max_file_size',
            config('paperless.max_file_size', 50 * 1024 * 1024)
        );

        if ($file->getSize() > $maxSize) {
            throw new PaperlessFileException(
                'File size exceeds maximum allowed size of '.($maxSize / 1024 / 1024).'MB',
                $file->getPathname(),
                $file->getClientOriginalName(),
                $file->getSize(),
                'file_size_validation'
            );
        }

        $this->validateMetadata($metadata);

        $fileStream = fopen($realPath, 'r');
        if (! $fileStream) {
            throw new PaperlessFileException(
                'Failed to open file for reading',
                $realPath,
                $file->getClientOriginalName(),
                $file->getSize(),
                'file_stream_open'
            );
        }

        $multipart = [
            [
                'name' => 'document',
                'contents' => $fileStream,
                'filename' => $file->getClientOriginalName(),
            ],
        ];

        foreach ($metadata as $key => $value) {
            if ($value !== null) {
                if (is_array($value)) {
                    foreach ($value as $arrayValue) {
                        if ($arrayValue !== null) {
                            $multipart[] = [
                                'name' => $key,
                                'contents' => $this->formatMetadataValue($arrayValue),
                            ];
                        }
                    }
                } else {
                    $multipart[] = [
                        'name' => $key,
                        'contents' => $this->formatMetadataValue($value),
                    ];
                }
            }
        }

        try {
            $data = $this->client->multipartPost('/api/documents/post_document/', $multipart);

            if (isset($data['id']) && count($data) === 1) {
                return ['task_id' => $data['id']];
            }

            return $data;
        } catch (PaperlessApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new PaperlessConnectionException(
                'Failed to upload file to Paperless-ngx: '.$e->getMessage(),
                $this->client->getBaseUrl(),
                $e->getMessage()
            );
        } finally {
            if (isset($fileStream) && is_resource($fileStream)) {
                fclose($fileStream);
            }
        }
    }

    public function getTaskByUUID(string $taskId): array
    {
        return $this->client->jsonGet('/api/tasks/', ['task_id' => $taskId]);
    }

    public function updateDocument(int $id, array $data): array
    {
        return $this->client->jsonPut("/api/documents/{$id}/", $data);
    }

    public function patchDocument(int $id, array $data): array
    {
        return $this->client->jsonPatch("/api/documents/{$id}/", $data);
    }

    public function deleteDocument(int $id): bool
    {
        return $this->client->successfulDelete("/api/documents/{$id}/");
    }

    public function downloadDocument(int $id, bool $original = false): string
    {
        $params = $original ? ['original' => 'true'] : [];

        return $this->client->bodyGet("/api/documents/{$id}/download/", $params);
    }

    public function getDocumentPreview(int $id): string
    {
        return $this->client->bodyGet("/api/documents/{$id}/preview/");
    }

    public function getDocumentThumbnail(int $id): string
    {
        return $this->client->bodyGet("/api/documents/{$id}/thumb/");
    }

    public function getDocumentMetadata(int $id): array
    {
        return $this->client->jsonGet("/api/documents/{$id}/metadata/");
    }

    public function getDocumentSuggestions(int $id): array
    {
        return $this->client->jsonGet("/api/documents/{$id}/suggestions/");
    }

    public function getDocumentNotes(int $id, int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet("/api/documents/{$id}/notes/", [
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    public function addDocumentNote(int $id, string $note): array
    {
        return $this->client->jsonPost("/api/documents/{$id}/notes/", ['note' => $note]);
    }

    public function deleteDocumentNote(int $documentId, int $noteId): bool
    {
        return $this->client->successfulDelete("/api/documents/{$documentId}/notes/?id={$noteId}");
    }

    public function getDocumentHistory(int $id, int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet("/api/documents/{$id}/history/", [
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    public function emailDocument(int $id, array $emailData): array
    {
        return $this->client->jsonPost("/api/documents/{$id}/email/", $emailData);
    }

    public function getDocumentShareLinks(int $id): array
    {
        return $this->client->jsonGet("/api/documents/{$id}/share_links/");
    }

    public function bulkDownloadDocuments(array $documentIds): array
    {
        return $this->client->jsonPost('/api/documents/bulk_download/', ['documents' => $documentIds]);
    }

    public function bulkEditDocuments(array $documentIds, array $editData): array
    {
        return $this->client->jsonPost('/api/documents/bulk_edit/', array_merge(
            ['documents' => $documentIds],
            $editData
        ));
    }

    public function getNextASN(): int
    {
        $data = $this->client->jsonGet('/api/documents/next_asn/');

        foreach (['next_asn', 'next_asn_number', 'asn', 'id'] as $key) {
            if (isset($data[$key]) && is_numeric($data[$key])) {
                return (int) $data[$key];
            }
        }

        if (count($data) === 1) {
            $only = reset($data);
            if (is_numeric($only)) {
                return (int) $only;
            }
        }

        throw new PaperlessApiException(
            'Unexpected response shape from next_asn endpoint',
            500,
            $data,
            500
        );
    }

    public function getDocumentSelectionData(array $documentIds): array
    {
        return $this->client->jsonPost('/api/documents/selection_data/', ['documents' => $documentIds]);
    }

    public function searchDocuments(string $query, bool $dbOnly = false): array
    {
        return $this->client->jsonGet('/api/search/', [
            'query' => $query,
            'db_only' => $dbOnly,
        ]);
    }

    public function getSearchAutocomplete(string $term, int $limit = 10): array
    {
        return $this->client->jsonGet('/api/search/autocomplete/', [
            'term' => $term,
            'limit' => $limit,
        ]);
    }

    private function validateMetadata(array $metadata): void
    {
        foreach ($metadata as $key => $value) {
            if ($value !== null) {
                if (is_array($value)) {
                    if (empty($value)) {
                        throw new PaperlessValidationException(
                            "Metadata field '{$key}' cannot be an empty array",
                            $key,
                            ['empty_array' => "Metadata field '{$key}' cannot be an empty array"]
                        );
                    }
                    foreach ($value as $index => $arrayValue) {
                        if ($arrayValue === null) {
                            throw new PaperlessValidationException(
                                "Metadata field '{$key}[{$index}]' cannot be null",
                                $key,
                                ['null_value' => "Metadata field '{$key}[{$index}]' cannot be null"]
                            );
                        }
                    }
                }
            }
        }
    }

    private function formatMetadataValue(mixed $value): string
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

        return (string) $value;
    }
}
