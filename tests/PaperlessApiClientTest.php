<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Support\Facades\Http;

class PaperlessApiClientTest extends TestCase
{
    public function test_api_version_accept_header_is_sent_when_configured(): void
    {
        config(['paperless.api_version' => 9]);

        Http::fake([
            'paperless.test/api/status*' => Http::response([], 200),
        ]);

        $this->app->forgetInstance(PaperlessService::class);
        $svc = $this->app->make(PaperlessService::class);
        $svc->getStatus();

        Http::assertSent(function ($request) {
            $accept = $request->header('Accept')[0] ?? '';

            return $accept === 'application/json; version=9';
        });
    }

    public function test_set_api_version_at_runtime(): void
    {
        Http::fake([
            'paperless.test/api/status*' => Http::response([], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $svc->setApiVersion(6)->getStatus();

        Http::assertSent(function ($request) {
            $accept = $request->header('Accept')[0] ?? '';

            return $accept === 'application/json; version=6';
        });
    }
}
