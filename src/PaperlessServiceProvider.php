<?php

namespace Codewithathis\PaperlessNgx;

use Codewithathis\PaperlessNgx\Commands\TestPaperlessConnection;
use Illuminate\Support\ServiceProvider;

class PaperlessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaperlessService::class, function ($app) {
            $config = config('paperless');

            $authMethod = $config['auth']['method'] ?? 'auto';
            if (! in_array($authMethod, ['token', 'basic', 'auto'], true)) {
                $authMethod = 'auto';
            }

            return new PaperlessService(
                $config['base_url'],
                $config['auth']['token'] ?? null,
                $config['auth']['username'] ?? null,
                $config['auth']['password'] ?? null,
                $authMethod
            );
        });

        $this->app->alias(PaperlessService::class, 'paperless');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/paperless.php' => config_path('paperless.php'),
        ], 'paperless-config');

        $this->mergeConfigFrom(
            __DIR__.'/../config/paperless.php',
            'paperless'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                TestPaperlessConnection::class,
            ]);
        }
    }
}
