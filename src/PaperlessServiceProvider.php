<?php

namespace Codewithathis\PaperlessNgx;

use Codewithathis\PaperlessNgx\Commands\GenerateApiToken;
use Codewithathis\PaperlessNgx\Commands\TestApiAuth;
use Codewithathis\PaperlessNgx\PaperlessService;
use Codewithathis\PaperlessNgx\Commands\TestPaperlessConnection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PaperlessServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PaperlessService::class, function ($app) {
            $config = config('paperless');

            $baseUrl = $config['base_url'];
            $token = $config['auth']['token'];
            $username = $config['auth']['username'];
            $password = $config['auth']['password'];

            return new PaperlessService($baseUrl, $token, $username, $password);
        });

        // Register as a facade alias for easier access
        $this->app->alias(PaperlessService::class, 'paperless');

        // Register facade
        $this->app->singleton('paperless', function ($app) {
            return $app->make(PaperlessService::class);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../config/paperless.php' => config_path('paperless.php'),
        ], 'paperless-config');

        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/paperless.php',
            'paperless'
        );

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                TestPaperlessConnection::class,
                GenerateApiToken::class,
                TestApiAuth::class,
            ]);
        }

        // Register middleware
        $this->app['router']->aliasMiddleware('paperless.auth', \Codewithathis\PaperlessNgx\Http\Middleware\PaperlessApiAuth::class);

        // Load routes
        Route::middleware('api')->prefix('api')->name('api.')->group(__DIR__ . '/../routes/api.php');
    }
}
