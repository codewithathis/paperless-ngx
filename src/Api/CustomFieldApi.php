<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class CustomFieldApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getCustomFields(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/custom_fields/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function createCustomField(array $customFieldData): array
    {
        return $this->client->jsonPost('/api/custom_fields/', $customFieldData);
    }

    public function updateCustomField(int $id, array $customFieldData): array
    {
        return $this->client->jsonPut("/api/custom_fields/{$id}/", $customFieldData);
    }

    public function deleteCustomField(int $id): bool
    {
        return $this->client->successfulDelete("/api/custom_fields/{$id}/");
    }
}
