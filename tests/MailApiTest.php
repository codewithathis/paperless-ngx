<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Support\Facades\Http;

class MailApiTest extends TestCase
{
    public function test_mail_account_crud_smoke(): void
    {
        Http::fake([
            'paperless.test/api/mail_accounts/*' => Http::sequence()
                ->push(['count' => 0, 'results' => []], 200)
                ->push(['id' => 1, 'name' => 'Inbox'], 201),
        ]);

        $svc = $this->app->make(PaperlessService::class);

        $this->assertSame(0, $svc->getMailAccounts()['count']);
        $this->assertSame(1, $svc->createMailAccount(['name' => 'Inbox'])['id']);
    }

    public function test_mail_rule_crud_smoke(): void
    {
        Http::fake([
            'paperless.test/api/mail_rules/*' => Http::response(['id' => 2, 'name' => 'Rule'], 201),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $this->assertSame(2, $svc->createMailRule(['name' => 'Rule'])['id']);
    }

    public function test_processed_mail_list(): void
    {
        Http::fake([
            'paperless.test/api/processed_mail/*' => Http::response(['count' => 1, 'results' => [['id' => 1]]], 200),
        ]);

        $svc = $this->app->make(PaperlessService::class);
        $this->assertSame(1, $svc->getProcessedMail()['count']);
    }
}
