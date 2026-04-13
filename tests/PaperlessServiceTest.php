<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Support\Facades\Http;

class PaperlessServiceTest extends TestCase
{
    public function test_get_documents_delegates_to_http(): void
    {
        Http::fake([
            'paperless.test/api/documents*' => Http::response([
                'count' => 0,
                'results' => [],
            ], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $data = $svc->getDocuments([], 1, 25);

        $this->assertSame(0, $data['count']);
        Http::assertSent(function ($request) {
            $auth = $request->header('Authorization')[0] ?? '';

            return str_contains($request->url(), 'paperless.test/api/documents')
                && str_contains($auth, 'Token test-token');
        });
    }

    public function test_token_auth_used_when_method_is_token_even_with_basic_credentials_in_config(): void
    {
        config([
            'paperless.auth.token' => 'tok',
            'paperless.auth.username' => 'u',
            'paperless.auth.password' => 'p',
            'paperless.auth.method' => 'token',
        ]);

        Http::fake([
            'paperless.test/api/status*' => Http::response(['pngx_version' => '2.0.0'], 200),
        ]);

        $this->app->forgetInstance(PaperlessService::class);
        $svc = $this->app->make(PaperlessService::class);
        $this->assertTrue($svc->testConnection());

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Token tok')
                && ! str_contains($request->header('Authorization')[0] ?? '', 'Basic');
        });
    }

    public function test_get_next_asn_parses_numeric_payload(): void
    {
        Http::fake([
            'paperless.test/api/documents/next_asn*' => Http::response(['next_asn' => 42], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $this->assertSame(42, $svc->getNextASN());
    }
}
