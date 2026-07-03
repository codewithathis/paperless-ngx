<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class ConfigApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getConfig(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/config/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function getConfigItem(int $id): array
    {
        return $this->client->jsonGetById('/api/config/', $id);
    }

    public function updateConfig(int $id, array $data): array
    {
        return $this->client->jsonPut("/api/config/{$id}/", $data);
    }

    public function patchConfig(int $id, array $data): array
    {
        return $this->client->jsonPatch("/api/config/{$id}/", $data);
    }
}
