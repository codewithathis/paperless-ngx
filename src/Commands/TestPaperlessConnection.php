<?php

namespace Codewithathis\PaperlessNgx\Commands;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestPaperlessConnection extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'paperless:test
                            {--upload= : Path to a file to upload for testing}
                            {--search= : Search term to test search functionality}';

    /**
     * The console command description.
     */
    protected $description = 'Test the connection to Paperless-ngx and demonstrate basic functionality';

    /**
     * Execute the console command.
     */
    public function handle(PaperlessService $paperlessService): int
    {
        $this->info('Testing Paperless-ngx connection...');

        try {
            // Test connection
            $isConnected = $paperlessService->testConnection();

            if (!$isConnected) {
                $this->error('Failed to connect to Paperless-ngx');
                return 1;
            }

            $this->info('✅ Successfully connected to Paperless-ngx');

            // Get system status
            $status = $paperlessService->getStatus();
            $this->info('System Status: ' . json_encode($status, JSON_PRETTY_PRINT));

            // Get statistics
            $statistics = $paperlessService->getStatistics();
            $this->info('Statistics: ' . json_encode($statistics, JSON_PRETTY_PRINT));

            // Get recent documents
            $documents = $paperlessService->getDocuments([], 1, 5);
            $this->info('Recent Documents: ' . count($documents['results'] ?? []) . ' found');

            // Test search if provided
            if ($searchTerm = $this->option('search')) {
                $this->info("Searching for: {$searchTerm}");
                $searchResults = $paperlessService->searchDocuments($searchTerm);
                $this->info('Search Results: ' . json_encode($searchResults, JSON_PRETTY_PRINT));
            }

            // Test file upload if provided
            if ($filePath = $this->option('upload')) {
                if (!file_exists($filePath)) {
                    $this->error("File not found: {$filePath}");
                    return 1;
                }

                $this->info("Uploading file: {$filePath}");

                // Create UploadedFile from path
                $file = new \Illuminate\Http\UploadedFile(
                    $filePath,
                    basename($filePath),
                    mime_content_type($filePath),
                    null,
                    true
                );

                $documentId = $paperlessService->uploadDocument($file, [
                    'title' => 'Test Upload - ' . basename($filePath),
                ]);

                $this->info("✅ File uploaded successfully. Document ID: {$documentId}");

                // Get the uploaded document
                $document = $paperlessService->getDocument($documentId);
                $this->info('Uploaded Document: ' . json_encode($document, JSON_PRETTY_PRINT));
            }

            // Get tags
            $tags = $paperlessService->getTags([], 1, 10);
            $this->info('Available Tags: ' . count($tags['results'] ?? []) . ' found');

            // Get correspondents
            $correspondents = $paperlessService->getCorrespondents([], 1, 10);
            $this->info('Available Correspondents: ' . count($correspondents['results'] ?? []) . ' found');

            // Get document types
            $documentTypes = $paperlessService->getDocumentTypes([], 1, 10);
            $this->info('Available Document Types: ' . count($documentTypes['results'] ?? []) . ' found');

            $this->info('✅ All tests completed successfully!');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            Log::error('Paperless test command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
}
