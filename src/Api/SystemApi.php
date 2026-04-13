<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class SystemApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getStatus(): array
    {
        return $this->client->jsonGet('/api/status/');
    }

    public function getRemoteVersion(): array
    {
        return $this->client->jsonGet('/api/remote_version/');
    }

    public function getProfile(): array
    {
        return $this->client->jsonGet('/api/profile/');
    }

    public function generateAuthToken(): array
    {
        return $this->client->jsonPost('/api/profile/generate_auth_token/');
    }

    public function getStatistics(): array
    {
        return $this->client->jsonGet('/api/statistics/');
    }

    public function getSavedViews(int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/saved_views/', [
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    public function createSavedView(array $savedViewData): array
    {
        return $this->client->jsonPost('/api/saved_views/', $savedViewData);
    }

    public function updateSavedView(int $id, array $savedViewData): array
    {
        return $this->client->jsonPut("/api/saved_views/{$id}/", $savedViewData);
    }

    public function deleteSavedView(int $id): bool
    {
        return $this->client->successfulDelete("/api/saved_views/{$id}/");
    }
}
