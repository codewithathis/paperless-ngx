<?php

namespace Codewithathis\PaperlessNgx;

use Codewithathis\PaperlessNgx\Api\CorrespondentApi;
use Codewithathis\PaperlessNgx\Api\CustomFieldApi;
use Codewithathis\PaperlessNgx\Api\DocumentApi;
use Codewithathis\PaperlessNgx\Api\DocumentTypeApi;
use Codewithathis\PaperlessNgx\Api\ShareLinkApi;
use Codewithathis\PaperlessNgx\Api\StoragePathApi;
use Codewithathis\PaperlessNgx\Api\SystemApi;
use Codewithathis\PaperlessNgx\Api\TagApi;
use Codewithathis\PaperlessNgx\Exceptions\PaperlessApiException;
use Codewithathis\PaperlessNgx\Http\PaperlessApiClient;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Public entry point for the Paperless-ngx API. Method signatures are stable for semver;
 * HTTP and domain logic are implemented in internal Http and Api classes in this package.
 */
class PaperlessService
{
    private PaperlessApiClient $client;

    private DocumentApi $documents;

    private SystemApi $system;

    private TagApi $tags;

    private CorrespondentApi $correspondents;

    private DocumentTypeApi $documentTypes;

    private StoragePathApi $storagePaths;

    private CustomFieldApi $customFields;

    private ShareLinkApi $shareLinks;

    public function __construct(
        string $baseUrl,
        ?string $token = null,
        ?string $username = null,
        ?string $password = null,
        string $authMethod = 'auto'
    ) {
        $this->client = new PaperlessApiClient($baseUrl, $token, $username, $password, $authMethod);
        $this->documents = new DocumentApi($this->client);
        $this->system = new SystemApi($this->client);
        $this->tags = new TagApi($this->client);
        $this->correspondents = new CorrespondentApi($this->client);
        $this->documentTypes = new DocumentTypeApi($this->client);
        $this->storagePaths = new StoragePathApi($this->client);
        $this->customFields = new CustomFieldApi($this->client);
        $this->shareLinks = new ShareLinkApi($this->client);
    }

    public function setToken(string $token): self
    {
        $this->client->setToken($token);

        return $this;
    }

    public function setBasicAuth(string $username, string $password): self
    {
        $this->client->setBasicAuth($username, $password);

        return $this;
    }

    public function getStatus(): array
    {
        return $this->system->getStatus();
    }

    public function getRemoteVersion(): array
    {
        return $this->system->getRemoteVersion();
    }

    public function getProfile(): array
    {
        return $this->system->getProfile();
    }

    public function generateAuthToken(): array
    {
        return $this->system->generateAuthToken();
    }

    public function getDocuments(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->documents->getDocuments($filters, $page, $pageSize);
    }

    public function getDocument(int $id): array
    {
        return $this->documents->getDocument($id);
    }

    public function uploadDocument(UploadedFile $file, array $metadata = []): array
    {
        return $this->documents->uploadDocument($file, $metadata);
    }

    public function getTaskByUUID(string $taskId): array
    {
        return $this->documents->getTaskByUUID($taskId);
    }

    public function updateDocument(int $id, array $data): array
    {
        return $this->documents->updateDocument($id, $data);
    }

    public function patchDocument(int $id, array $data): array
    {
        return $this->documents->patchDocument($id, $data);
    }

    public function deleteDocument(int $id): bool
    {
        return $this->documents->deleteDocument($id);
    }

    public function downloadDocument(int $id, bool $original = false): string
    {
        return $this->documents->downloadDocument($id, $original);
    }

    public function getDocumentPreview(int $id): string
    {
        return $this->documents->getDocumentPreview($id);
    }

    public function getDocumentThumbnail(int $id): string
    {
        return $this->documents->getDocumentThumbnail($id);
    }

    public function getDocumentMetadata(int $id): array
    {
        return $this->documents->getDocumentMetadata($id);
    }

    public function getDocumentSuggestions(int $id): array
    {
        return $this->documents->getDocumentSuggestions($id);
    }

    public function getDocumentNotes(int $id, int $page = 1, int $pageSize = 25): array
    {
        return $this->documents->getDocumentNotes($id, $page, $pageSize);
    }

    public function addDocumentNote(int $id, string $note): array
    {
        return $this->documents->addDocumentNote($id, $note);
    }

    public function deleteDocumentNote(int $documentId, int $noteId): bool
    {
        return $this->documents->deleteDocumentNote($documentId, $noteId);
    }

    public function getDocumentHistory(int $id, int $page = 1, int $pageSize = 25): array
    {
        return $this->documents->getDocumentHistory($id, $page, $pageSize);
    }

    public function emailDocument(int $id, array $emailData): array
    {
        return $this->documents->emailDocument($id, $emailData);
    }

    public function getDocumentShareLinks(int $id): array
    {
        return $this->documents->getDocumentShareLinks($id);
    }

    public function bulkDownloadDocuments(array $documentIds): array
    {
        return $this->documents->bulkDownloadDocuments($documentIds);
    }

    public function bulkEditDocuments(array $documentIds, array $editData): array
    {
        return $this->documents->bulkEditDocuments($documentIds, $editData);
    }

    public function getNextASN(): int
    {
        return $this->documents->getNextASN();
    }

    public function getDocumentSelectionData(array $documentIds): array
    {
        return $this->documents->getDocumentSelectionData($documentIds);
    }

    public function searchDocuments(string $query, bool $dbOnly = false): array
    {
        return $this->documents->searchDocuments($query, $dbOnly);
    }

    public function getSearchAutocomplete(string $term, int $limit = 10): array
    {
        return $this->documents->getSearchAutocomplete($term, $limit);
    }

    public function getTags(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->tags->getTags($filters, $page, $pageSize);
    }

    public function createTag(array $tagData): array
    {
        return $this->tags->createTag($tagData);
    }

    public function updateTag(int $id, array $tagData): array
    {
        return $this->tags->updateTag($id, $tagData);
    }

    public function deleteTag(int $id): bool
    {
        return $this->tags->deleteTag($id);
    }

    public function getCorrespondents(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->correspondents->getCorrespondents($filters, $page, $pageSize);
    }

    public function createCorrespondent(array $correspondentData): array
    {
        return $this->correspondents->createCorrespondent($correspondentData);
    }

    public function updateCorrespondent(int $id, array $correspondentData): array
    {
        return $this->correspondents->updateCorrespondent($id, $correspondentData);
    }

    public function deleteCorrespondent(int $id): bool
    {
        return $this->correspondents->deleteCorrespondent($id);
    }

    public function getDocumentTypes(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->documentTypes->getDocumentTypes($filters, $page, $pageSize);
    }

    public function createDocumentType(array $documentTypeData): array
    {
        return $this->documentTypes->createDocumentType($documentTypeData);
    }

    public function updateDocumentType(int $id, array $documentTypeData): array
    {
        return $this->documentTypes->updateDocumentType($id, $documentTypeData);
    }

    public function deleteDocumentType(int $id): bool
    {
        return $this->documentTypes->deleteDocumentType($id);
    }

    public function getStoragePaths(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->storagePaths->getStoragePaths($filters, $page, $pageSize);
    }

    public function createStoragePath(array $storagePathData): array
    {
        return $this->storagePaths->createStoragePath($storagePathData);
    }

    public function updateStoragePath(int $id, array $storagePathData): array
    {
        return $this->storagePaths->updateStoragePath($id, $storagePathData);
    }

    public function deleteStoragePath(int $id): bool
    {
        return $this->storagePaths->deleteStoragePath($id);
    }

    public function getCustomFields(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->customFields->getCustomFields($filters, $page, $pageSize);
    }

    public function createCustomField(array $customFieldData): array
    {
        return $this->customFields->createCustomField($customFieldData);
    }

    public function updateCustomField(int $id, array $customFieldData): array
    {
        return $this->customFields->updateCustomField($id, $customFieldData);
    }

    public function deleteCustomField(int $id): bool
    {
        return $this->customFields->deleteCustomField($id);
    }

    public function getShareLinks(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->shareLinks->getShareLinks($filters, $page, $pageSize);
    }

    public function createShareLink(array $shareLinkData): array
    {
        return $this->shareLinks->createShareLink($shareLinkData);
    }

    public function updateShareLink(int $id, array $shareLinkData): array
    {
        return $this->shareLinks->updateShareLink($id, $shareLinkData);
    }

    public function deleteShareLink(int $id): bool
    {
        return $this->shareLinks->deleteShareLink($id);
    }

    public function getStatistics(): array
    {
        return $this->system->getStatistics();
    }

    public function getSavedViews(int $page = 1, int $pageSize = 25): array
    {
        return $this->system->getSavedViews($page, $pageSize);
    }

    public function createSavedView(array $savedViewData): array
    {
        return $this->system->createSavedView($savedViewData);
    }

    public function updateSavedView(int $id, array $savedViewData): array
    {
        return $this->system->updateSavedView($id, $savedViewData);
    }

    public function deleteSavedView(int $id): bool
    {
        return $this->system->deleteSavedView($id);
    }

    public function testConnection(): bool
    {
        try {
            $this->getStatus();

            return true;
        } catch (PaperlessApiException $e) {
            Log::error('Paperless connection test failed - API Error', [
                'error' => $e->getMessage(),
                'status_code' => $e->getStatusCode(),
                'base_url' => $this->client->getBaseUrl(),
                'response_data' => $e->getResponseData(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('Paperless connection test failed - General Error', [
                'error' => $e->getMessage(),
                'base_url' => $this->client->getBaseUrl(),
            ]);

            return false;
        }
    }
}
