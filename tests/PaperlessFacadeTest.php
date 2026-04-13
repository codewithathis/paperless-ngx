<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\Facades\Paperless;
use Illuminate\Support\Facades\Http;

class PaperlessFacadeTest extends TestCase
{
    public function test_facade_resolves_container_binding(): void
    {
        Http::fake([
            'paperless.test/api/status*' => Http::response(['pngx_version' => '2.0.0'], 200),
        ]);

        $this->assertTrue(Paperless::testConnection());
    }
}
