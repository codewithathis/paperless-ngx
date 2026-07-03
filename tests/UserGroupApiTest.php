<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Support\Facades\Http;

class UserGroupApiTest extends TestCase
{
    public function test_users_list_and_create(): void
    {
        Http::fake([
            'paperless.test/api/users/*' => Http::sequence()
                ->push(['count' => 0, 'results' => []], 200)
                ->push(['id' => 1, 'username' => 'bob'], 201),
        ]);

        $svc = $this->app->make(PaperlessService::class);

        $this->assertSame(0, $svc->getUsers()['count']);
        $this->assertSame('bob', $svc->createUser(['username' => 'bob'])['username']);
    }

    public function test_groups_list_and_create(): void
    {
        Http::fake([
            'paperless.test/api/groups/*' => Http::sequence()
                ->push(['count' => 0, 'results' => []], 200)
                ->push(['id' => 1, 'name' => 'Editors'], 201),
        ]);

        $svc = $this->app->make(PaperlessService::class);

        $this->assertSame(0, $svc->getGroups()['count']);
        $this->assertSame('Editors', $svc->createGroup(['name' => 'Editors'])['name']);
    }

    public function test_logs_list(): void
    {
        Http::fake([
            'paperless.test/api/logs/*' => Http::response(['count' => 1, 'results' => [['id' => 1]]], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $this->assertSame(1, $svc->getLogs()['count']);
    }

    public function test_config_get_and_patch(): void
    {
        Http::fake([
            'paperless.test/api/config/*' => Http::sequence()
                ->push(['count' => 1, 'results' => [['id' => 1]]], 200)
                ->push(['id' => 1, 'app_title' => 'Docs'], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);

        $this->assertSame(1, $svc->getConfig()['count']);
        $this->assertSame('Docs', $svc->patchConfig(1, ['app_title' => 'Docs'])['app_title']);
    }
}
