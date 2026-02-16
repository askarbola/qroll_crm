# Codebase Structure

**Analysis Date:** 2026-02-14

## Directory Layout

```
qroll_crm/
├── app/                    # Thin Laravel shell (mostly boilerplate)
│   ├── Console/            # Console Kernel
│   ├── Exceptions/         # Default exception handler (overridden by Admin package)
│   ├── Http/               # HTTP Kernel, base middleware, base Controller
│   ├── Models/             # Default User model (not used - CRM uses Webkul\User)
│   └── Providers/          # App/Auth/Broadcast/Event/Route service providers
├── bootstrap/              # Laravel application bootstrap
│   └── app.php             # Application instance creation
├── config/                 # Laravel and package configuration files
├── database/               # Core Laravel migrations, factories, seeders
│   ├── factories/          # Default UserFactory
│   ├── migrations/         # Framework migrations (jobs, failed_jobs, tokens)
│   └── seeders/            # DatabaseSeeder (delegates to Installer seeders)
├── lang/                   # Application-level language files
├── packages/               # *** ALL DOMAIN LOGIC LIVES HERE ***
│   └── Webkul/             # Vendor namespace for all modules
│       ├── Activity/       # Activity tracking (calls, meetings, notes, etc.)
│       ├── Admin/          # Main admin panel (controllers, views, routes, DataGrids)
│       ├── Attribute/      # EAV attribute system
│       ├── Automation/     # Workflows and webhooks
│       ├── Contact/        # Persons and Organizations
│       ├── Core/           # Framework utilities (Repository base, Vite, Menu, ACL, etc.)
│       ├── DataGrid/       # Reusable data table abstraction
│       ├── DataTransfer/   # CSV/file import system
│       ├── Email/          # Email management (IMAP, compose, attachments)
│       ├── EmailTemplate/  # Email template CRUD
│       ├── Installer/      # Installation wizard + database seeders
│       ├── Lead/           # Lead management (pipelines, stages, sources, types)
│       ├── Marketing/      # Marketing campaigns and events
│       ├── Product/        # Product catalog
│       ├── Quote/          # Quotation management
│       ├── Tag/            # Tagging system
│       ├── User/           # Admin user management (users, roles, groups)
│       ├── Warehouse/      # Warehouse/location management
│       └── WebForm/        # Public-facing lead capture web forms
├── public/                 # Web server document root
│   └── index.php           # Application entry point
├── resources/              # Default Laravel resources (mostly unused, assets in packages)
├── routes/                 # Base Laravel routes (minimal, real routes in Admin package)
│   ├── api.php             # API routes placeholder
│   ├── breadcrumbs.php     # Breadcrumb definitions
│   ├── web.php             # Default web route (welcome view)
│   └── console.php         # Console route definitions
├── storage/                # Laravel storage (logs, cache, sessions, uploads)
├── tests/                  # Test suite root
│   ├── Feature/            # Feature tests
│   ├── Unit/               # Unit tests
│   ├── Pest.php            # Pest configuration
│   └── TestCase.php        # Base test case
├── artisan                 # Laravel CLI entry point
├── composer.json           # PHP dependencies and PSR-4 autoload mapping
├── Dockerfile              # Docker build (FrankenPHP + PHP 8.2)
├── package.json            # Node.js dependencies (Vite, axios)
├── phpunit.xml             # PHPUnit/Pest configuration
├── pint.json               # Laravel Pint code style configuration
└── vite.config.js          # Root Vite config (not primary - packages have their own)
```

## Directory Purposes

**`app/` (Laravel Shell):**
- Purpose: Minimal boilerplate required by Laravel. Almost no custom logic.
- Contains: HTTP Kernel with middleware stack, base Controller, service providers
- Key files: `app/Http/Kernel.php` (middleware registration including `Webkul\Installer\Http\Middleware\CanInstall`)
- Note: `app/Models/User.php` exists but is NOT the primary user model. Use `Webkul\User\Models\User` instead.

**`packages/Webkul/` (Domain Modules):**
- Purpose: All business logic, organized as self-contained Laravel packages
- Contains: 18 domain packages, each following a consistent internal structure
- Key pattern: Each package has `src/` containing: `Contracts/`, `Models/`, `Repositories/`, `Providers/`, `Database/Migrations/`

**Standard Package Internal Structure:**
```
packages/Webkul/{PackageName}/src/
├── Config/                 # Package config files (optional)
├── Contracts/              # Interfaces for each model
├── Database/
│   ├── Factories/          # Model factories (optional)
│   ├── Migrations/         # Database migrations
│   └── Seeders/            # Database seeders (optional, mainly in Installer)
├── Helpers/                # Business logic helpers (optional)
├── Listeners/              # Event listeners (optional)
├── Models/                 # Eloquent models + Proxy classes
├── Providers/
│   ├── ModuleServiceProvider.php  # Registers models with Concord
│   └── {Name}ServiceProvider.php  # Boots routes, views, migrations, config
├── Repositories/           # Data access layer
├── Resources/              # Views, assets, lang (only in Admin, Core, WebForm, Installer)
│   ├── assets/             # JS, CSS, images, fonts
│   ├── lang/               # Translation files
│   └── views/              # Blade templates
├── Routes/                 # Route definitions (only in Admin, WebForm, Installer)
├── Services/               # Service classes (optional)
└── Traits/                 # Shared traits (optional)
```

**`packages/Webkul/Admin/` (Admin Package -- the largest):**
- Purpose: The main application package. Contains ALL admin-facing controllers, routes, views, DataGrids, and frontend assets.
- Contains: HTTP layer, Blade views, Vue.js app, DataGrid implementations, Helpers/Reporting, Listeners, Notifications
- Key subdirectories:
  - `src/Http/Controllers/` - All admin controllers organized by domain
  - `src/Routes/Admin/` - All admin route files
  - `src/DataGrids/` - DataGrid implementations per domain
  - `src/Resources/views/` - Blade templates organized by domain
  - `src/Resources/assets/js/` - Vue.js 3 application and plugins
  - `src/Config/` - menu.php, acl.php, core_config.php, attribute configs
  - `src/Helpers/` - Dashboard.php and Reporting/ helpers

**`packages/Webkul/Core/` (Core Package):**
- Purpose: Shared infrastructure: base Repository class, facades, Vite, Menu, ACL, SystemConfig
- Contains: Base abstractions used by all other packages
- Key files:
  - `src/Eloquent/Repository.php` - Base repository all others extend
  - `src/Core.php` - Core helper (currencies, config, countries)
  - `src/Menu.php` - Admin menu builder
  - `src/Acl.php` - ACL registry
  - `src/SystemConfig.php` - System configuration manager
  - `src/Vite.php` - Multi-package Vite asset helper

**`config/` (Laravel Configuration):**
- Purpose: Standard Laravel config plus Krayin-specific configs
- Key files:
  - `config/concord.php` - Registers all Webkul modules (17 ModuleServiceProviders)
  - `config/krayin-vite.php` - Vite build config per package (admin, installer, webform)
  - `config/imap.php` - IMAP email configuration
  - `config/repository.php` - Prettus repository configuration
  - `config/app.php` - Includes custom `admin_path` config key

## Key File Locations

**Entry Points:**
- `public/index.php`: HTTP entry point
- `artisan`: CLI entry point
- `bootstrap/app.php`: Application instance factory

**Configuration:**
- `config/concord.php`: Module registration (add new packages here)
- `config/krayin-vite.php`: Frontend asset build configuration
- `config/app.php`: Application settings including `admin_path`
- `packages/Webkul/Admin/src/Config/menu.php`: Admin navigation menu definition
- `packages/Webkul/Admin/src/Config/acl.php`: Permission definitions

**Core Logic (by domain):**
- Leads: `packages/Webkul/Lead/src/Repositories/LeadRepository.php`, `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`
- Contacts: `packages/Webkul/Contact/src/Repositories/PersonRepository.php`, `packages/Webkul/Admin/src/Http/Controllers/Contact/Persons/PersonController.php`
- Products: `packages/Webkul/Product/src/Repositories/ProductRepository.php`, `packages/Webkul/Admin/src/Http/Controllers/Products/ProductController.php`
- Quotes: `packages/Webkul/Quote/src/Repositories/QuoteRepository.php`, `packages/Webkul/Admin/src/Http/Controllers/Quote/QuoteController.php`
- Activities: `packages/Webkul/Activity/src/Repositories/ActivityRepository.php`, `packages/Webkul/Admin/src/Http/Controllers/Activity/ActivityController.php`
- Emails: `packages/Webkul/Email/src/Repositories/EmailRepository.php`, `packages/Webkul/Admin/src/Http/Controllers/Mail/EmailController.php`
- Automation: `packages/Webkul/Automation/src/Repositories/WorkflowRepository.php`, `packages/Webkul/Admin/src/Http/Controllers/Settings/WorkflowController.php`
- Dashboard: `packages/Webkul/Admin/src/Helpers/Dashboard.php`, `packages/Webkul/Admin/src/Http/Controllers/DashboardController.php`

**Frontend:**
- `packages/Webkul/Admin/src/Resources/assets/js/app.js`: Vue.js 3 application root
- `packages/Webkul/Admin/src/Resources/assets/js/plugins/`: Vue plugins (admin, axios, emitter, flatpickr, vee-validate, vue-cal, draggable, createElement)
- `packages/Webkul/Admin/src/Resources/assets/js/directives/`: Vue directives (debounce, dompurify, tooltip)
- `packages/Webkul/Admin/src/Resources/assets/css/app.css`: Main CSS (Tailwind CSS)
- `packages/Webkul/Admin/src/Resources/views/components/`: Reusable Blade components (accordion, datagrid, drawer, dropdown, form, layouts, modal, etc.)

**Testing:**
- `tests/Feature/AuthenticationTest.php`: Auth feature test
- `tests/Unit/BasicTest.php`: Basic unit test
- `tests/Pest.php`: Pest test runner configuration
- `phpunit.xml`: PHPUnit configuration

**Database:**
- Migrations are distributed across packages: `packages/Webkul/*/src/Database/Migrations/`
- Seeders primarily in: `packages/Webkul/Installer/src/Database/Seeders/`
- Core framework migrations: `database/migrations/` (jobs, tokens)

## Naming Conventions

**Files:**
- Controllers: `PascalCase` + `Controller.php` (e.g., `LeadController.php`, `PersonController.php`)
- Models: `PascalCase.php` (e.g., `Lead.php`, `Person.php`)
- Model Proxies: `PascalCase` + `Proxy.php` (e.g., `LeadProxy.php`)
- Repositories: `PascalCase` + `Repository.php` (e.g., `LeadRepository.php`)
- DataGrids: `PascalCase` + `DataGrid.php` (e.g., `LeadDataGrid.php`)
- Form Requests: `PascalCase` + `Form.php` or descriptive (e.g., `LeadForm.php`, `MassDestroyRequest.php`)
- API Resources: `PascalCase` + `Resource.php` (e.g., `LeadResource.php`)
- Service Providers: `PascalCase` + `ServiceProvider.php` (e.g., `LeadServiceProvider.php`)
- Blade views: `kebab-case.blade.php` or `snake_case.blade.php` (e.g., `index.blade.php`, `create.blade.php`)
- Migrations: Laravel timestamp format `YYYY_MM_DD_HHMMSS_description.php`
- Config files: `snake_case.php` (e.g., `core_config.php`, `attribute_lookups.php`)
- Route files: `kebab-case-routes.php` (e.g., `leads-routes.php`, `auth-routes.php`)

**Directories:**
- Package names: `PascalCase` (e.g., `Lead`, `Contact`, `DataGrid`, `EmailTemplate`)
- Controller subdirectories: `PascalCase` matching domain (e.g., `Lead/`, `Contact/Persons/`, `Settings/`)
- View directories: `kebab-case` or `lowercase` (e.g., `leads/`, `contacts/`, `settings/`)
- DataGrid subdirectories: `PascalCase` (e.g., `Lead/`, `Contact/`, `Settings/`)

**Namespaces:**
- All packages: `Webkul\{PackageName}\` (e.g., `Webkul\Lead\`, `Webkul\Contact\`)
- Controllers: `Webkul\Admin\Http\Controllers\{Domain}\` (e.g., `Webkul\Admin\Http\Controllers\Lead\`)
- Models: `Webkul\{Package}\Models\` (e.g., `Webkul\Lead\Models\Lead`)
- Contracts: `Webkul\{Package}\Contracts\` (e.g., `Webkul\Lead\Contracts\Lead`)
- Repositories: `Webkul\{Package}\Repositories\` (e.g., `Webkul\Lead\Repositories\LeadRepository`)

**Route Names:**
- Pattern: `admin.{domain}.{action}` (e.g., `admin.leads.index`, `admin.leads.store`, `admin.leads.view`)
- Nested: `admin.{domain}.{sub}.{action}` (e.g., `admin.leads.tags.attach`, `admin.contacts.persons.index`)
- Settings: `admin.settings.{entity}.{action}` (e.g., `admin.settings.pipelines.index`)

## Where to Add New Code

**New CRM Domain Feature (e.g., "Invoices"):**
1. Create package: `packages/Webkul/Invoice/src/`
2. Add standard structure: `Contracts/`, `Models/`, `Repositories/`, `Providers/`, `Database/Migrations/`
3. Create `ModuleServiceProvider.php` registering models
4. Create `InvoiceServiceProvider.php` for migrations/config
5. Register in `config/concord.php` modules array
6. Add PSR-4 autoload entry in `composer.json`: `"Webkul\\Invoice\\": "packages/Webkul/Invoice/src"`
7. Add controllers in `packages/Webkul/Admin/src/Http/Controllers/Invoice/`
8. Add routes in `packages/Webkul/Admin/src/Routes/Admin/invoice-routes.php` and require it from `web.php`
9. Add DataGrid in `packages/Webkul/Admin/src/DataGrids/Invoice/`
10. Add views in `packages/Webkul/Admin/src/Resources/views/invoices/`
11. Add menu item in `packages/Webkul/Admin/src/Config/menu.php`
12. Add ACL permissions in `packages/Webkul/Admin/src/Config/acl.php`

**New Controller Action in Existing Domain:**
- Add method to existing controller in `packages/Webkul/Admin/src/Http/Controllers/{Domain}/`
- Add route in corresponding `packages/Webkul/Admin/src/Routes/Admin/{domain}-routes.php`

**New Repository Method:**
- Add to the relevant repository in `packages/Webkul/{Package}/src/Repositories/`

**New DataGrid:**
- Create in `packages/Webkul/Admin/src/DataGrids/{Domain}/`
- Extend `Webkul\DataGrid\DataGrid`
- Implement `prepareQueryBuilder()`, `prepareColumns()`, `prepareActions()`

**New Blade Component:**
- Create in `packages/Webkul/Admin/src/Resources/views/components/`
- Use as `<x-admin::component-name />`

**New Migration:**
- Create in the owning package's `src/Database/Migrations/` directory
- Follow Laravel timestamp naming convention

**New Event Listener:**
- Add listener class in `packages/Webkul/Admin/src/Listeners/` or the relevant package
- Register in `packages/Webkul/Admin/src/Providers/EventServiceProvider.php`

**Utilities/Helpers:**
- Shared helpers: `packages/Webkul/Core/src/Helpers/`
- Domain-specific helpers: `packages/Webkul/{Package}/src/Helpers/`
- Admin-specific helpers: `packages/Webkul/Admin/src/Helpers/`

## Special Directories

**`packages/Webkul/Installer/`:**
- Purpose: First-time installation wizard and ALL database seeders for initial data (attributes, pipelines, stages, users, workflows, email templates, countries)
- Generated: No
- Committed: Yes
- Key: `src/Database/Seeders/` contains the canonical seed data for a fresh install

**`storage/`:**
- Purpose: Laravel storage (logs, cache, compiled views, sessions, uploaded files)
- Generated: Yes (at runtime)
- Committed: No (gitignored except directory structure)

**`public/admin/build/` and `public/installer/build/` and `public/webform/build/`:**
- Purpose: Compiled Vite frontend assets for each package namespace
- Generated: Yes (by `npm run build` or Vite)
- Committed: Varies (check .gitignore)

**`bootstrap/cache/`:**
- Purpose: Laravel framework cache (compiled config, routes, services)
- Generated: Yes (by `php artisan optimize`)
- Committed: No

**`.github/workflows/`:**
- Purpose: CI/CD pipeline definitions
- Contains: `ci.yml`, `admin_playwright_tests.yml`, `auto_commits.yml`

---

*Structure analysis: 2026-02-14*
