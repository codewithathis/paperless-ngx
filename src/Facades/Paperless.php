<?php

namespace Codewithathis\PaperlessNgx\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool testConnection()
 * @method static array getStatus()
 * @method static array getRemoteVersion()
 * @method static array getProfile()
 * @method static string generateAuthToken()
 * @method static array getDocuments(array $filters = [], int $page = 1, int $pageSize = 25)
 * @method static array getDocument(int $id)
 * @method static string uploadDocument(\Illuminate\Http\UploadedFile $file, array $metadata = [])
 * @method static array updateDocument(int $id, array $data)
 * @method static array patchDocument(int $id, array $data)
 * @method static bool deleteDocument(int $id)
 * @method static string downloadDocument(int $id, bool $original = false)
 * @method static string getDocumentPreview(int $id)
 * @method static string getDocumentThumbnail(int $id)
 * @method static array getDocumentMetadata(int $id)
 * @method static array getDocumentSuggestions(int $id)
 * @method static array getDocumentNotes(int $id, int $page = 1, int $pageSize = 25)
 * @method static array addDocumentNote(int $id, string $note)
 * @method static bool deleteDocumentNote(int $documentId, int $noteId)
 * @method static array getDocumentHistory(int $id, int $page = 1, int $pageSize = 25)
 * @method static array emailDocument(int $id, array $emailData)
 * @method static array getDocumentShareLinks(int $id)
 * @method static array bulkDownloadDocuments(array $documentIds)
 * @method static array bulkEditDocuments(array $documentIds, array $editData)
 * @method static int getNextASN()
 * @method static array getDocumentSelectionData(array $documentIds)
 * @method static array searchDocuments(string $query, bool $dbOnly = false)
 * @method static array getSearchAutocomplete(string $term, int $limit = 10)
 * @method static array getTags(array $filters = [], int $page = 1, int $pageSize = 25)
 * @method static array createTag(array $tagData)
 * @method static array updateTag(int $id, array $tagData)
 * @method static bool deleteTag(int $id)
 * @method static array getCorrespondents(array $filters = [], int $page = 1, int $pageSize = 25)
 * @method static array createCorrespondent(array $correspondentData)
 * @method static array updateCorrespondent(int $id, array $correspondentData)
 * @method static bool deleteCorrespondent(int $id)
 * @method static array getDocumentTypes(array $filters = [], int $page = 1, int $pageSize = 25)
 * @method static array createDocumentType(array $documentTypeData)
 * @method static array updateDocumentType(int $id, array $documentTypeData)
 * @method static bool deleteDocumentType(int $id)
 * @method static array getStoragePaths(array $filters = [], int $page = 1, int $pageSize = 25)
 * @method static array createStoragePath(array $storagePathData)
 * @method static array updateStoragePath(int $id, array $storagePathData)
 * @method static bool deleteStoragePath(int $id)
 * @method static array getCustomFields(array $filters = [], int $page = 1, int $pageSize = 25)
 * @method static array createCustomField(array $customFieldData)
 * @method static array updateCustomField(int $id, array $customFieldData)
 * @method static bool deleteCustomField(int $id)
 * @method static array getShareLinks(array $filters = [], int $page = 1, int $pageSize = 25)
 * @method static array createShareLink(array $shareLinkData)
 * @method static array updateShareLink(int $id, array $shareLinkData)
 * @method static bool deleteShareLink(int $id)
 * @method static array getStatistics()
 * @method static array getSavedViews(int $page = 1, int $pageSize = 25)
 * @method static array createSavedView(array $savedViewData)
 * @method static array updateSavedView(int $id, array $savedViewData)
 * @method static bool deleteSavedView(int $id)
 * @method static \Codewithathis\PaperlessNgx\PaperlessService setToken(string $token)
 * @method static \Codewithathis\PaperlessNgx\PaperlessService setBasicAuth(string $username, string $password)
 *
 * @see \Codewithathis\PaperlessNgx\PaperlessService
 */
class Paperless extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'paperless';
    }
}
