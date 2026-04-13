<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Support\Facades\Http;

class PaperlessServiceConstructorTest extends TestCase
{
    public function test_auto_auth_prefers_basic_when_username_and_password_set(): void
    {
        Http::fake([
            'paperless.test/api/status*' => Http::response([], 200),
        ]);

        $svc = new PaperlessService('http://paperless.test', 'ignored-token', 'user', 'pass', 'auto');
        $svc->getStatus();

        Http::assertSent(function ($request) {
            $auth = $request->header('Authorization')[0] ?? '';

            return str_starts_with($auth, 'Basic ');
        });
    }

    public function test_explicit_basic_auth_mode(): void
    {
        Http::fake([
            'paperless.test/api/status*' => Http::response([], 200),
        ]);

        $svc = new PaperlessService('http://paperless.test', 'tok', 'user', 'pass', 'basic');
        $svc->getStatus();

        Http::assertSent(function ($request) {
            $auth = $request->header('Authorization')[0] ?? '';

            return str_starts_with($auth, 'Basic ');
        });
    }
}
