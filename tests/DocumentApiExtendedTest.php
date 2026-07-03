<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Support\Facades\Http;

class DocumentApiExtendedTest extends TestCase
{
    public function test_search_documents_via_documents_endpoint(): void
    {
        Http::fake([
            'paperless.test/api/documents*' => function ($request) {
                $this->assertSame('invoice', $request->data()['query']);

                return Http::response(['count' => 1, 'results' => [['id' => 1]]], 200);
            },
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $data = $svc->searchDocumentsViaDocuments('invoice');

        $this->assertSame(1, $data['count']);
    }

    public function test_get_similar_documents(): void
    {
        Http::fake([
            'paperless.test/api/documents*' => function ($request) {
                $this->assertSame(42, $request->data()['more_like_id']);

                return Http::response(['count' => 0, 'results' => []], 200);
            },
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $svc->getSimilarDocuments(42);
    }

    public function test_bulk_add_tag_uses_correct_payload(): void
    {
        Http::fake([
            'paperless.test/api/documents/bulk_edit/*' => Http::response(['result' => 'OK'], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $svc->bulkAddTag([1, 2], 5);

        Http::assertSent(function ($request) {
            return $request['documents'] === [1, 2]
                && $request['method'] === 'add_tag'
                && $request['parameters'] === ['tag' => 5];
        });
    }

    public function test_get_trash(): void
    {
        Http::fake([
            'paperless.test/api/trash/*' => Http::response(['count' => 1, 'results' => [['id' => 9]]], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $this->assertSame(1, $svc->getTrash()['count']);
    }
}
