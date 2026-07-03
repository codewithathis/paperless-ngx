<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class UserApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getUsers(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/users/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function getUser(int $id): array
    {
        return $this->client->jsonGetById('/api/users/', $id);
    }

    public function createUser(array $data): array
    {
        return $this->client->jsonPost('/api/users/', $data);
    }

    public function updateUser(int $id, array $data): array
    {
        return $this->client->jsonPut("/api/users/{$id}/", $data);
    }

    public function patchUser(int $id, array $data): array
    {
        return $this->client->jsonPatch("/api/users/{$id}/", $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->client->successfulDelete("/api/users/{$id}/");
    }
}
