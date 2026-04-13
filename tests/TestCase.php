<?php

namespace Codewithathis\PaperlessNgx\Tests;

use Codewithathis\PaperlessNgx\PaperlessServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [PaperlessServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('paperless.base_url', 'http://paperless.test');
        $app['config']->set('paperless.auth.token', 'test-token');
        $app['config']->set('paperless.auth.username', null);
        $app['config']->set('paperless.auth.password', null);
        $app['config']->set('paperless.auth.method', 'token');
        $app['config']->set('paperless.defaults.timeout', 5);
        $app['config']->set('paperless.defaults.retry_attempts', 0);
        $app['config']->set('paperless.logging.enabled', false);
    }
}
