<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class GroupApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getGroups(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/groups/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function getGroup(int $id): array
    {
        return $this->client->jsonGetById('/api/groups/', $id);
    }

    public function createGroup(array $data): array
    {
        return $this->client->jsonPost('/api/groups/', $data);
    }

    public function updateGroup(int $id, array $data): array
    {
        return $this->client->jsonPut("/api/groups/{$id}/", $data);
    }

    public function patchGroup(int $id, array $data): array
    {
        return $this->client->jsonPatch("/api/groups/{$id}/", $data);
    }

    public function deleteGroup(int $id): bool
    {
        return $this->client->successfulDelete("/api/groups/{$id}/");
    }
}
