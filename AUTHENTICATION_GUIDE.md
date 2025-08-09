# Paperless-ngx API Authentication Guide

## Overview

The Paperless-ngx Laravel package includes a comprehensive authentication system to protect your API endpoints. This guide covers all authentication methods, security features, and best practices.

## 🔐 Authentication Methods

### 1. Laravel Sanctum (Recommended for Web Applications)

**Best for:** Web applications, SPA frontends, mobile apps with user accounts

**Setup:**
```bash
# Install Laravel Sanctum
composer require laravel/sanctum

# Publish configuration
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Run migrations
php artisan migrate
```

**Configuration:**
```env
PAPERLESS_API_AUTH_ENABLED=true
PAPERLESS_API_AUTH_METHOD=sanctum
PAPERLESS_SANCTUM_GUARD=web
PAPERLESS_SANCTUM_STATEFUL=false
```

**Generate Token:**
```php
// In your application
$user = User::find(1);
$token = $user->createToken('paperless-api')->plainTextToken;
```

**Usage:**
```bash
curl -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
     -H "Accept: application/json" \
     https://your-domain.com/api/paperless/test-connection
```

### 2. API Token Authentication (Recommended for API Clients)

**Best for:** Third-party integrations, microservices, automated scripts

**Generate Token:**
```bash
php artisan paperless:generate-token --name="my-api-client" --show
```

**Configuration:**
```env
PAPERLESS_API_AUTH_ENABLED=true
PAPERLESS_API_AUTH_METHOD=token
PAPERLESS_API_TOKENS=your-secure-token-1,your-secure-token-2
PAPERLESS_TOKEN_HEADER=X-Paperless-Token
```

**Usage:**
```bash
curl -H "X-Paperless-Token: YOUR_API_TOKEN" \
     -H "Accept: application/json" \
     https://your-domain.com/api/paperless/test-connection
```

### 3. Basic Authentication

**Best for:** Simple integrations, legacy systems

**Configuration:**
```env
PAPERLESS_API_AUTH_ENABLED=true
PAPERLESS_API_AUTH_METHOD=basic
PAPERLESS_API_USERNAME=api-user
PAPERLESS_API_PASSWORD=secure-password
```

**Usage:**
```bash
curl -u "username:password" \
     -H "Accept: application/json" \
     https://your-domain.com/api/paperless/test-connection
```

### 4. No Authentication (Development Only)

**⚠️ Warning:** Only use in development environments!

**Configuration:**
```env
PAPERLESS_API_AUTH_ENABLED=false
# or
PAPERLESS_API_AUTH_METHOD=none
```

## 🛡️ Security Features

### Rate Limiting

Protect against abuse with configurable rate limiting:

```env
PAPERLESS_RATE_LIMIT_ENABLED=true
PAPERLESS_RATE_LIMIT_MAX_ATTEMPTS=60
PAPERLESS_RATE_LIMIT_DECAY_MINUTES=1
```

**Response when rate limited:**
```json
{
    "success": false,
    "message": "Rate limit exceeded. Please try again later.",
    "retry_after": 60
}
```

### IP Whitelisting

Restrict access to specific IP addresses or ranges:

```env
# Single IP
PAPERLESS_IP_WHITELIST=192.168.1.100

# Multiple IPs
PAPERLESS_IP_WHITELIST=192.168.1.100,10.0.0.50

# CIDR ranges
PAPERLESS_IP_WHITELIST=10.0.0.0/8,172.16.0.0/12

# Mixed
PAPERLESS_IP_WHITELIST=192.168.1.100,10.0.0.0/8,172.16.0.0/12
```

### CORS Configuration

Control which origins can access your API:

```env
# Allow specific domains
PAPERLESS_ALLOWED_ORIGINS=https://your-frontend.com,https://admin.your-domain.com

# Allow all (not recommended for production)
PAPERLESS_ALLOWED_ORIGINS=*
```

## 🧪 Testing Authentication

### Test Current Configuration
```bash
php artisan paperless:test-auth
```

### Test Specific Method
```bash
# Test Sanctum
php artisan paperless:test-auth --method=sanctum

# Test API Token
php artisan paperless:test-auth --method=token --token=YOUR_TOKEN

# Test Basic Auth
php artisan paperless:test-auth --method=basic --username=user --password=pass

# Test No Auth
php artisan paperless:test-auth --method=none
```

### Test via API
```bash
# Test with Sanctum
curl -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
     -H "Accept: application/json" \
     https://your-domain.com/api/paperless/test-connection

# Test with API Token
curl -H "X-Paperless-Token: YOUR_API_TOKEN" \
     -H "Accept: application/json" \
     https://your-domain.com/api/paperless/test-connection

# Test with Basic Auth
curl -u "username:password" \
     -H "Accept: application/json" \
     https://your-domain.com/api/paperless/test-connection
```

## 📝 Logging and Monitoring

All authentication attempts are logged to the configured channel:

```env
PAPERLESS_LOGGING_ENABLED=true
PAPERLESS_LOG_LEVEL=info
PAPERLESS_LOG_CHANNEL=paperless
```

**Log entries include:**
- Successful authentication attempts
- Failed authentication attempts
- Rate limit violations
- IP whitelist violations
- User information (for Sanctum)

## 🔧 Configuration Reference

### Complete Environment Configuration

```env
# API Authentication
PAPERLESS_API_AUTH_ENABLED=true
PAPERLESS_API_AUTH_METHOD=sanctum

# Sanctum Settings
PAPERLESS_SANCTUM_GUARD=web
PAPERLESS_SANCTUM_STATEFUL=false
PAPERLESS_SANCTUM_EXPIRATION=null

# API Token Settings
PAPERLESS_API_TOKENS=token1,token2,token3
PAPERLESS_TOKEN_HEADER=X-Paperless-Token

# Basic Auth Settings
PAPERLESS_API_USERNAME=api-user
PAPERLESS_API_PASSWORD=secure-password

# Rate Limiting
PAPERLESS_RATE_LIMIT_ENABLED=true
PAPERLESS_RATE_LIMIT_MAX_ATTEMPTS=60
PAPERLESS_RATE_LIMIT_DECAY_MINUTES=1

# IP Whitelist
PAPERLESS_IP_WHITELIST=192.168.1.100,10.0.0.0/8

# CORS
PAPERLESS_ALLOWED_ORIGINS=https://your-frontend.com

# Logging
PAPERLESS_LOGGING_ENABLED=true
PAPERLESS_LOG_LEVEL=info
PAPERLESS_LOG_CHANNEL=paperless
```

## 🚀 Quick Start Examples

### For Web Applications (Sanctum)

1. **Install and configure Sanctum:**
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

2. **Configure environment:**
```env
PAPERLESS_API_AUTH_ENABLED=true
PAPERLESS_API_AUTH_METHOD=sanctum
```

3. **Generate token:**
```php
$user = User::find(1);
$token = $user->createToken('paperless-api')->plainTextToken;
```

4. **Use in requests:**
```javascript
fetch('/api/paperless/documents', {
    headers: {
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'
    }
})
```

### For API Clients (Token Auth)

1. **Generate API token:**
```bash
php artisan paperless:generate-token --name="my-client" --show
```

2. **Configure environment:**
```env
PAPERLESS_API_AUTH_ENABLED=true
PAPERLESS_API_AUTH_METHOD=token
PAPERLESS_API_TOKENS=your-generated-token
```

3. **Use in requests:**
```bash
curl -H "X-Paperless-Token: YOUR_TOKEN" \
     -H "Accept: application/json" \
     https://your-domain.com/api/paperless/documents
```

## 🔒 Security Best Practices

1. **Use Sanctum for web applications** - Provides user-based authentication
2. **Use API tokens for integrations** - Easier to manage and revoke
3. **Enable rate limiting** - Protect against abuse
4. **Use IP whitelisting** - Restrict access to known IPs
5. **Enable logging** - Monitor authentication attempts
6. **Use HTTPS** - Always in production
7. **Rotate tokens regularly** - Especially for long-lived tokens
8. **Use strong passwords** - For basic authentication
9. **Monitor logs** - Check for suspicious activity
10. **Test authentication** - Use the provided test commands

## 🆘 Troubleshooting

### Common Issues

**"Authentication required" error:**
- Check if authentication is enabled
- Verify the authentication method is correct
- Ensure credentials are properly configured

**"Rate limit exceeded" error:**
- Wait for the rate limit to reset
- Increase rate limit settings if needed
- Check for excessive API usage

**"IP address not allowed" error:**
- Add your IP to the whitelist
- Check IP whitelist configuration
- Verify CIDR notation is correct

**"Invalid API token" error:**
- Regenerate the token
- Check token configuration
- Ensure token is not expired

### Debug Commands

```bash
# Test authentication configuration
php artisan paperless:test-auth

# Generate new API token
php artisan paperless:generate-token --show

# Test Paperless-ngx connection
php artisan paperless:test-connection

# Check logs
tail -f storage/logs/paperless.log
```

## 📞 Support

For authentication issues or questions:

- Check the logs in `storage/logs/paperless.log`
- Use the test commands to verify configuration
- Review this authentication guide
- Contact support with specific error messages

---

**Remember:** Always use HTTPS in production and keep your authentication credentials secure!
