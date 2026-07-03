<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class MailApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getMailAccounts(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/mail_accounts/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function getMailAccount(int $id): array
    {
        return $this->client->jsonGetById('/api/mail_accounts/', $id);
    }

    public function createMailAccount(array $data): array
    {
        return $this->client->jsonPost('/api/mail_accounts/', $data);
    }

    public function updateMailAccount(int $id, array $data): array
    {
        return $this->client->jsonPut("/api/mail_accounts/{$id}/", $data);
    }

    public function patchMailAccount(int $id, array $data): array
    {
        return $this->client->jsonPatch("/api/mail_accounts/{$id}/", $data);
    }

    public function deleteMailAccount(int $id): bool
    {
        return $this->client->successfulDelete("/api/mail_accounts/{$id}/");
    }

    public function getMailRules(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/mail_rules/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function getMailRule(int $id): array
    {
        return $this->client->jsonGetById('/api/mail_rules/', $id);
    }

    public function createMailRule(array $data): array
    {
        return $this->client->jsonPost('/api/mail_rules/', $data);
    }

    public function updateMailRule(int $id, array $data): array
    {
        return $this->client->jsonPut("/api/mail_rules/{$id}/", $data);
    }

    public function patchMailRule(int $id, array $data): array
    {
        return $this->client->jsonPatch("/api/mail_rules/{$id}/", $data);
    }

    public function deleteMailRule(int $id): bool
    {
        return $this->client->successfulDelete("/api/mail_rules/{$id}/");
    }

    public function getProcessedMail(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/processed_mail/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function getProcessedMailItem(int $id): array
    {
        return $this->client->jsonGetById('/api/processed_mail/', $id);
    }
}
