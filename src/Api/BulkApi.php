<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class BulkApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function bulkEditObjects(array $payload): array
    {
        return $this->client->jsonPost('/api/bulk_edit_objects/', $payload);
    }
}
