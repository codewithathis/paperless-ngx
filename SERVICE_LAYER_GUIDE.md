# Paperless-ngx Laravel Package - API Reference

## ⚠️ API Endpoints Removed

**The Paperless-ngx Laravel package no longer provides its own API endpoints.**

This package is designed to be used as a **service layer** within your Laravel application, not as a standalone API provider.

## Service Layer Reference

### PaperlessService Class

The main service class that handles all Paperless-ngx interactions:

```php
use Codewithathis\PaperlessNgx\PaperlessService;

$paperlessService = app(PaperlessService::class);
```

#### Available Methods

- `testConnection()` - Test connection to Paperless-ngx
- `getDocuments($filters = [], $page = 1, $pageSize = 25)` - Get documents
- `getDocument($id)` - Get single document
- `uploadDocument($file, $metadata = [])` - Upload document
- `updateDocument($id, $metadata)` - Update document
- `deleteDocument($id)` - Delete document
- `downloadDocument($id, $original = false)` - Download document
- `searchDocuments($query, $dbOnly = false)` - Search documents
- `getTags($filters = [], $page = 1, $pageSize = 25)` - Get tags
- `getCorrespondents($filters = [], $page = 1, $pageSize = 25)` - Get correspondents
- `getDocumentTypes($filters = [], $page = 1, $pageSize = 25)` - Get document types
- `getStatistics()` - Get system statistics
- `bulkEditDocuments($documentIds, $metadata)` - Bulk edit documents

### Facade Usage

```php
use Codewithathis\PaperlessNgx\Facades\Paperless;

// All service methods are available via facade
$documents = Paperless::getDocuments();
$isConnected = Paperless::testConnection();
```

## Creating Your Own API Endpoints

If you need to expose Paperless-ngx functionality via API endpoints, you can create your own routes in your Laravel application:

```php
// In routes/api.php
Route::prefix('paperless')->group(function () {
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{id}', [DocumentController::class, 'show']);
    Route::put('/documents/{id}', [DocumentController::class, 'update']);
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
    Route::get('/documents/{id}/download', [DocumentController::class, 'download']);
    Route::get('/search', [DocumentController::class, 'search']);
    Route::get('/tags', [DocumentController::class, 'tags']);
    Route::get('/correspondents', [DocumentController::class, 'correspondents']);
    Route::get('/document-types', [DocumentController::class, 'documentTypes']);
    Route::get('/statistics', [DocumentController::class, 'statistics']);
    Route::post('/bulk-edit', [DocumentController::class, 'bulkEdit']);
});
```

## Example Controller Implementation

```php
<?php

namespace App\Http\Controllers;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    protected $paperlessService;
    
    public function __construct(PaperlessService $paperlessService)
    {
        $this->paperlessService = $paperlessService;
    }
    
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search', 'title__icontains', 'content__icontains',
                'correspondent__id', 'document_type__id', 'tags__id',
                'created__gte', 'created__lte', 'added__gte', 'added__lte'
            ]);
            
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 25);
            
            $documents = $this->paperlessService->getDocuments($filters, $page, $pageSize);
            
            return response()->json([
                'success' => true,
                'data' => $documents
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function show(int $id): JsonResponse
    {
        try {
            $document = $this->paperlessService->getDocument($id);
            
            return response()->json([
                'success' => true,
                'data' => $document
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'document' => 'required|file|max:51200', // 50MB max
                'title' => 'nullable|string|max:255',
                'correspondent' => 'nullable|integer',
                'document_type' => 'nullable|integer',
                'tags' => 'nullable|array',
                'tags.*' => 'integer',
                'storage_path' => 'nullable|integer',
                'archive_serial_number' => 'nullable|string'
            ]);
            
            $metadata = $request->except('document');
            $document = $request->file('document');
            
            $result = $this->paperlessService->uploadDocument($document, $metadata);
            
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document_id' => $result['id']
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'nullable|string|max:255',
                'correspondent' => 'nullable|integer',
                'document_type' => 'nullable|integer',
                'tags' => 'nullable|array',
                'tags.*' => 'integer',
                'storage_path' => 'nullable|integer',
                'archive_serial_number' => 'nullable|string'
            ]);
            
            $metadata = $request->all();
            $document = $this->paperlessService->updateDocument($id, $metadata);
            
            return response()->json([
                'success' => true,
                'message' => 'Document updated successfully',
                'data' => $document
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->paperlessService->deleteDocument($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function download(Request $request, int $id): JsonResponse
    {
        try {
            $original = $request->get('original', false);
            $result = $this->paperlessService->downloadDocument($id, $original);
            
            return response()->json([
                'success' => true,
                'data' => $result['content'],
                'size' => $result['size']
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:1',
                'db_only' => 'nullable|boolean'
            ]);
            
            $query = $request->get('query');
            $dbOnly = $request->get('db_only', false);
            
            $results = $this->paperlessService->searchDocuments($query, $dbOnly);
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function tags(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['name__icontains', 'id__in']);
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 25);
            
            $tags = $this->paperlessService->getTags($filters, $page, $pageSize);
            
            return response()->json([
                'success' => true,
                'data' => $tags
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function correspondents(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['name__icontains', 'id__in']);
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 25);
            
            $correspondents = $this->paperlessService->getCorrespondents($filters, $page, $pageSize);
            
            return response()->json([
                'success' => true,
                'data' => $correspondents
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function documentTypes(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['name__icontains', 'id__in']);
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 25);
            
            $documentTypes = $this->paperlessService->getDocumentTypes($filters, $page, $pageSize);
            
            return response()->json([
                'success' => true,
                'data' => $documentTypes
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->paperlessService->getStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function bulkEdit(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'documents' => 'required|array|min:1',
                'documents.*' => 'integer',
                'title' => 'nullable|string|max:255',
                'correspondent' => 'nullable|integer',
                'document_type' => 'nullable|integer',
                'tags' => 'nullable|array',
                'tags.*' => 'integer',
                'storage_path' => 'nullable|integer'
            ]);
            
            $documentIds = $request->get('documents');
            $metadata = $request->except('documents');
            
            $result = $this->paperlessService->bulkEditDocuments($documentIds, $metadata);
            
            return response()->json([
                'success' => true,
                'message' => 'Documents updated successfully',
                'data' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

## Why APIs Were Removed

The package was simplified to focus on its core purpose: providing a clean service layer for Paperless-ngx integration. This approach:

- **Reduces complexity** - No need to manage authentication, middleware, or route conflicts
- **Increases flexibility** - You can implement your own API design and authentication
- **Better separation of concerns** - The package handles Paperless-ngx communication, you handle your API design
- **Easier maintenance** - Fewer moving parts and potential conflicts

## Support

For questions about using the service layer or creating your own API endpoints, please refer to the main README.md file or create an issue in the package repository.
