# Paperless-ngx API Reference

## Base URL
```
https://your-domain.com/api/paperless
```

## Authentication
- **Token Auth**: `PAPERLESS_TOKEN=your-token`
- **Basic Auth**: `PAPERLESS_USERNAME=user` + `PAPERLESS_PASSWORD=pass`

## Response Format
```json
{
    "success": true|false,
    "data": {...},
    "message": "Optional message"
}
```

---

## Endpoints

### Connection Test
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/test-connection` | Test Paperless-ngx connection |

**Response:**
```json
{
    "success": true,
    "message": "Successfully connected to Paperless-ngx",
    "status": {
        "version": "2.8.4",
        "build": "2024-01-15",
        "authentication": {
            "method": "token",
            "user": "admin"
        }
    }
}
```

---

### Documents

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/documents` | Get documents with filters |
| GET | `/documents/{id}` | Get single document |
| POST | `/documents` | Upload document |
| PUT | `/documents/{id}` | Update document |
| DELETE | `/documents/{id}` | Delete document |
| GET | `/documents/{id}/download` | Download document |

#### GET `/documents`
**Query Parameters:**
- `page` (int, default: 1)
- `page_size` (int, default: 25)
- `search` (string)
- `title__icontains` (string)
- `content__icontains` (string)
- `correspondent__id` (int)
- `document_type__id` (int)
- `tags__id` (string, comma-separated)
- `created__gte` (date: YYYY-MM-DD)
- `created__lte` (date: YYYY-MM-DD)
- `added__gte` (date: YYYY-MM-DD)
- `added__lte` (date: YYYY-MM-DD)

#### POST `/documents`
**Form Data:**
- `document` (file, required, max 50MB)
- `title` (string, optional)
- `correspondent` (int, optional)
- `document_type` (int, optional)
- `tags[]` (array of int, optional)
- `storage_path` (int, optional)
- `archive_serial_number` (int, optional)

#### PUT `/documents/{id}`
**JSON Body:**
```json
{
    "title": "string",
    "correspondent": "integer",
    "document_type": "integer",
    "tags": ["integer"],
    "storage_path": "integer",
    "archive_serial_number": "integer"
}
```

#### GET `/documents/{id}/download`
**Query Parameters:**
- `original` (boolean, default: false)

---

### Search

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/search` | Search documents |

#### GET `/search`
**Query Parameters:**
- `query` (string, required)
- `db_only` (boolean, default: false)

---

### Tags

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/tags` | Get tags |

#### GET `/tags`
**Query Parameters:**
- `page` (int, default: 1)
- `page_size` (int, default: 25)
- `name__icontains` (string)
- `id__in` (string, comma-separated)

---

### Correspondents

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/correspondents` | Get correspondents |

#### GET `/correspondents`
**Query Parameters:**
- `page` (int, default: 1)
- `page_size` (int, default: 25)
- `name__icontains` (string)
- `id__in` (string, comma-separated)

---

### Document Types

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/document-types` | Get document types |

#### GET `/document-types`
**Query Parameters:**
- `page` (int, default: 1)
- `page_size` (int, default: 25)
- `name__icontains` (string)
- `id__in` (string, comma-separated)

---

### Statistics

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/statistics` | Get system statistics |

#### GET `/statistics`
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

### Bulk Operations

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/bulk-edit` | Bulk edit documents |

#### POST `/bulk-edit`
**JSON Body:**
```json
{
    "documents": ["integer"],
    "title": "string",
    "correspondent": "integer",
    "document_type": "integer",
    "tags": ["integer"],
    "storage_path": "integer"
}
```

---

## HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created |
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation errors |
| 500 | Internal Server Error - Server error |

---

## Error Response Format

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Error message"]
    }
}
```

---

## Quick Examples

### Test Connection
```bash
curl -X GET "https://your-domain.com/api/paperless/test-connection"
```

### Get Documents
```bash
curl -X GET "https://your-domain.com/api/paperless/documents?page=1&page_size=10"
```

### Upload Document
```bash
curl -X POST "https://your-domain.com/api/paperless/documents" \
  -F "document=@file.pdf" \
  -F "title=Document Title" \
  -F "correspondent=5" \
  -F "tags[]=1" \
  -F "tags[]=2"
```

### Search Documents
```bash
curl -X GET "https://your-domain.com/api/paperless/search?query=invoice"
```

### Update Document
```bash
curl -X PUT "https://your-domain.com/api/paperless/documents/123" \
  -H "Content-Type: application/json" \
  -d '{"title": "Updated Title", "correspondent": 5}'
```

### Bulk Edit
```bash
curl -X POST "https://your-domain.com/api/paperless/bulk-edit" \
  -H "Content-Type: application/json" \
  -d '{"documents": [123, 124], "title": "Updated Title"}'
```
