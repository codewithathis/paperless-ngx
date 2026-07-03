<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Support\Facades\Http;

class BulkApiTest extends TestCase
{
    public function test_bulk_edit_objects_forwards_payload(): void
    {
        Http::fake([
            'paperless.test/api/bulk_edit_objects/*' => Http::response(['result' => 'OK'], 200),
        ]);

        $payload = [
            'objects' => [1, 2],
            'object_type' => 'tags',
            'operation' => 'delete',
        ];

        $svc = $this->app->make(PaperlessService::class);
        $data = $svc->bulkEditObjects($payload);

        $this->assertSame('OK', $data['result']);

        Http::assertSent(function ($request) use ($payload) {
            return str_contains($request->url(), '/api/bulk_edit_objects/')
                && $request->data() === $payload;
        });
    }
}
