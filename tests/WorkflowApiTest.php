<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Support\Facades\Http;

class WorkflowApiTest extends TestCase
{
    public function test_workflow_crud_round_trip(): void
    {
        Http::fake([
            'paperless.test/api/workflows/*' => Http::sequence()
                ->push(['count' => 0, 'results' => []], 200)
                ->push(['id' => 1, 'name' => 'Test'], 201)
                ->push(['id' => 1, 'name' => 'Test'], 200)
                ->push(['id' => 1, 'name' => 'Updated'], 200)
                ->push('', 204),
        ]);

        $svc = $this->app->make(PaperlessService::class);

        $this->assertSame(0, $svc->getWorkflows()['count']);
        $this->assertSame(1, $svc->createWorkflow(['name' => 'Test'])['id']);
        $this->assertSame(1, $svc->getWorkflow(1)['id']);
        $this->assertSame('Updated', $svc->updateWorkflow(1, ['name' => 'Updated'])['name']);
        $this->assertTrue($svc->deleteWorkflow(1));
    }

    public function test_workflow_trigger_create(): void
    {
        Http::fake([
            'paperless.test/api/workflow_triggers/*' => Http::response(['id' => 5], 201),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $this->assertSame(5, $svc->createWorkflowTrigger(['type' => 1])['id']);
    }

    public function test_workflow_action_create(): void
    {
        Http::fake([
            'paperless.test/api/workflow_actions/*' => Http::response(['id' => 7], 201),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $this->assertSame(7, $svc->createWorkflowAction(['type' => 2])['id']);
    }
}
