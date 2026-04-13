<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class TagApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getTags(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/tags/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function createTag(array $tagData): array
    {
        return $this->client->jsonPost('/api/tags/', $tagData);
    }

    public function updateTag(int $id, array $tagData): array
    {
        return $this->client->jsonPut("/api/tags/{$id}/", $tagData);
    }

    public function deleteTag(int $id): bool
    {
        return $this->client->successfulDelete("/api/tags/{$id}/");
    }
}
