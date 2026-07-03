<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Support\Facades\Http;

class TasksApiTest extends TestCase
{
    public function test_get_tasks_normalizes_bare_array_response(): void
    {
        Http::fake([
            'paperless.test/api/tasks*' => Http::response([
                ['id' => 1, 'task_id' => 'uuid-1', 'status' => 'SUCCESS'],
            ], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $data = $svc->getTasks();

        $this->assertArrayHasKey('results', $data);
        $this->assertCount(1, $data['results']);
    }

    public function test_get_task_by_uuid(): void
    {
        Http::fake([
            'paperless.test/api/tasks*' => function ($request) {
                $this->assertSame('uuid-abc', $request->data()['task_id']);

                return Http::response([
                    ['task_id' => 'uuid-abc', 'status' => 'PENDING'],
                ], 200);
            },
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $data = $svc->getTaskByUUID('uuid-abc');

        $this->assertSame('uuid-abc', $data['results'][0]['task_id']);
    }

    public function test_acknowledge_tasks(): void
    {
        Http::fake([
            'paperless.test/api/tasks/acknowledge/*' => Http::response(['result' => 2], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $data = $svc->acknowledgeTasks([1, 2]);

        $this->assertSame(2, $data['result']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/tasks/acknowledge/')
                && $request['tasks'] === [1, 2];
        });
    }
}
