<?php

namespace Codewithathis\PaperlessNgx\Http\Controllers;

use Codewithathis\PaperlessNgx\PaperlessService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Exception;

class PaperlessController extends BaseController
{
    protected PaperlessService $paperlessService;

    public function __construct(PaperlessService $paperlessService)
    {
        $this->paperlessService = $paperlessService;
    }

    /**
     * Test the connection to Paperless-ngx
     */
    public function testConnection(): JsonResponse
    {
        try {
            $isConnected = $this->paperlessService->testConnection();

            if ($isConnected) {
                $status = $this->paperlessService->getStatus();
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully connected to Paperless-ngx',
                    'status' => $status,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to Paperless-ngx',
                ], 500);
            }
        } catch (Exception $e) {
            Log::error('Paperless connection test failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get documents with optional filters
     */
    public function getDocuments(Request $request): JsonResponse
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
                'data' => $documents,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get documents', [
                'error' => $e->getMessage(),
                'filters' => $filters ?? [],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get documents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single document
     */
    public function getDocument(int $id): JsonResponse
    {
        try {
            $document = $this->paperlessService->getDocument($id);

            return response()->json([
                'success' => true,
                'data' => $document,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get document', [
                'error' => $e->getMessage(),
                'document_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload a document
     */
    public function uploadDocument(Request $request): JsonResponse
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
                'archive_serial_number' => 'nullable|integer',
            ]);

            $file = $request->file('document');
            $metadata = $request->only([
                'title', 'correspondent', 'document_type',
                'tags', 'storage_path', 'archive_serial_number'
            ]);

            $documentId = $this->paperlessService->uploadDocument($file, $metadata);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document_id' => $documentId,
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to upload document', [
                'error' => $e->getMessage(),
                'file' => $request->file('document')?->getClientOriginalName(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a document
     */
    public function updateDocument(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'nullable|string|max:255',
                'correspondent' => 'nullable|integer',
                'document_type' => 'nullable|integer',
                'tags' => 'nullable|array',
                'tags.*' => 'integer',
                'storage_path' => 'nullable|integer',
                'archive_serial_number' => 'nullable|integer',
            ]);

            $data = $request->only([
                'title', 'correspondent', 'document_type',
                'tags', 'storage_path', 'archive_serial_number'
            ]);

            $document = $this->paperlessService->updateDocument($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Document updated successfully',
                'data' => $document,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update document', [
                'error' => $e->getMessage(),
                'document_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a document
     */
    public function deleteDocument(int $id): JsonResponse
    {
        try {
            $deleted = $this->paperlessService->deleteDocument($id);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document deleted successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete document',
                ], 500);
            }
        } catch (Exception $e) {
            Log::error('Failed to delete document', [
                'error' => $e->getMessage(),
                'document_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a document
     */
    public function downloadDocument(int $id, Request $request): JsonResponse
    {
        try {
            $original = $request->get('original', false);
            $content = $this->paperlessService->downloadDocument($id, $original);

            return response()->json([
                'success' => true,
                'data' => base64_encode($content),
                'size' => strlen($content),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to download document', [
                'error' => $e->getMessage(),
                'document_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to download document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search documents
     */
    public function searchDocuments(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:1',
                'db_only' => 'nullable|boolean',
            ]);

            $query = $request->get('query');
            $dbOnly = $request->get('db_only', false);

            $results = $this->paperlessService->searchDocuments($query, $dbOnly);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to search documents', [
                'error' => $e->getMessage(),
                'query' => $request->get('query'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to search documents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get tags
     */
    public function getTags(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['name__icontains', 'id__in']);
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 25);

            $tags = $this->paperlessService->getTags($filters, $page, $pageSize);

            return response()->json([
                'success' => true,
                'data' => $tags,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get tags', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get tags: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get correspondents
     */
    public function getCorrespondents(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['name__icontains', 'id__in']);
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 25);

            $correspondents = $this->paperlessService->getCorrespondents($filters, $page, $pageSize);

            return response()->json([
                'success' => true,
                'data' => $correspondents,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get correspondents', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get correspondents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get document types
     */
    public function getDocumentTypes(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['name__icontains', 'id__in']);
            $page = $request->get('page', 1);
            $pageSize = $request->get('page_size', 25);

            $documentTypes = $this->paperlessService->getDocumentTypes($filters, $page, $pageSize);

            return response()->json([
                'success' => true,
                'data' => $documentTypes,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get document types', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get document types: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->paperlessService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get statistics', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk edit documents
     */
    public function bulkEditDocuments(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'documents' => 'required|array',
                'documents.*' => 'integer',
                'title' => 'nullable|string|max:255',
                'correspondent' => 'nullable|integer',
                'document_type' => 'nullable|integer',
                'tags' => 'nullable|array',
                'tags.*' => 'integer',
                'storage_path' => 'nullable|integer',
            ]);

            $documentIds = $request->get('documents');
            $editData = $request->only([
                'title', 'correspondent', 'document_type',
                'tags', 'storage_path'
            ]);

            $result = $this->paperlessService->bulkEditDocuments($documentIds, $editData);

            return response()->json([
                'success' => true,
                'message' => 'Documents updated successfully',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to bulk edit documents', [
                'error' => $e->getMessage(),
                'document_ids' => $request->get('documents'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk edit documents: ' . $e->getMessage(),
            ], 500);
        }
    }
}
