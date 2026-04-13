<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class StoragePathApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getStoragePaths(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/storage_paths/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function createStoragePath(array $storagePathData): array
    {
        return $this->client->jsonPost('/api/storage_paths/', $storagePathData);
    }

    public function updateStoragePath(int $id, array $storagePathData): array
    {
        return $this->client->jsonPut("/api/storage_paths/{$id}/", $storagePathData);
    }

    public function deleteStoragePath(int $id): bool
    {
        return $this->client->successfulDelete("/api/storage_paths/{$id}/");
    }
}
