# Technology Stack

**Analysis Date:** 2026-02-14

## Languages

**Primary:**
- PHP ^8.2 - All backend logic, controllers, models, migrations, service providers
- JavaScript (ES Modules) - Frontend Vue.js components, Vite build pipeline, Playwright E2E tests

**Secondary:**
- TypeScript - Playwright E2E test configuration and test files (`packages/Webkul/Admin/tests/e2e-pw/`)
- CSS (Tailwind) - Styling via Tailwind CSS utility classes
- Blade (PHP templates) - Server-rendered views in `packages/Webkul/*/src/Resources/views/`

## Runtime

**Environment:**
- PHP 8.2+ (Dockerfile uses `dunglas/frankenphp:php8.2-bookworm`)
- Node.js 22 (installed in Docker, CI uses `22.13.1`)

**Package Manager:**
- Composer v2 - PHP dependency management
  - Lockfile: `composer.lock` present
- npm - JavaScript dependency management
  - Lockfile: root and per-package (`packages/Webkul/Admin/`, `packages/Webkul/Installer/`, `packages/Webkul/WebForm/`)

**Required PHP Extensions:**
- pdo_mysql, gd, zip, calendar, imap, intl, bcmath, opcache (from `Dockerfile`)
- curl, fileinfo, mbstring, openssl, tokenizer (from CI config)

## Frameworks

**Core:**
- Laravel ^10.0 (`laravel/framework`) - Full MVC web framework
- Vue.js ^3.4.21 - Frontend reactive UI (Options API, `vue.esm-bundler`)
- Konekt Concord ^1.10 (`konekt/concord`) - Modular architecture for Laravel packages

**Testing:**
- Pest ^2.6 (`pestphp/pest`) - PHP unit/integration testing (with Laravel plugin)
- PHPUnit ^10.0 - Underlying test runner
- Playwright ^1.50.1 (`@playwright/test`) - E2E browser testing (Chromium)

**Build/Dev:**
- Vite ^5.0+ (`vite`) - JavaScript/CSS bundler with HMR
- laravel-vite-plugin ^1.0 - Laravel-Vite integration
- Tailwind CSS ^3.3.2 - Utility-first CSS framework
- PostCSS ^8.4.23 - CSS processing
- Autoprefixer ^10.4.16 - Vendor prefix addition
- Laravel Pint ^1.16 - PHP code style fixer (Laravel preset)
- StyleCI - External code style service (`.styleci.yml`)
- Laravel Debugbar ^3.6 (dev only) - Debug toolbar
- Laravel Sail ^1.0.1 (dev only) - Docker dev environment
- Spatie Ignition ^2.0 (dev only) - Error page

## Key Dependencies

**Critical (Business Logic):**
- `prettus/l5-repository` ^2.7.9 - Repository pattern implementation for all data access
- `webklex/laravel-imap` ^5.3 - IMAP email client for inbound email processing
- `laravel/sanctum` ^3.2 - API token authentication (SPA stateful + token-based)
- `laravel/ui` ^4.5 - Authentication scaffolding
- `maatwebsite/excel` ^3.1 - Excel/CSV import/export for data transfer
- `barryvdh/laravel-dompdf` ^2.0.0 - PDF generation (quotes, reports)
- `mpdf/mpdf` ^8.2 - Alternative PDF generation library
- `smalot/pdfparser` ^2.11 - PDF parsing for data extraction

**Frontend (Production):**
- `vee-validate` ^4.9.1 + `@vee-validate/rules` + `@vee-validate/i18n` - Form validation
- `vue-cal` ^4.9.0 - Calendar component for activities
- `flatpickr` ^4.6.13 + `vue-flatpickr` ^2.3.0 - Date/time picker
- `vuedraggable` ^4.1.0 - Drag-and-drop (pipeline/kanban views)
- `chartjs-chart-funnel` ^4.2.1 - Funnel chart visualization (sales pipeline)
- `dompurify` ^3.1.7 - HTML sanitization
- `mitt` ^3.0.1 - Event bus for Vue components
- `axios` ^1.6.4+ - HTTP client

**Infrastructure:**
- `guzzlehttp/guzzle` ^7.0.1 - HTTP client for outgoing webhooks
- `doctrine/dbal` ^3.0 - Database abstraction for migrations
- `diglactic/laravel-breadcrumbs` ^8.0 - Breadcrumb navigation
- `enshrined/svg-sanitize` ^0.21.0 - SVG upload sanitization
- `khaled.alshamaa/ar-php` ^6.3 - Arabic language support

**Dev/Testing:**
- `fakerphp/faker` ^1.9.1 - Test data generation
- `mockery/mockery` ^1.4.2 - PHP mocking
- `nunomaduro/collision` ^7.0 - CLI error reporting
- `krayin/krayin-package-generator` dev-master - Package scaffolding

## Configuration

**Environment:**
- `.env` file (gitignored) with `.env.example` as template
- Key env vars: `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`, `APP_TIMEZONE`, `APP_LOCALE`, `APP_CURRENCY`
- Admin path configurable: `APP_ADMIN_PATH` (default: `admin`)
- Database: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_PREFIX`
- Mail: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_DOMAIN`
- Mail receiver: `MAIL_RECEIVER_DRIVER` (sendgrid or webklex-imap)
- IMAP: `IMAP_HOST`, `IMAP_PORT`, `IMAP_ENCRYPTION`, `IMAP_USERNAME`, `IMAP_PASSWORD`
- Vite: `VITE_HOST`, `VITE_PORT`
- Cache: `CACHE_DRIVER` (default: file)
- Queue: `QUEUE_CONNECTION` (default: sync)
- Session: `SESSION_DRIVER` (default: file)
- Broadcast: `BROADCAST_DRIVER` (default: null/log)

**Build:**
- Root: `vite.config.js` - Unused base config
- Admin panel: `packages/Webkul/Admin/vite.config.js` - Main frontend build
  - Entry points: `src/Resources/assets/css/app.css`, `src/Resources/assets/js/app.js`, `src/Resources/assets/js/chart.js`
  - Output: `public/admin/build/`
  - Hot file: `public/admin-vite.hot`
- Installer: `packages/Webkul/Installer/vite.config.js` - Installer frontend
  - Output: `public/installer/build/`
- WebForm: `packages/Webkul/WebForm/vite.config.js` - Embeddable form frontend
  - Output: `public/webform/build/`
- Tailwind: `packages/Webkul/Admin/tailwind.config.js`
  - Dark mode: class-based
  - Custom fonts: Inter, icomoon (icon font)
  - Custom color: `brandColor` via CSS variable `--brand-color`
  - Content: `./src/Resources/**/*.blade.php`, `./src/Resources/**/*.js`
- PostCSS: `packages/Webkul/Admin/postcss.config.cjs`
- Krayin Vite config: `config/krayin-vite.php` - Maps package names to Vite build directories

**Code Style:**
- PHP: Laravel Pint (`pint.json`) - Laravel preset with aligned `=>` operators
- PHP: StyleCI (`.styleci.yml`) - Laravel preset, `no_unused_imports` disabled
- Editor: `.editorconfig` - 4-space indent, UTF-8, LF line endings

## Modular Package Architecture

The application uses Konekt Concord for modular package management. Each domain module lives under `packages/Webkul/`:

| Package | Namespace | Purpose |
|---------|-----------|---------|
| Activity | `Webkul\Activity` | Activities, tasks, calls, meetings |
| Admin | `Webkul\Admin` | Admin panel UI, controllers, views |
| Attribute | `Webkul\Attribute` | Dynamic EAV attributes system |
| Automation | `Webkul\Automation` | Workflows and webhooks |
| Contact | `Webkul\Contact` | Persons and organizations |
| Core | `Webkul\Core` | Core config, countries, base classes |
| DataGrid | `Webkul\DataGrid` | Reusable data table component |
| DataTransfer | `Webkul\DataTransfer` | CSV/Excel import functionality |
| Email | `Webkul\Email` | Email sending/receiving, IMAP |
| EmailTemplate | `Webkul\EmailTemplate` | Email template management |
| Installer | `Webkul\Installer` | Application installer/seeder |
| Lead | `Webkul\Lead` | Leads, pipelines, stages |
| Marketing | `Webkul\Marketing` | Campaign and event management |
| Product | `Webkul\Product` | Product catalog with inventory |
| Quote | `Webkul\Quote` | Quotes/proposals |
| Tag | `Webkul\Tag` | Tagging system |
| User | `Webkul\User` | Users, roles, groups, permissions |
| Warehouse | `Webkul\Warehouse` | Warehouse/location management |
| WebForm | `Webkul\WebForm` | Embeddable lead capture forms |

Modules are registered in `config/concord.php` and `config/app.php` providers array.

## Platform Requirements

**Development:**
- PHP 8.2+ with extensions: pdo_mysql, gd, zip, imap, intl, bcmath, mbstring, curl, openssl
- Node.js 22+ with npm
- MySQL 8.0 (primary database)
- Composer v2
- Optional: Redis (cache/queue/sessions), Mailhog (local email testing)

**Production:**
- Docker container based on `dunglas/frankenphp:php8.2-bookworm` (`Dockerfile`)
- Target deployment: Railway (trusted proxy config references Railway)
- Exposed port: 8000 (configurable via `PORT` env var)
- CMD: `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}`
- No docker-compose file present (single-container deployment)

**Internationalization:**
- Supported locales: Arabic, English, Spanish, Persian, Portuguese (BR), Turkish, Vietnamese
- Configured in `config/app.php` `available_locales`
- Default: English, Fallback: English

---

*Stack analysis: 2026-02-14*
