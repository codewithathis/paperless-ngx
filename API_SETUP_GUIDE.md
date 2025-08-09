# Paperless-ngx API Setup Guide

## Quick Start

### 1. Installation

```bash
composer require codewithathis/paperless-ngx
```

### 2. Configuration

Add to your `.env` file:

```env
# Paperless-ngx Connection
PAPERLESS_BASE_URL=https://your-paperless-instance.com
PAPERLESS_TOKEN=your-api-token
PAPERLESS_AUTH_METHOD=token

# API Authentication (Choose one method)
PAPERLESS_API_AUTH_ENABLED=true
PAPERLESS_API_AUTH_METHOD=sanctum

# For Sanctum Authentication (Recommended)
# Install Sanctum: composer require laravel/sanctum
# Run: php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
# Run: php artisan migrate

# For API Token Authentication
# PAPERLESS_API_AUTH_METHOD=token
# PAPERLESS_API_TOKENS=your-secure-token-1,your-secure-token-2

# For Basic Authentication
# PAPERLESS_API_AUTH_METHOD=basic
# PAPERLESS_API_USERNAME=api-user
# PAPERLESS_API_PASSWORD=secure-password

# Security Settings
PAPERLESS_RATE_LIMIT_ENABLED=true
PAPERLESS_RATE_LIMIT_MAX_ATTEMPTS=60
PAPERLESS_IP_WHITELIST=192.168.1.100,10.0.0.0/8
```

### 3. Publish Configuration (Optional)

```bash
php artisan vendor:publish --provider="Codewithathis\PaperlessNgx\PaperlessServiceProvider"
```

### 4. Setup Authentication

#### Option A: Sanctum Authentication (Recommended)
```bash
# Install Laravel Sanctum
composer require laravel/sanctum

# Publish configuration
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Run migrations
php artisan migrate

# Generate a token for testing
php artisan tinker
>>> $user = App\Models\User::first();
>>> $token = $user->createToken('paperless-api')->plainTextToken;
>>> echo $token;
```

#### Option B: API Token Authentication
```bash
# Generate a secure API token
php artisan paperless:generate-token --name="my-api-client" --show

# Add the token to your .env file
PAPERLESS_API_TOKENS=your-generated-token
PAPERLESS_API_AUTH_METHOD=token
```

#### Option C: Basic Authentication
```env
PAPERLESS_API_AUTH_METHOD=basic
PAPERLESS_API_USERNAME=your-username
PAPERLESS_API_PASSWORD=your-password
```

### 5. Test Connection

```bash
# Test Paperless-ngx connection
php artisan paperless:test-connection

# Test API authentication
php artisan paperless:test-auth --method=token --token=YOUR_TOKEN
```

Or via API:
```bash
# With Sanctum
curl -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
     -H "Accept: application/json" \
     https://your-domain.com/api/paperless/test-connection

# With API Token
curl -H "X-Paperless-Token: YOUR_API_TOKEN" \
     -H "Accept: application/json" \
     https://your-domain.com/api/paperless/test-connection
```

## API Endpoints Overview

| Category | Endpoints | Description |
|----------|-----------|-------------|
| **Connection** | `GET /test-connection` | Test Paperless-ngx connection |
| **Documents** | `GET /documents`<br>`GET /documents/{id}`<br>`POST /documents`<br>`PUT /documents/{id}`<br>`DELETE /documents/{id}`<br>`GET /documents/{id}/download` | Full CRUD operations for documents |
| **Search** | `GET /search` | Full-text document search |
| **Metadata** | `GET /tags`<br>`GET /correspondents`<br>`GET /document-types` | Get tags, correspondents, document types |
| **Statistics** | `GET /statistics` | System statistics and user info |
| **Bulk Operations** | `POST /bulk-edit` | Bulk edit multiple documents |

## Common Use Cases

### 1. Upload a Document

```bash
curl -X POST "https://your-domain.com/api/paperless/documents" \
  -F "document=@invoice.pdf" \
  -F "title=Invoice #123" \
  -F "correspondent=5" \
  -F "document_type=2" \
  -F "tags[]=1" \
  -F "tags[]=2"
```

### 2. Search Documents

```bash
curl -X GET "https://your-domain.com/api/paperless/search?query=invoice&db_only=false"
```

### 3. Get Documents with Filters

```bash
curl -X GET "https://your-domain.com/api/paperless/documents?correspondent__id=5&page=1&page_size=10"
```

### 4. Update Document

```bash
curl -X PUT "https://your-domain.com/api/paperless/documents/123" \
  -H "Content-Type: application/json" \
  -d '{"title": "Updated Title", "correspondent": 5}'
```

### 5. Bulk Edit Documents

```bash
curl -X POST "https://your-domain.com/api/paperless/bulk-edit" \
  -H "Content-Type: application/json" \
  -d '{"documents": [123, 124], "title": "Updated Title"}'
```

## PHP/Laravel Usage

### Using the Facade

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

### Using the Service Directly

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

## JavaScript/Fetch Examples

### Test Connection

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

### Upload Document

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

## Postman Collection

Import the provided Postman collection (`Paperless-ngx_API.postman_collection.json`) to test all endpoints easily.

1. Open Postman
2. Click "Import"
3. Select the collection file
4. Update the `base_url` variable with your domain
5. Start testing!

## Error Handling

All API responses include a `success` field:

```json
{
    "success": true,
    "data": {...},
    "message": "Optional message"
}
```

For errors:
```json
{
    "success": false,
    "message": "Error description"
}
```

## Common HTTP Status Codes

- `200 OK`: Request successful
- `201 Created`: Resource created
- `400 Bad Request`: Invalid parameters
- `401 Unauthorized`: Authentication required
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation errors
- `500 Internal Server Error`: Server error

## Best Practices

1. **Authentication**: Use token authentication for production
2. **Error Handling**: Always check the `success` field
3. **Pagination**: Use pagination for large datasets
4. **File Uploads**: Validate file types and sizes
5. **Caching**: Cache frequently accessed data
6. **Bulk Operations**: Use bulk operations for multiple updates
7. **Search**: Use specific filters for better performance

## Troubleshooting

### Connection Issues
- Verify `PAPERLESS_BASE_URL` is correct
- Check authentication credentials
- Ensure Paperless-ngx is running and accessible

### Upload Issues
- Check file size (max 50MB)
- Verify file format is supported
- Ensure proper form data structure

### Search Issues
- Use specific search terms
- Try `db_only=true` for database-only search
- Check if full-text search is enabled in Paperless-ngx

## Support

- **Documentation**: See `API_DOCUMENTATION.md` for detailed docs
- **Reference**: See `API_REFERENCE.md` for quick reference
- **Postman**: Use the provided collection for testing
- **Email**: trichyathis@gmail.com
