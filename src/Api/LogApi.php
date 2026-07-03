<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class LogApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getLogs(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/logs/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function getLog(int $id): array
    {
        return $this->client->jsonGetById('/api/logs/', $id);
    }
}
