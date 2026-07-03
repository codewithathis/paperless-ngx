<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class WorkflowApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getWorkflows(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/workflows/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function getWorkflow(int $id): array
    {
        return $this->client->jsonGetById('/api/workflows/', $id);
    }

    public function createWorkflow(array $data): array
    {
        return $this->client->jsonPost('/api/workflows/', $data);
    }

    public function updateWorkflow(int $id, array $data): array
    {
        return $this->client->jsonPut("/api/workflows/{$id}/", $data);
    }

    public function patchWorkflow(int $id, array $data): array
    {
        return $this->client->jsonPatch("/api/workflows/{$id}/", $data);
    }

    public function deleteWorkflow(int $id): bool
    {
        return $this->client->successfulDelete("/api/workflows/{$id}/");
    }

    public function getWorkflowTriggers(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/workflow_triggers/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function getWorkflowTrigger(int $id): array
    {
        return $this->client->jsonGetById('/api/workflow_triggers/', $id);
    }

    public function createWorkflowTrigger(array $data): array
    {
        return $this->client->jsonPost('/api/workflow_triggers/', $data);
    }

    public function updateWorkflowTrigger(int $id, array $data): array
    {
        return $this->client->jsonPut("/api/workflow_triggers/{$id}/", $data);
    }

    public function patchWorkflowTrigger(int $id, array $data): array
    {
        return $this->client->jsonPatch("/api/workflow_triggers/{$id}/", $data);
    }

    public function deleteWorkflowTrigger(int $id): bool
    {
        return $this->client->successfulDelete("/api/workflow_triggers/{$id}/");
    }

    public function getWorkflowActions(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->client->jsonGet('/api/workflow_actions/', array_merge($filters, [
            'page' => $page,
            'page_size' => $pageSize,
        ]));
    }

    public function getWorkflowAction(int $id): array
    {
        return $this->client->jsonGetById('/api/workflow_actions/', $id);
    }

    public function createWorkflowAction(array $data): array
    {
        return $this->client->jsonPost('/api/workflow_actions/', $data);
    }

    public function updateWorkflowAction(int $id, array $data): array
    {
        return $this->client->jsonPut("/api/workflow_actions/{$id}/", $data);
    }

    public function patchWorkflowAction(int $id, array $data): array
    {
        return $this->client->jsonPatch("/api/workflow_actions/{$id}/", $data);
    }

    public function deleteWorkflowAction(int $id): bool
    {
        return $this->client->successfulDelete("/api/workflow_actions/{$id}/");
    }
}
