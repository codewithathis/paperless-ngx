<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class CorrespondentApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getCorrespondents(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/correspondents/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function createCorrespondent(array $correspondentData): array
    {
        return $this->client->jsonPost('/api/correspondents/', $correspondentData);
    }

    public function updateCorrespondent(int $id, array $correspondentData): array
    {
        return $this->client->jsonPut("/api/correspondents/{$id}/", $correspondentData);
    }

    public function deleteCorrespondent(int $id): bool
    {
        return $this->client->successfulDelete("/api/correspondents/{$id}/");
    }
}
