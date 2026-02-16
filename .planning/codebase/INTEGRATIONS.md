# External Integrations

**Analysis Date:** 2026-02-14

## APIs & External Services

**Email Sending (Outbound):**
- SMTP (default) - Primary mail transport
  - Config: `config/mail.php`
  - Auth: `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`
  - Default host: `smtp.mailgun.org`
- Mailgun - Supported mail transport
  - Config: `config/services.php`
  - Auth: `MAILGUN_DOMAIN`, `MAILGUN_SECRET`, `MAILGUN_ENDPOINT`
- Amazon SES - Supported mail transport
  - Config: `config/services.php`
  - Auth: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`
- Postmark - Supported mail transport
  - Config: `config/services.php`
  - Auth: `POSTMARK_TOKEN`

**Email Receiving (Inbound):**
- SendGrid Inbound Parse - Webhook-based email reception
  - Implementation: `packages/Webkul/Email/src/InboundEmailProcessor/SendgridEmailProcessor.php`
  - Config: `MAIL_RECEIVER_DRIVER=sendgrid` in `config/mail-receiver.php`
  - Receives raw email via webhook POST, parses headers/body/attachments
  - Does NOT support bulk folder processing (only individual message processing)
- IMAP (via webklex/laravel-imap) - Direct mailbox polling
  - Implementation: `packages/Webkul/Email/src/InboundEmailProcessor/WebklexImapEmailProcessor.php`
  - Config: `config/imap.php`, overridable from CRM admin settings via `core()->getConfigData()`
  - Auth: `IMAP_HOST`, `IMAP_PORT`, `IMAP_ENCRYPTION`, `IMAP_USERNAME`, `IMAP_PASSWORD`
  - Supports bulk folder traversal (processes messages from last 10 days)
  - Artisan command: `ProcessInboundEmails` (`packages/Webkul/Email/src/Console/Commands/ProcessInboundEmails.php`)
- Driver selection: `packages/Webkul/Email/src/Providers/EmailServiceProvider.php` binds `InboundEmailProcessor` contract based on `MAIL_RECEIVER_DRIVER`

**Outgoing Webhooks (User-Configured):**
- Custom webhook system for CRM automation
  - Service: `packages/Webkul/Automation/src/Services/WebhookService.php`
  - Model: `packages/Webkul/Automation/src/Models/Webhook.php`
  - Controller: `packages/Webkul/Admin/src/Http/Controllers/Settings/WebhookController.php`
  - Routes: `packages/Webkul/Admin/src/Routes/Admin/settings-routes.php` (prefix: `webhooks`)
  - Uses GuzzleHttp client with 30s timeout, 10s connect timeout
  - Supports methods: GET, POST, PUT, PATCH, DELETE
  - Supports content types: JSON, form-urlencoded, multipart/form-data, text/plain, XML
  - Configurable: endpoint URL, headers, query params, payload
  - Triggered by workflow automation engine

**Marketing Campaigns:**
- Email campaign system using Laravel Mail queues
  - Helper: `packages/Webkul/Marketing/src/Helpers/Campaign.php`
  - Mail: `packages/Webkul/Marketing/src/Mail/CampaignMail.php`
  - Sends to all persons' emails from the contact database
  - Artisan command: `packages/Webkul/Marketing/src/Console/Commands/CampaignCommand.php`

## Data Storage

**Databases:**
- MySQL 8.0 (Primary, default)
  - Connection: `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  - Config: `config/database.php`
  - Charset: utf8mb4, Collation: utf8mb4_unicode_ci
  - Strict mode: disabled (`'strict' => false`)
  - SSL support: `MYSQL_ATTR_SSL_CA`
  - Table prefix support: `DB_PREFIX`
- PostgreSQL (configured but not primary)
  - Connection available in `config/database.php` as `pgsql`
- SQLite (configured for testing)
  - Connection available in `config/database.php` as `sqlite`
- SQL Server (configured but not primary)
  - Connection available in `config/database.php` as `sqlsrv`

**ORM/Data Access:**
- Eloquent ORM (Laravel built-in) - Model layer
- Prettus L5 Repository pattern (`prettus/l5-repository`) - Repository abstraction layer
  - Config: `config/repository.php`
  - Pagination limit: 15 items
  - Cache: disabled by default
  - Repository classes in each package: `packages/Webkul/*/src/Repositories/`
- Konekt Concord Proxy pattern - Model resolution via proxy classes (e.g., `EmailProxy`, `UserProxy`)

**Migrations:**
- Distributed across packages: `packages/Webkul/*/src/Database/Migrations/`
- Core Laravel migrations: `database/migrations/` (failed_jobs, personal_access_tokens, job_batches, jobs)
- 70+ migration files across all packages

**File Storage:**
- Local filesystem (default) - `storage/app/public/`
  - Config: `config/filesystems.php`, default disk: `public`
  - Symlink: `public/storage` -> `storage/app/public`
- Amazon S3 (configured, optional)
  - Auth: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`
  - Config: `config/filesystems.php` `s3` disk

**Caching:**
- File-based (default) - `storage/framework/cache/data/`
  - Config: `config/cache.php`, `CACHE_DRIVER=file`
- Redis (optional) - Available if configured
  - Config: `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`
  - Client: phpredis (default) or predis
  - Separate DB for cache (DB 1) vs default (DB 0)
- Database, Memcached, DynamoDB also configured as options

**Sessions:**
- File-based (default) - `storage/framework/sessions/`
  - Config: `config/session.php`, `SESSION_DRIVER=file`
  - Lifetime: 120 minutes
  - Cookie: HTTP-only, SameSite=lax
- Redis, database, and other drivers available

## Authentication & Identity

**Auth Provider:**
- Custom (Laravel built-in auth) - Session-based authentication
  - Guard: `user` (session driver)
  - Model: `Webkul\User\Models\User` (`packages/Webkul/User/src/Models/User.php`)
  - Provider: Eloquent
  - Config: `config/auth.php`

**API Authentication:**
- Laravel Sanctum ^3.2 - Token-based API auth + SPA stateful auth
  - Config: `config/sanctum.php`
  - Guard: `['user']`
  - Token expiration: none (null)
  - Stateful domains: localhost, 127.0.0.1, APP_URL host
  - User model uses `HasApiTokens` trait

**Session Management:**
- Login: `packages/Webkul/Admin/src/Http/Controllers/User/SessionController.php`
- Password reset: `packages/Webkul/Admin/src/Http/Controllers/User/ForgotPasswordController.php`
- Password reset table: `user_password_resets` (expire: 60 min)
- Password confirmation timeout: 3 hours (10800 seconds)

**Authorization (RBAC):**
- Role-based with custom permissions
  - User has one Role, Role has permission_type ('custom' or 'all')
  - Permissions stored as array on Role model
  - `hasPermission()` method on User model
  - `bouncer()` helper for permission checks in controllers
  - User belongs to Groups (many-to-many via `user_groups`)
  - View permission scoping on User model (`view_permission` field)

## Monitoring & Observability

**Error Tracking:**
- Spatie Ignition ^2.0 (dev only) - Rich error pages
- Laravel Debugbar ^3.6 (dev only) - Debug toolbar
- No production error tracking service detected (no Sentry, Bugsnag, etc.)

**Logs:**
- Monolog (via Laravel) - `config/logging.php`
- Default channel: `stack` -> `single` file
- Log file: `storage/logs/laravel.log`
- Configurable: daily rotation (14 days), Slack webhook, Papertrail, Syslog, Stderr
- Slack logging: `LOG_SLACK_WEBHOOK_URL` (optional)
- Papertrail: `PAPERTRAIL_URL`, `PAPERTRAIL_PORT` (optional)

## CI/CD & Deployment

**Hosting:**
- Railway (indicated by trusted proxy configuration in `Dockerfile`)
- Docker container deployment (single container, no docker-compose)
- `Dockerfile` at project root

**CI Pipeline:**
- GitHub Actions (3 workflows in `.github/workflows/`)

**Workflow 1: CI (`ci.yml`):**
- Trigger: push, pull_request
- Matrix: PHP 8.2, 8.3 on ubuntu-latest
- Services: MySQL 8.0
- Steps: Composer install, configure .env, run Krayin installer, Pest tests (parallel)

**Workflow 2: Admin Playwright Tests (`admin_playwright_tests.yml`):**
- Trigger: push, pull_request
- Matrix: PHP 8.3, Node 22.13.1 on ubuntu-latest
- Sharded: 6 shards for parallel test execution
- Services: MySQL 8.0
- Steps: Install deps, install Playwright browsers, start Laravel server, run Playwright tests
- Artifacts: test results uploaded per shard

**Workflow 3: Linting Tests (`auto_commits.yml`):**
- Trigger: push, pull_request
- Runs Laravel Pint (PHP code style)
- Auto-commits linted files back to the branch via `stefanzweifel/git-auto-commit-action@v5`

## Queue System

**Default:** Sync (no async processing by default)
- Config: `config/queue.php`, `QUEUE_CONNECTION=sync`
- Database queue driver available (jobs table, job_batches table with migrations)
- Redis queue available if configured
- SQS available: `SQS_PREFIX`, `SQS_QUEUE`, `SQS_SUFFIX`
- Failed jobs: `failed_jobs` table (database-uuids driver)
- Used by: Marketing campaign emails (`Mail::queue`), DataTransfer import jobs

## Broadcasting

**Default:** null (disabled)
- Config: `config/broadcasting.php`, `BROADCAST_DRIVER=null`
- Pusher configured but not active: `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_CLUSTER`
- Ably configured but not active: `ABLY_KEY`
- Redis broadcasting available
- BroadcastServiceProvider is commented out in `config/app.php`

## Environment Configuration

**Required env vars (minimum for operation):**
- `APP_KEY` - Encryption key (generated via `php artisan key:generate`)
- `APP_URL` - Application base URL
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` - Database
- `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT` - Outbound email (if sending mail)
- `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` - Sender identity

**Optional env vars (feature-dependent):**
- `MAIL_RECEIVER_DRIVER` - Inbound email processing (sendgrid or webklex-imap)
- `IMAP_*` - IMAP email reception
- `AWS_*` - S3 storage, SES email, SQS queues
- `MAILGUN_*` - Mailgun email
- `POSTMARK_TOKEN` - Postmark email
- `PUSHER_*` - Real-time broadcasting (currently disabled)
- `REDIS_*` - Redis cache/queue/sessions
- `LOG_SLACK_WEBHOOK_URL` - Slack error alerts

**Secrets location:**
- `.env` file in project root (gitignored)
- `.env.example` provides template with placeholder values
- No external secret management detected (no Vault, AWS Secrets Manager, etc.)

## Webhooks & Callbacks

**Incoming:**
- SendGrid Inbound Parse webhook - Receives inbound emails for processing
  - Processor: `packages/Webkul/Email/src/InboundEmailProcessor/SendgridEmailProcessor.php`
  - Expects raw email payload via POST
- Web Form submissions - Public-facing lead capture forms
  - Controller: `packages/Webkul/WebForm/src/Http/Controllers/WebFormController.php`
  - CORS enabled for `admin/web-forms/forms/*` paths (`config/cors.php`)
  - Embeddable via JavaScript: `formJS()` endpoint returns JS embed code
  - Form submission: `formStore()` creates leads or persons

**Outgoing:**
- User-configured webhooks via Automation module
  - Service: `packages/Webkul/Automation/src/Services/WebhookService.php`
  - Triggered by workflow rules on entity events (Lead, Person, Activity, Quote)
  - Entity types for automation: Lead, Person, Activity, Quote
  - Workflow helpers: `packages/Webkul/Automation/src/Helpers/Entity/Lead.php`, `Person.php`, `Activity.php`, `Quote.php`
  - Configurable per-webhook: URL, method, headers, query params, payload

## Data Import/Export

**Import:**
- CSV and Excel file import via `maatwebsite/excel`
  - Sources: `packages/Webkul/DataTransfer/src/Helpers/Sources/CSV.php`, `Excel.php`
  - Supported entities: Leads, Persons, Products
  - Importers: `packages/Webkul/DataTransfer/src/Helpers/Importers/{Leads,Persons,Products}/Importer.php`
  - Uses Laravel job batching for async processing
  - Jobs: `ImportBatch`, `IndexBatch`, `LinkBatch`, `Completed`
  - Config: `packages/Webkul/DataTransfer/src/Config/importers.php`

**Export:**
- PDF generation via `barryvdh/laravel-dompdf` and `mpdf/mpdf`
- PDF parsing via `smalot/pdfparser`
- Excel export capabilities via `maatwebsite/excel`

---

*Integration audit: 2026-02-14*
