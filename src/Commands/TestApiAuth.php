<?php

namespace Codewithathis\PaperlessNgx\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestApiAuth extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'paperless:test-auth 
                            {--method= : Authentication method to test (sanctum, token, basic, none)}
                            {--token= : API token to test with}
                            {--username= : Username for basic auth}
                            {--password= : Password for basic auth}';

    /**
     * The console command description.
     */
    protected $description = 'Test Paperless-ngx API authentication configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $config = config('paperless.api_auth');
        $method = $this->option('method') ?: $config['method'];

        $this->info('Testing Paperless-ngx API Authentication');
        $this->newLine();

        // Display current configuration
        $this->info('Current Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Authentication Enabled', $config['enabled'] ? 'Yes' : 'No'],
                ['Method', $method],
                ['Rate Limiting', $config['rate_limit']['enabled'] ? 'Yes' : 'No'],
                ['IP Whitelist', $config['ip_whitelist'] ?: 'None'],
            ]
        );

        if (!$config['enabled']) {
            $this->warn('Authentication is disabled. API is open to all requests.');
            return self::SUCCESS;
        }

        $this->newLine();

        // Test different authentication methods
        switch ($method) {
            case 'sanctum':
                return $this->testSanctumAuth();
            case 'token':
                return $this->testTokenAuth();
            case 'basic':
                return $this->testBasicAuth();
            case 'none':
                return $this->testNoAuth();
            default:
                $this->error("Unknown authentication method: {$method}");
                return self::FAILURE;
        }
    }

    /**
     * Test Sanctum authentication
     */
    private function testSanctumAuth(): int
    {
        $this->info('Testing Sanctum Authentication...');
        
        try {
            // This would require a proper Sanctum setup
            $this->warn('Sanctum authentication requires a proper Laravel Sanctum setup.');
            $this->line('To test Sanctum:');
            $this->line('1. Ensure Laravel Sanctum is installed and configured');
            $this->line('2. Create a user and generate a Sanctum token');
            $this->line('3. Use the token in API requests');
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Sanctum authentication test failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Test token authentication
     */
    private function testTokenAuth(): int
    {
        $this->info('Testing Token Authentication...');
        
        $config = config('paperless.api_auth');
        $testToken = $this->option('token');
        $configuredTokens = array_filter(explode(',', $config['token']['tokens']));
        
        if (empty($configuredTokens) && !$testToken) {
            $this->error('No API tokens configured. Use --token option or configure PAPERLESS_API_TOKENS.');
            return self::FAILURE;
        }

        if ($testToken) {
            $tokens = [$testToken];
        } else {
            $tokens = $configuredTokens;
        }

        $this->line('Testing with tokens: ' . implode(', ', array_map(fn($t) => substr($t, 0, 8) . '...', $tokens)));

        foreach ($tokens as $token) {
            $this->line("Testing token: " . substr($token, 0, 8) . '...');
            
            try {
                $response = Http::withHeaders([
                    $config['token']['header_name'] => $token
                ])->get(url('/api/paperless/test-connection'));

                if ($response->successful()) {
                    $this->info('✓ Token authentication successful');
                    return self::SUCCESS;
                } else {
                    $this->warn('✗ Token authentication failed: ' . $response->status());
                }
            } catch (\Exception $e) {
                $this->error('✗ Token authentication error: ' . $e->getMessage());
            }
        }

        $this->error('All token authentication tests failed');
        return self::FAILURE;
    }

    /**
     * Test basic authentication
     */
    private function testBasicAuth(): int
    {
        $this->info('Testing Basic Authentication...');
        
        $config = config('paperless.api_auth');
        $username = $this->option('username') ?: $config['basic']['username'];
        $password = $this->option('password') ?: $config['basic']['password'];

        if (!$username || !$password) {
            $this->error('Basic auth credentials not configured. Use --username and --password options or configure PAPERLESS_API_USERNAME and PAPERLESS_API_PASSWORD.');
            return self::FAILURE;
        }

        $this->line("Testing with username: {$username}");

        try {
            $response = Http::withBasicAuth($username, $password)
                ->get(url('/api/paperless/test-connection'));

            if ($response->successful()) {
                $this->info('✓ Basic authentication successful');
                return self::SUCCESS;
            } else {
                $this->error('✗ Basic authentication failed: ' . $response->status());
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('✗ Basic authentication error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Test no authentication
     */
    private function testNoAuth(): int
    {
        $this->info('Testing No Authentication...');
        
        try {
            $response = Http::get(url('/api/paperless/test-connection'));

            if ($response->successful()) {
                $this->info('✓ No authentication successful (API is open)');
                return self::SUCCESS;
            } else {
                $this->error('✗ No authentication failed: ' . $response->status());
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('✗ No authentication error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
