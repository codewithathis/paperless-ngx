<?php

namespace Codewithathis\PaperlessNgx;

use Codewithathis\PaperlessNgx\Api\AuthApi;
use Codewithathis\PaperlessNgx\Api\BulkApi;
use Codewithathis\PaperlessNgx\Api\ConfigApi;
use Codewithathis\PaperlessNgx\Api\CorrespondentApi;
use Codewithathis\PaperlessNgx\Api\CustomFieldApi;
use Codewithathis\PaperlessNgx\Api\DocumentApi;
use Codewithathis\PaperlessNgx\Api\DocumentTypeApi;
use Codewithathis\PaperlessNgx\Api\GroupApi;
use Codewithathis\PaperlessNgx\Api\LogApi;
use Codewithathis\PaperlessNgx\Api\MailApi;
use Codewithathis\PaperlessNgx\Api\ShareLinkApi;
use Codewithathis\PaperlessNgx\Api\StoragePathApi;
use Codewithathis\PaperlessNgx\Api\SystemApi;
use Codewithathis\PaperlessNgx\Api\TagApi;
use Codewithathis\PaperlessNgx\Api\TasksApi;
use Codewithathis\PaperlessNgx\Api\UserApi;
use Codewithathis\PaperlessNgx\Api\WorkflowApi;
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

    private TasksApi $tasks;

    private AuthApi $auth;

    private BulkApi $bulk;

    private WorkflowApi $workflows;

    private MailApi $mail;

    private UserApi $users;

    private GroupApi $groups;

    private LogApi $logs;

    private ConfigApi $config;

    public function __construct(
        string $baseUrl,
        ?string $token = null,
        ?string $username = null,
        ?string $password = null,
        string $authMethod = 'auto',
        ?int $apiVersion = null
    ) {
        $this->client = new PaperlessApiClient($baseUrl, $token, $username, $password, $authMethod, $apiVersion);
        $this->documents = new DocumentApi($this->client);
        $this->system = new SystemApi($this->client);
        $this->tags = new TagApi($this->client);
        $this->correspondents = new CorrespondentApi($this->client);
        $this->documentTypes = new DocumentTypeApi($this->client);
        $this->storagePaths = new StoragePathApi($this->client);
        $this->customFields = new CustomFieldApi($this->client);
        $this->shareLinks = new ShareLinkApi($this->client);
        $this->tasks = new TasksApi($this->client);
        $this->auth = new AuthApi($this->client);
        $this->bulk = new BulkApi($this->client);
        $this->workflows = new WorkflowApi($this->client);
        $this->mail = new MailApi($this->client);
        $this->users = new UserApi($this->client);
        $this->groups = new GroupApi($this->client);
        $this->logs = new LogApi($this->client);
        $this->config = new ConfigApi($this->client);
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

    public function setApiVersion(?int $version): self
    {
        $this->client->setApiVersion($version);

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
        return $this->tasks->getTaskByUUID($taskId);
    }

    public function getTasks(array $filters = []): array
    {
        return $this->tasks->getTasks($filters);
    }

    public function acknowledgeTasks(array $taskIds): array
    {
        return $this->tasks->acknowledgeTasks($taskIds);
    }

    public function obtainToken(string $username, string $password): array
    {
        return $this->auth->obtainToken($username, $password);
    }

    public function bulkEditObjects(array $payload): array
    {
        return $this->bulk->bulkEditObjects($payload);
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

    public function bulkAddTag(array $documentIds, int $tagId): array
    {
        return $this->documents->bulkAddTag($documentIds, $tagId);
    }

    public function bulkRemoveTag(array $documentIds, int $tagId): array
    {
        return $this->documents->bulkRemoveTag($documentIds, $tagId);
    }

    public function bulkSetCorrespondent(array $documentIds, int $correspondentId): array
    {
        return $this->documents->bulkSetCorrespondent($documentIds, $correspondentId);
    }

    public function bulkSetDocumentType(array $documentIds, int $documentTypeId): array
    {
        return $this->documents->bulkSetDocumentType($documentIds, $documentTypeId);
    }

    public function bulkSetStoragePath(array $documentIds, int $storagePathId): array
    {
        return $this->documents->bulkSetStoragePath($documentIds, $storagePathId);
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

    public function searchDocumentsViaDocuments(
        string $query,
        array $filters = [],
        int $page = 1,
        int $pageSize = 25
    ): array {
        return $this->documents->searchDocumentsViaDocuments($query, $filters, $page, $pageSize);
    }

    public function getSimilarDocuments(int $id, array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->documents->getSimilarDocuments($id, $filters, $page, $pageSize);
    }

    public function getDocumentsByCustomFieldQuery(
        array $customFieldQuery,
        array $filters = [],
        int $page = 1,
        int $pageSize = 25
    ): array {
        return $this->documents->getDocumentsByCustomFieldQuery($customFieldQuery, $filters, $page, $pageSize);
    }

    public function getTrash(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->documents->getTrash($filters, $page, $pageSize);
    }

    public function trashAction(?array $documentIds, string $action): array
    {
        return $this->documents->trashAction($documentIds, $action);
    }

    public function getTags(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->tags->getTags($filters, $page, $pageSize);
    }

    public function getTag(int $id): array
    {
        return $this->tags->getTag($id);
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

    public function getCorrespondent(int $id): array
    {
        return $this->correspondents->getCorrespondent($id);
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

    public function getDocumentType(int $id): array
    {
        return $this->documentTypes->getDocumentType($id);
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

    public function getStoragePath(int $id): array
    {
        return $this->storagePaths->getStoragePath($id);
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

    public function getCustomField(int $id): array
    {
        return $this->customFields->getCustomField($id);
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

    public function getShareLink(int $id): array
    {
        return $this->shareLinks->getShareLink($id);
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

    public function getUiSettings(): array
    {
        return $this->system->getUiSettings();
    }

    public function updateUiSettings(array $data): array
    {
        return $this->system->updateUiSettings($data);
    }

    public function disconnectSocialAccount(array $data = []): array
    {
        return $this->system->disconnectSocialAccount($data);
    }

    public function getSocialAccountProviders(): array
    {
        return $this->system->getSocialAccountProviders();
    }

    public function getTotpSettings(): array
    {
        return $this->system->getTotpSettings();
    }

    public function updateTotpSettings(array $data): array
    {
        return $this->system->updateTotpSettings($data);
    }

    public function getWorkflows(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->workflows->getWorkflows($filters, $page, $pageSize);
    }

    public function getWorkflow(int $id): array
    {
        return $this->workflows->getWorkflow($id);
    }

    public function createWorkflow(array $data): array
    {
        return $this->workflows->createWorkflow($data);
    }

    public function updateWorkflow(int $id, array $data): array
    {
        return $this->workflows->updateWorkflow($id, $data);
    }

    public function patchWorkflow(int $id, array $data): array
    {
        return $this->workflows->patchWorkflow($id, $data);
    }

    public function deleteWorkflow(int $id): bool
    {
        return $this->workflows->deleteWorkflow($id);
    }

    public function getWorkflowTriggers(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->workflows->getWorkflowTriggers($filters, $page, $pageSize);
    }

    public function getWorkflowTrigger(int $id): array
    {
        return $this->workflows->getWorkflowTrigger($id);
    }

    public function createWorkflowTrigger(array $data): array
    {
        return $this->workflows->createWorkflowTrigger($data);
    }

    public function updateWorkflowTrigger(int $id, array $data): array
    {
        return $this->workflows->updateWorkflowTrigger($id, $data);
    }

    public function patchWorkflowTrigger(int $id, array $data): array
    {
        return $this->workflows->patchWorkflowTrigger($id, $data);
    }

    public function deleteWorkflowTrigger(int $id): bool
    {
        return $this->workflows->deleteWorkflowTrigger($id);
    }

    public function getWorkflowActions(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->workflows->getWorkflowActions($filters, $page, $pageSize);
    }

    public function getWorkflowAction(int $id): array
    {
        return $this->workflows->getWorkflowAction($id);
    }

    public function createWorkflowAction(array $data): array
    {
        return $this->workflows->createWorkflowAction($data);
    }

    public function updateWorkflowAction(int $id, array $data): array
    {
        return $this->workflows->updateWorkflowAction($id, $data);
    }

    public function patchWorkflowAction(int $id, array $data): array
    {
        return $this->workflows->patchWorkflowAction($id, $data);
    }

    public function deleteWorkflowAction(int $id): bool
    {
        return $this->workflows->deleteWorkflowAction($id);
    }

    public function getMailAccounts(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->mail->getMailAccounts($filters, $page, $pageSize);
    }

    public function getMailAccount(int $id): array
    {
        return $this->mail->getMailAccount($id);
    }

    public function createMailAccount(array $data): array
    {
        return $this->mail->createMailAccount($data);
    }

    public function updateMailAccount(int $id, array $data): array
    {
        return $this->mail->updateMailAccount($id, $data);
    }

    public function patchMailAccount(int $id, array $data): array
    {
        return $this->mail->patchMailAccount($id, $data);
    }

    public function deleteMailAccount(int $id): bool
    {
        return $this->mail->deleteMailAccount($id);
    }

    public function getMailRules(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->mail->getMailRules($filters, $page, $pageSize);
    }

    public function getMailRule(int $id): array
    {
        return $this->mail->getMailRule($id);
    }

    public function createMailRule(array $data): array
    {
        return $this->mail->createMailRule($data);
    }

    public function updateMailRule(int $id, array $data): array
    {
        return $this->mail->updateMailRule($id, $data);
    }

    public function patchMailRule(int $id, array $data): array
    {
        return $this->mail->patchMailRule($id, $data);
    }

    public function deleteMailRule(int $id): bool
    {
        return $this->mail->deleteMailRule($id);
    }

    public function getProcessedMail(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->mail->getProcessedMail($filters, $page, $pageSize);
    }

    public function getProcessedMailItem(int $id): array
    {
        return $this->mail->getProcessedMailItem($id);
    }

    public function getUsers(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->users->getUsers($filters, $page, $pageSize);
    }

    public function getUser(int $id): array
    {
        return $this->users->getUser($id);
    }

    public function createUser(array $data): array
    {
        return $this->users->createUser($data);
    }

    public function updateUser(int $id, array $data): array
    {
        return $this->users->updateUser($id, $data);
    }

    public function patchUser(int $id, array $data): array
    {
        return $this->users->patchUser($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->users->deleteUser($id);
    }

    public function getGroups(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->groups->getGroups($filters, $page, $pageSize);
    }

    public function getGroup(int $id): array
    {
        return $this->groups->getGroup($id);
    }

    public function createGroup(array $data): array
    {
        return $this->groups->createGroup($data);
    }

    public function updateGroup(int $id, array $data): array
    {
        return $this->groups->updateGroup($id, $data);
    }

    public function patchGroup(int $id, array $data): array
    {
        return $this->groups->patchGroup($id, $data);
    }

    public function deleteGroup(int $id): bool
    {
        return $this->groups->deleteGroup($id);
    }

    public function getLogs(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->logs->getLogs($filters, $page, $pageSize);
    }

    public function getLog(int $id): array
    {
        return $this->logs->getLog($id);
    }

    public function getConfig(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return $this->config->getConfig($filters, $page, $pageSize);
    }

    public function getConfigItem(int $id): array
    {
        return $this->config->getConfigItem($id);
    }

    public function updateConfig(int $id, array $data): array
    {
        return $this->config->updateConfig($id, $data);
    }

    public function patchConfig(int $id, array $data): array
    {
        return $this->config->patchConfig($id, $data);
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
