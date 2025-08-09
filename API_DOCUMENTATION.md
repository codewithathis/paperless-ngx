# Paperless-ngx Laravel Package API Documentation

## Overview

The Paperless-ngx Laravel package provides a comprehensive API for managing documents, tags, correspondents, and other Paperless-ngx features. This documentation covers all available endpoints, request/response formats, and usage examples.

## Base URL

All API endpoints are prefixed with `/api/paperless`:

```
https://your-domain.com/api/paperless
```

## Authentication

The package supports two authentication methods:

### 1. Token Authentication (Recommended)
```php
// In your .env file
PAPERLESS_BASE_URL=https://your-paperless-instance.com
PAPERLESS_TOKEN=your-api-token
PAPERLESS_AUTH_METHOD=token
```

### 2. Basic Authentication
```php
// In your .env file
PAPERLESS_BASE_URL=https://your-paperless-instance.com
PAPERLESS_USERNAME=your-username
PAPERLESS_PASSWORD=your-password
PAPERLESS_AUTH_METHOD=basic
```

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
    "success": true,
    "data": {...},
    "message": "Optional success message"
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description"
}
```

## API Endpoints

### 1. Connection Test

#### Test Connection
**GET** `/api/paperless/test-connection`

Tests the connection to your Paperless-ngx instance.

**Response:**
```json
{
    "success": true,
    "message": "Successfully connected to Paperless-ngx",
    "status": {
        "version": "2.8.4",
        "build": "2024-01-15",
        "debug": false,
        "authentication": {
            "method": "token",
            "user": "admin"
        }
    }
}
```

---

### 2. Documents

#### Get Documents
**GET** `/api/paperless/documents`

Retrieve a list of documents with optional filtering and pagination.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `page_size` (optional): Items per page (default: 25)
- `search` (optional): Search term
- `title__icontains` (optional): Filter by title containing text
- `content__icontains` (optional): Filter by content containing text
- `correspondent__id` (optional): Filter by correspondent ID
- `document_type__id` (optional): Filter by document type ID
- `tags__id` (optional): Filter by tag IDs (comma-separated)
- `created__gte` (optional): Filter by creation date (YYYY-MM-DD)
- `created__lte` (optional): Filter by creation date (YYYY-MM-DD)
- `added__gte` (optional): Filter by added date (YYYY-MM-DD)
- `added__lte` (optional): Filter by added date (YYYY-MM-DD)

**Example Request:**
```
GET /api/paperless/documents?page=1&page_size=10&title__icontains=invoice&correspondent__id=5
```

**Response:**
```json
{
    "success": true,
    "data": {
        "count": 150,
        "next": "https://api.example.com/api/paperless/documents?page=2",
        "previous": null,
        "results": [
            {
                "id": 123,
                "title": "Invoice #INV-2024-001",
                "correspondent": {
                    "id": 5,
                    "name": "Acme Corp"
                },
                "document_type": {
                    "id": 2,
                    "name": "Invoice"
                },
                "tags": [
                    {
                        "id": 1,
                        "name": "Important"
                    }
                ],
                "created_date": "2024-01-15",
                "added": "2024-01-15T10:30:00Z",
                "modified": "2024-01-15T10:30:00Z",
                "archive_serial_number": 12345,
                "storage_path": {
                    "id": 1,
                    "name": "Invoices"
                }
            }
        ]
    }
}
```

#### Get Single Document
**GET** `/api/paperless/documents/{id}`

Retrieve a specific document by ID.

**Path Parameters:**
- `id` (required): Document ID

**Example Request:**
```
GET /api/paperless/documents/123
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 123,
        "title": "Invoice #INV-2024-001",
        "correspondent": {
            "id": 5,
            "name": "Acme Corp"
        },
        "document_type": {
            "id": 2,
            "name": "Invoice"
        },
        "tags": [
            {
                "id": 1,
                "name": "Important"
            }
        ],
        "created_date": "2024-01-15",
        "added": "2024-01-15T10:30:00Z",
        "modified": "2024-01-15T10:30:00Z",
        "archive_serial_number": 12345,
        "storage_path": {
            "id": 1,
            "name": "Invoices"
        },
        "content": "Document content text...",
        "notes": [
            {
                "id": 1,
                "note": "Payment received",
                "created": "2024-01-16T09:00:00Z"
            }
        ]
    }
}
```

#### Upload Document
**POST** `/api/paperless/documents`

Upload a new document to Paperless-ngx.

**Request Body (multipart/form-data):**
- `document` (required): File to upload (max 50MB)
- `title` (optional): Document title
- `correspondent` (optional): Correspondent ID
- `document_type` (optional): Document type ID
- `tags` (optional): Array of tag IDs
- `storage_path` (optional): Storage path ID
- `archive_serial_number` (optional): Archive serial number

**Example Request:**
```bash
curl -X POST /api/paperless/documents \
  -F "document=@invoice.pdf" \
  -F "title=Invoice #INV-2024-002" \
  -F "correspondent=5" \
  -F "document_type=2" \
  -F "tags[]=1" \
  -F "tags[]=3"
```

**Response:**
```json
{
    "success": true,
    "message": "Document uploaded successfully",
    "document_id": 124
}
```

#### Update Document
**PUT** `/api/paperless/documents/{id}`

Update an existing document's metadata.

**Path Parameters:**
- `id` (required): Document ID

**Request Body (JSON):**
```json
{
    "title": "Updated Invoice Title",
    "correspondent": 5,
    "document_type": 2,
    "tags": [1, 3],
    "storage_path": 1,
    "archive_serial_number": 12346
}
```

**Response:**
```json
{
    "success": true,
    "message": "Document updated successfully",
    "data": {
        "id": 123,
        "title": "Updated Invoice Title",
        "correspondent": {
            "id": 5,
            "name": "Acme Corp"
        },
        "document_type": {
            "id": 2,
            "name": "Invoice"
        },
        "tags": [
            {
                "id": 1,
                "name": "Important"
            },
            {
                "id": 3,
                "name": "Paid"
            }
        ]
    }
}
```

#### Delete Document
**DELETE** `/api/paperless/documents/{id}`

Delete a document from Paperless-ngx.

**Path Parameters:**
- `id` (required): Document ID

**Example Request:**
```
DELETE /api/paperless/documents/123
```

**Response:**
```json
{
    "success": true,
    "message": "Document deleted successfully"
}
```

#### Download Document
**GET** `/api/paperless/documents/{id}/download`

Download a document file.

**Path Parameters:**
- `id` (required): Document ID

**Query Parameters:**
- `original` (optional): Download original file instead of processed version (default: false)

**Example Request:**
```
GET /api/paperless/documents/123/download?original=true
```

**Response:**
```json
{
    "success": true,
    "data": "base64-encoded-file-content",
    "size": 1024000
}
```

---

### 3. Search

#### Search Documents
**GET** `/api/paperless/search`

Search documents using full-text search.

**Query Parameters:**
- `query` (required): Search query
- `db_only` (optional): Search only in database, not full-text (default: false)

**Example Request:**
```
GET /api/paperless/search?query=invoice&db_only=false
```

**Response:**
```json
{
    "success": true,
    "data": {
        "count": 25,
        "results": [
            {
                "id": 123,
                "title": "Invoice #INV-2024-001",
                "correspondent": {
                    "id": 5,
                    "name": "Acme Corp"
                },
                "score": 0.95,
                "highlights": [
                    {
                        "field": "title",
                        "text": "Invoice #INV-2024-001"
                    },
                    {
                        "field": "content",
                        "text": "...invoice details..."
                    }
                ]
            }
        ]
    }
}
```

---

### 4. Tags

#### Get Tags
**GET** `/api/paperless/tags`

Retrieve a list of tags with optional filtering and pagination.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `page_size` (optional): Items per page (default: 25)
- `name__icontains` (optional): Filter by name containing text
- `id__in` (optional): Filter by specific tag IDs (comma-separated)

**Example Request:**
```
GET /api/paperless/tags?page=1&page_size=10&name__icontains=important
```

**Response:**
```json
{
    "success": true,
    "data": {
        "count": 15,
        "next": null,
        "previous": null,
        "results": [
            {
                "id": 1,
                "name": "Important",
                "slug": "important",
                "color": "#ff0000",
                "text_color": "#ffffff",
                "matching_algorithm": 1,
                "matching_algorithm_name": "Exact",
                "is_inbox_tag": false,
                "document_count": 25
            }
        ]
    }
}
```

---

### 5. Correspondents

#### Get Correspondents
**GET** `/api/paperless/correspondents`

Retrieve a list of correspondents with optional filtering and pagination.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `page_size` (optional): Items per page (default: 25)
- `name__icontains` (optional): Filter by name containing text
- `id__in` (optional): Filter by specific correspondent IDs (comma-separated)

**Example Request:**
```
GET /api/paperless/correspondents?page=1&page_size=10&name__icontains=acme
```

**Response:**
```json
{
    "success": true,
    "data": {
        "count": 8,
        "next": null,
        "previous": null,
        "results": [
            {
                "id": 5,
                "name": "Acme Corp",
                "slug": "acme-corp",
                "last_correspondence": "2024-01-15T10:30:00Z",
                "document_count": 45,
                "matching_algorithm": 1,
                "matching_algorithm_name": "Exact"
            }
        ]
    }
}
```

---

### 6. Document Types

#### Get Document Types
**GET** `/api/paperless/document-types`

Retrieve a list of document types with optional filtering and pagination.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `page_size` (optional): Items per page (default: 25)
- `name__icontains` (optional): Filter by name containing text
- `id__in` (optional): Filter by specific document type IDs (comma-separated)

**Example Request:**
```
GET /api/paperless/document-types?page=1&page_size=10
```

**Response:**
```json
{
    "success": true,
    "data": {
        "count": 5,
        "next": null,
        "previous": null,
        "results": [
            {
                "id": 2,
                "name": "Invoice",
                "slug": "invoice",
                "matching_algorithm": 1,
                "matching_algorithm_name": "Exact",
                "document_count": 150
            }
        ]
    }
}
```

---

### 7. Statistics

#### Get Statistics
**GET** `/api/paperless/statistics`

Retrieve system statistics and user profile information.

**Example Request:**
```
GET /api/paperless/statistics
```

**Response:**
```json
{
    "success": true,
    "data": {
        "documents_total": 1250,
        "documents_inbox": 15,
        "documents_archive": 1235,
        "correspondents_total": 45,
        "tags_total": 25,
        "document_types_total": 8,
        "storage_paths_total": 12,
        "storage_size": "2.5 GB",
        "user": {
            "id": 1,
            "username": "admin",
            "first_name": "Admin",
            "last_name": "User",
            "email": "admin@example.com",
            "date_joined": "2024-01-01T00:00:00Z",
            "is_superuser": true
        }
    }
}
```

---

### 8. Bulk Operations

#### Bulk Edit Documents
**POST** `/api/paperless/bulk-edit`

Update multiple documents at once.

**Request Body (JSON):**
```json
{
    "documents": [123, 124, 125],
    "title": "Updated Title",
    "correspondent": 5,
    "document_type": 2,
    "tags": [1, 3],
    "storage_path": 1
}
```

**Response:**
```json
{
    "success": true,
    "message": "Documents updated successfully",
    "data": {
        "updated_count": 3,
        "documents": [
            {
                "id": 123,
                "title": "Updated Title"
            },
            {
                "id": 124,
                "title": "Updated Title"
            },
            {
                "id": 125,
                "title": "Updated Title"
            }
        ]
    }
}
```

---

## Error Handling

### HTTP Status Codes

- `200 OK`: Request successful
- `201 Created`: Resource created successfully
- `400 Bad Request`: Invalid request parameters
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation errors
- `500 Internal Server Error`: Server error

### Common Error Responses

#### Validation Error
```json
{
    "success": false,
    "message": "The document field is required.",
    "errors": {
        "document": ["The document field is required."]
    }
}
```

#### Authentication Error
```json
{
    "success": false,
    "message": "Authentication failed: Invalid token"
}
```

#### Not Found Error
```json
{
    "success": false,
    "message": "Document not found"
}
```

#### Server Error
```json
{
    "success": false,
    "message": "Failed to connect to Paperless-ngx: Connection timeout"
}
```

---

## Configuration

### Environment Variables

```env
# Paperless-ngx Connection
PAPERLESS_BASE_URL=https://your-paperless-instance.com
PAPERLESS_AUTH_METHOD=token

# Token Authentication
PAPERLESS_TOKEN=your-api-token

# Basic Authentication (alternative)
PAPERLESS_USERNAME=your-username
PAPERLESS_PASSWORD=your-password

# Default Settings
PAPERLESS_PAGE_SIZE=25
PAPERLESS_TIMEOUT=30
PAPERLESS_RETRY_ATTEMPTS=3

# Upload Settings
PAPERLESS_MAX_FILE_SIZE=52428800
PAPERLESS_AUTO_OCR=true
PAPERLESS_AUTO_TAG=false

# Logging
PAPERLESS_LOGGING_ENABLED=true
PAPERLESS_LOG_LEVEL=error
PAPERLESS_LOG_CHANNEL=paperless

# Cache
PAPERLESS_CACHE_ENABLED=true
PAPERLESS_CACHE_TTL=3600
PAPERLESS_CACHE_PREFIX=paperless

# Security
PAPERLESS_VERIFY_SSL=true
PAPERLESS_ALLOW_SELF_SIGNED=false
PAPERLESS_REQUEST_TIMEOUT=30
```

---

## Usage Examples

### PHP/Laravel Examples

#### Using the Facade
```php
use Codewithathis\PaperlessNgx\Facades\Paperless;

// Test connection
$isConnected = Paperless::testConnection();

// Get documents
$documents = Paperless::getDocuments(['correspondent__id' => 5], 1, 10);

// Upload document
$documentId = Paperless::uploadDocument($file, [
    'title' => 'Invoice #123',
    'correspondent' => 5,
    'tags' => [1, 2]
]);

// Search documents
$results = Paperless::searchDocuments('invoice payment', false);
```

#### Using the Service Directly
```php
use Codewithathis\PaperlessNgx\PaperlessService;

$paperlessService = app(PaperlessService::class);

// Get statistics
$statistics = $paperlessService->getStatistics();

// Update document
$document = $paperlessService->updateDocument(123, [
    'title' => 'Updated Title',
    'tags' => [1, 3]
]);
```

### JavaScript/Fetch Examples

#### Test Connection
```javascript
fetch('/api/paperless/test-connection')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Connected:', data.status);
        } else {
            console.error('Connection failed:', data.message);
        }
    });
```

#### Upload Document
```javascript
const formData = new FormData();
formData.append('document', fileInput.files[0]);
formData.append('title', 'Invoice #123');
formData.append('correspondent', '5');
formData.append('tags[]', '1');
formData.append('tags[]', '2');

fetch('/api/paperless/documents', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Uploaded:', data.document_id);
    } else {
        console.error('Upload failed:', data.message);
    }
});
```

#### Search Documents
```javascript
fetch('/api/paperless/search?query=invoice&db_only=false')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Results:', data.data.results);
        } else {
            console.error('Search failed:', data.message);
        }
    });
```

### cURL Examples

#### Test Connection
```bash
curl -X GET "https://your-domain.com/api/paperless/test-connection"
```

#### Get Documents
```bash
curl -X GET "https://your-domain.com/api/paperless/documents?page=1&page_size=10&correspondent__id=5"
```

#### Upload Document
```bash
curl -X POST "https://your-domain.com/api/paperless/documents" \
  -F "document=@invoice.pdf" \
  -F "title=Invoice #123" \
  -F "correspondent=5" \
  -F "tags[]=1" \
  -F "tags[]=2"
```

#### Update Document
```bash
curl -X PUT "https://your-domain.com/api/paperless/documents/123" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Invoice Title",
    "correspondent": 5,
    "tags": [1, 3]
  }'
```

---

## Rate Limiting

The API respects Paperless-ngx's rate limiting. If you encounter rate limiting:

- Wait before making additional requests
- Implement exponential backoff
- Consider caching frequently accessed data
- Use bulk operations when possible

---

## Best Practices

1. **Authentication**: Use token authentication for production environments
2. **Error Handling**: Always check the `success` field in responses
3. **Pagination**: Use pagination for large datasets
4. **File Uploads**: Validate file types and sizes before upload
5. **Caching**: Cache frequently accessed data like tags and correspondents
6. **Bulk Operations**: Use bulk operations for multiple document updates
7. **Search**: Use specific filters to improve search performance
8. **Logging**: Enable logging for debugging and monitoring

---

## Support

For issues, questions, or contributions:

- GitHub Issues: [Repository URL]
- Documentation: [Documentation URL]
- Email: trichyathis@gmail.com

---

## Version History

- **v1.0.0**: Initial release with comprehensive API integration
