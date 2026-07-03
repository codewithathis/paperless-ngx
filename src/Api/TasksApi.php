<?php

namespace Codewithathis\PaperlessNgx\Api;

use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;

/**
 * @internal
 */
final class TasksApi
{
    public function __construct(
        private PaperlessApiClient $client
    ) {}

    public function getTasks(array $filters = []): array
    {
        return $this->client->jsonGetList('/api/tasks/', $filters);
    }

    public function getTaskByUUID(string $taskId): array
    {
        return $this->client->jsonGetList('/api/tasks/', ['task_id' => $taskId]);
    }

    public function acknowledgeTasks(array $taskIds): array
    {
        return $this->client->jsonPost('/api/tasks/acknowledge/', ['tasks' => $taskIds]);
    }
}
