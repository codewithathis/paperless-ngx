# Changelog

## Unreleased

### Added

- Internal HTTP layer (`Http\PaperlessApiClient`) and domain API classes under `Api\` (documents, system, tags, correspondents, etc.).
- PHPUnit suite and GitHub Actions workflow (PHP 8.2–8.4 against Laravel 10–12).
- Request options wired from config: `defaults.timeout`, `defaults.retry_attempts`, `security.verify_ssl`, `security.allow_self_signed`, `security.timeout`, and optional API error logging via `logging.*`.

### Changed

- **PHP** minimum is now **8.2** (PHP 8.4 supported).
- **Laravel** support is **10 / 11 / 12** (Laravel 9 is no longer supported).
- Config default for `PAPERLESS_AUTH_METHOD` is now explicit: **`auto`** (same behaviour as before: Basic when both username and password are set, otherwise token). Set **`token`** to force the API token even when Basic credentials exist in `.env`, or **`basic`** to force Basic auth.
- Upload size limit is read from `config('paperless.upload.max_file_size')`, with a fallback to the legacy `paperless.max_file_size` key if present.
- `getNextASN()` now returns a real `int` parsed from the API response (and throws if the shape is unexpected).

### Fixed

- `paperless:test` Artisan command no longer treats the upload response as a numeric document id (uploads return a task id or similar payload).

### Upgrade notes

- If you relied on **both** token and Basic credentials in `.env` and expected **token** auth, set `PAPERLESS_AUTH_METHOD=token` explicitly.
- Application code should continue to type-hint `PaperlessService` or use the `Paperless` facade only; no changes required for the public method set.
