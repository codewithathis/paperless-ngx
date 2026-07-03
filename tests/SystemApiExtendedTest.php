<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Support\Facades\Http;

class SystemApiExtendedTest extends TestCase
{
    public function test_ui_settings_get_and_patch(): void
    {
        Http::fake([
            'paperless.test/api/ui_settings/*' => Http::sequence()
                ->push(['settings' => ['theme' => 'light']], 200)
                ->push(['settings' => ['theme' => 'dark']], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);

        $this->assertSame('light', $svc->getUiSettings()['settings']['theme']);
        $this->assertSame('dark', $svc->updateUiSettings(['settings' => ['theme' => 'dark']])['settings']['theme']);
    }

    public function test_trash_restore(): void
    {
        Http::fake([
            'paperless.test/api/trash/*' => Http::response(['result' => 'OK', 'doc_ids' => [1]], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $data = $svc->trashAction([1], 'restore');

        $this->assertSame('OK', $data['result']);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request['action'] === 'restore'
                && $request['documents'] === [1];
        });
    }

    public function test_get_tag_by_id(): void
    {
        Http::fake([
            'paperless.test/api/tags/1/*' => Http::response(['id' => 1, 'name' => 'Important'], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $this->assertSame('Important', $svc->getTag(1)['name']);
    }
}
