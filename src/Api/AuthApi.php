<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class AuthApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function obtainToken(string $username, string $password): array
    {
        return $this->client->jsonPost('/api/token/', [
            'username' => $username,
            'password' => $password,
        ]);
    }
}
