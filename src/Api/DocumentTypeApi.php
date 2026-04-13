<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class DocumentTypeApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getDocumentTypes(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/document_types/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function createDocumentType(array $documentTypeData): array
    {
        return $this->client->jsonPost('/api/document_types/', $documentTypeData);
    }

    public function updateDocumentType(int $id, array $documentTypeData): array
    {
        return $this->client->jsonPut("/api/document_types/{$id}/", $documentTypeData);
    }

    public function deleteDocumentType(int $id): bool
    {
        return $this->client->successfulDelete("/api/document_types/{$id}/");
    }
}
