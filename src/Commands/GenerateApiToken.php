<?php

namespace Codewithathis\PaperlessNgx\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'paperless:generate-token 
                            {--name= : Name for the token}
                            {--length=32 : Length of the token}
                            {--show : Show the token in output}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a secure API token for Paperless-ngx API authentication';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?: 'paperless-api-token-' . date('Y-m-d-H-i-s');
        $length = (int) $this->option('length');
        $show = $this->option('show');

        // Generate a secure token
        $token = Str::random($length);

        // Display the token
        $this->info('API Token generated successfully!');
        $this->newLine();
        
        $this->table(
            ['Property', 'Value'],
            [
                ['Name', $name],
                ['Token', $show ? $token : str_repeat('*', $length)],
                ['Length', $length],
                ['Generated At', now()->toDateTimeString()],
            ]
        );

        if (!$show) {
            $this->warn('Token is hidden. Use --show flag to display the token.');
        }

        $this->newLine();
        $this->info('To use this token:');
        $this->line('1. Add it to your .env file:');
        $this->line("   PAPERLESS_API_TOKENS={$token}");
        $this->line('');
        $this->line('2. Or add it to existing tokens (comma-separated):');
        $this->line("   PAPERLESS_API_TOKENS=existing-token,{$token}");
        $this->line('');
        $this->line('3. Set authentication method to token:');
        $this->line('   PAPERLESS_API_AUTH_METHOD=token');
        $this->line('');
        $this->line('4. Use in API requests with header:');
        $this->line("   X-Paperless-Token: {$token}");

        // Save token to a file for backup
        $backupFile = storage_path('paperless-tokens.txt');
        $backupContent = "Token: {$token}\nName: {$name}\nGenerated: " . now()->toDateTimeString() . "\n\n";
        file_put_contents($backupFile, $backupContent, FILE_APPEND | LOCK_EX);

        $this->newLine();
        $this->info("Token backed up to: {$backupFile}");

        return self::SUCCESS;
    }
}
