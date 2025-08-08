<?php

use Illuminate\Support\Facades\Route;
use Codewithathis\PaperlessNgx\Http\Controllers\PaperlessController;

Route::prefix('paperless')->group(function () {
    // Connection test
    Route::get('/test-connection', [PaperlessController::class, 'testConnection']);

    // Documents
    Route::get('/documents', [PaperlessController::class, 'getDocuments']);
    Route::get('/documents/{id}', [PaperlessController::class, 'getDocument']);
    Route::post('/documents', [PaperlessController::class, 'uploadDocument']);
    Route::put('/documents/{id}', [PaperlessController::class, 'updateDocument']);
    Route::delete('/documents/{id}', [PaperlessController::class, 'deleteDocument']);
    Route::get('/documents/{id}/download', [PaperlessController::class, 'downloadDocument']);

    // Search
    Route::get('/search', [PaperlessController::class, 'searchDocuments']);

    // Tags
    Route::get('/tags', [PaperlessController::class, 'getTags']);

    // Correspondents
    Route::get('/correspondents', [PaperlessController::class, 'getCorrespondents']);

    // Document Types
    Route::get('/document-types', [PaperlessController::class, 'getDocumentTypes']);

    // Statistics
    Route::get('/statistics', [PaperlessController::class, 'getStatistics']);

    // Bulk operations
    Route::post('/bulk-edit', [PaperlessController::class, 'bulkEditDocuments']);
});
