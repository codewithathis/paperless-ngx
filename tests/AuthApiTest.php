<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Support\Facades\Http;

class AuthApiTest extends TestCase
{
    public function test_obtain_token_posts_credentials(): void
    {
        Http::fake([
            'paperless.test/api/token/*' => Http::response(['token' => 'new-token'], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $data = $svc->obtainToken('admin', 'secret');

        $this->assertSame('new-token', $data['token']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/token/')
                && $request['username'] === 'admin'
                && $request['password'] === 'secret';
        });
    }
}
