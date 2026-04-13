<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class ShareLinkApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getShareLinks(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/share_links/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function createShareLink(array $shareLinkData): array
    {
        return $this->client->jsonPost('/api/share_links/', $shareLinkData);
    }

    public function updateShareLink(int $id, array $shareLinkData): array
    {
        return $this->client->jsonPut("/api/share_links/{$id}/", $shareLinkData);
    }

    public function deleteShareLink(int $id): bool
    {
        return $this->client->successfulDelete("/api/share_links/{$id}/");
    }
}
