# Architecture

**Analysis Date:** 2026-02-14

## Pattern Overview

**Overall:** Modular Monolith (Laravel + Konekt Concord Module System)

This is **Krayin CRM**, a Laravel 10 application using a modular package architecture powered by Konekt Concord. The core Laravel `app/` directory is a thin shell; all domain logic lives in `packages/Webkul/*` as self-contained modules.

**Key Characteristics:**
- 18 self-contained Webkul packages under `packages/Webkul/`, each with its own Models, Repositories, Migrations, and Providers
- Repository pattern (via `prettus/l5-repository`) as the primary data access abstraction
- Konekt Concord for module registration, model proxying, and cross-package model resolution
- Blade + Vue.js 3 hybrid frontend (server-rendered Blade templates with Vue.js components mounted inline)
- Event-driven side effects via Laravel Events (`Event::dispatch('lead.create.after', $lead)`)
- ACL-based authorization via a custom Bouncer system
- EAV (Entity-Attribute-Value) pattern for dynamic custom attributes on Leads, Persons, Products, etc.

## Layers

**HTTP / Presentation Layer (Admin Package):**
- Purpose: Handle all web requests, render views, return JSON responses
- Location: `packages/Webkul/Admin/src/Http/`
- Contains: Controllers (`Http/Controllers/`), Middleware (`Http/Middleware/`), Form Requests (`Http/Requests/`), API Resources (`Http/Resources/`)
- Depends on: Domain Repositories from all packages, DataGrids, Helpers
- Used by: Routes defined in `packages/Webkul/Admin/src/Routes/`

**Routes Layer:**
- Purpose: Define all URL endpoints for admin panel and front-facing pages
- Location: `packages/Webkul/Admin/src/Routes/Admin/` (authenticated admin routes) and `packages/Webkul/Admin/src/Routes/Front/` (public routes)
- Contains: Route definition files organized by domain: `leads-routes.php`, `contacts-routes.php`, `settings-routes.php`, `mail-routes.php`, `products-routes.php`, `activities-routes.php`, `quote-routes.php`, `auth-routes.php`, `configuration-routes.php`, `rest-routes.php`
- Depends on: Controllers in `Admin/Http/Controllers/`
- Used by: `AdminServiceProvider` which registers them with `web`, `admin_locale`, `user` middleware under a configurable admin URL prefix

**Repository Layer:**
- Purpose: Abstract database operations, provide business logic around CRUD
- Location: Each package has its own `Repositories/` directory, e.g., `packages/Webkul/Lead/src/Repositories/`
- Contains: Repository classes extending `Webkul\Core\Eloquent\Repository` (which extends `Prettus\Repository\Eloquent\BaseRepository`)
- Depends on: Eloquent Models, other Repositories (cross-package), AttributeValueRepository for EAV
- Used by: Controllers, Listeners, Helpers, Services
- Key pattern: Constructor injection of dependencies, `$fieldSearchable` for filtering, overridden `create()`/`update()` for business logic

**Model Layer:**
- Purpose: Define Eloquent models, relationships, and database schema
- Location: Each package has `Models/` and `Contracts/` directories
- Contains: Eloquent Models (e.g., `Lead.php`), Proxy classes (e.g., `LeadProxy.php`), Contract interfaces (e.g., `Contracts/Lead.php`)
- Depends on: Other package models via Proxy classes
- Used by: Repositories, Eloquent queries
- Key pattern: Every model has a `Contract` interface + a `Proxy` class. The Proxy (via Concord) allows model swapping. Cross-package relationships use `XProxy::modelClass()` instead of direct class references.

**DataGrid Layer:**
- Purpose: Provide sortable, filterable, paginated data tables
- Location: `packages/Webkul/Admin/src/DataGrids/` and `packages/Webkul/DataGrid/src/`
- Contains: Domain-specific DataGrid classes inheriting from abstract `Webkul\DataGrid\DataGrid`
- Depends on: Database queries (raw query builders), Column/Action/MassAction value objects
- Used by: Controllers via `datagrid(LeadDataGrid::class)->process()` helper call

**View / Frontend Layer:**
- Purpose: Render the admin UI
- Location: `packages/Webkul/Admin/src/Resources/views/` (Blade templates) and `packages/Webkul/Admin/src/Resources/assets/` (JS/CSS)
- Contains: Blade views organized by domain, anonymous Blade components (`views/components/`), Vue.js app (`assets/js/app.js`)
- Depends on: `admin::` view namespace, Blade components prefixed `x-admin::`, Krayin Vite for asset compilation
- Used by: Controllers returning `view('admin::leads.index', ...)`

**Configuration Layer:**
- Purpose: Define menu structure, ACL permissions, system config, attribute config
- Location: `packages/Webkul/Admin/src/Config/` (menu.php, acl.php, core_config.php, attribute_lookups.php, attribute_entity_types.php)
- Contains: Array-based config files registered in service providers
- Depends on: Nothing
- Used by: Core facades (Menu, Acl, SystemConfig), Bouncer

**Service Provider Layer:**
- Purpose: Bootstrap each package (register routes, views, migrations, facades, config, middleware)
- Location: Each package has `Providers/` with a `*ServiceProvider.php` and a `ModuleServiceProvider.php`
- Contains: `ModuleServiceProvider` (registers models with Concord) and domain `ServiceProvider` (boots routes, views, migrations, config)
- Depends on: Laravel Service Container
- Used by: Concord module loader (`config/concord.php`) and composer autoload

## Data Flow

**Web Request (e.g., Create Lead):**

1. Request hits `public/index.php` -> Laravel HTTP Kernel
2. Middleware stack: `web` -> `admin_locale` (sets locale) -> `user` (Bouncer auth + ACL check)
3. Route matches `POST /admin/leads/create` -> `LeadController@store`
4. Controller validates via `LeadForm` FormRequest (dynamic rules from EAV attributes)
5. `Event::dispatch('lead.create.before')` fires
6. `LeadRepository::create()` handles: person creation/lookup, lead persistence, EAV attribute values save, product association
7. `Event::dispatch('lead.create.after', $lead)` fires -> `Webkul\Admin\Listeners\Lead@linkToEmail`
8. Controller returns redirect or JSON response

**DataGrid Request (e.g., Lead List):**

1. AJAX request to `GET /admin/leads` (with `request()->ajax()`)
2. Controller detects AJAX, calls `datagrid(LeadDataGrid::class)->process()`
3. DataGrid builds query, applies filters/sort from request params, paginates
4. Returns JSON with columns, rows, pagination meta

**Kanban View (Lead Pipeline):**

1. `GET /admin/leads/get?pipeline_id=X` -> `LeadController@get`
2. Loads pipeline and its stages
3. For each stage, queries leads with `RequestCriteria`, applies Bouncer user filtering
4. Returns JSON with stages containing paginated leads as `LeadResource` collections

**State Management:**
- Server-side: Laravel session (database/file), Eloquent models as source of truth
- Client-side: Vue.js 3 component-level state, no Vuex/Pinia global store
- Auth state: Laravel `auth` guard named `user` (not default `web`)

## Key Abstractions

**Repository Pattern:**
- Purpose: Encapsulate all data access and business logic for each entity
- Base: `Webkul\Core\Eloquent\Repository` at `packages/Webkul/Core/src/Eloquent/Repository.php`
- Examples: `packages/Webkul/Lead/src/Repositories/LeadRepository.php`, `packages/Webkul/Contact/src/Repositories/PersonRepository.php`
- Pattern: Each repository declares `model()` returning a Contract interface. Concord resolves the concrete model class. Repositories inject other repositories for cross-domain operations.

**Concord Model Proxy:**
- Purpose: Allow model classes to be swapped/extended without changing consuming code
- Examples: `packages/Webkul/Lead/src/Models/LeadProxy.php`, `packages/Webkul/Contact/src/Models/PersonProxy.php`
- Pattern: `LeadProxy extends ModelProxy`. Relationships use `PersonProxy::modelClass()` instead of `Person::class`.

**EAV (Entity-Attribute-Value):**
- Purpose: Support dynamic/custom attributes on entities without schema changes
- Key files: `packages/Webkul/Attribute/src/Models/Attribute.php`, `packages/Webkul/Attribute/src/Models/AttributeValue.php`, `packages/Webkul/Attribute/src/Traits/CustomAttribute.php`
- Pattern: Models use `CustomAttribute` trait. `AttributeValueRepository::save()` persists attribute values. `LeadForm` FormRequest dynamically builds validation rules from attribute definitions.

**DataGrid:**
- Purpose: Reusable sortable/filterable/exportable table abstraction
- Base: `packages/Webkul/DataGrid/src/DataGrid.php`
- Examples: `packages/Webkul/Admin/src/DataGrids/Lead/LeadDataGrid.php`, `packages/Webkul/Admin/src/DataGrids/Contact/PersonDataGrid.php`
- Pattern: Subclass defines `prepareQueryBuilder()`, `prepareColumns()`, `prepareActions()`. Controller calls `datagrid(XDataGrid::class)->process()`.

**Bouncer (Authorization):**
- Purpose: Role-based access control + data visibility scoping
- Key files: `packages/Webkul/Admin/src/Bouncer.php`, `packages/Webkul/Admin/src/Config/acl.php`
- Pattern: `bouncer()->hasPermission('leads.create')` checks ACL. `bouncer()->getAuthorizedUserIds()` returns user IDs for data scoping (global/group/individual visibility). Used in views, controllers, and repository queries.

**Krayin Vite:**
- Purpose: Multi-package asset compilation with separate Vite builds
- Key files: `packages/Webkul/Core/src/Vite.php`, `config/krayin-vite.php`
- Pattern: Each package namespace (admin, installer, webform) has its own hot file, build directory, and assets directory. Templates use `krayin_vite()->set(...)` to include package-specific compiled assets.

## Entry Points

**Web (HTTP):**
- Location: `public/index.php`
- Triggers: All HTTP requests
- Responsibilities: Bootstrap Laravel, resolve HTTP Kernel, handle request, send response

**CLI (Artisan):**
- Location: `artisan`
- Triggers: `php artisan` commands
- Responsibilities: Run console commands, migrations, seeders, scheduled tasks

**Admin Panel Routes:**
- Location: `packages/Webkul/Admin/src/Routes/Admin/web.php`
- Triggers: All `/admin/*` (configurable via `APP_ADMIN_PATH` env var) requests
- Responsibilities: Aggregate all admin route files (leads, contacts, settings, etc.)
- Middleware: `web`, `admin_locale`, `user`

**Front Routes:**
- Location: `packages/Webkul/Admin/src/Routes/Front/web.php`
- Triggers: `GET /` redirects to admin login
- Middleware: `web`, `admin_locale`

**WebForm Routes:**
- Location: `packages/Webkul/WebForm/src/Routes/routes.php`
- Triggers: Public-facing lead capture form embeds

**Installer Routes:**
- Location: `packages/Webkul/Installer/src/Routes/`
- Triggers: First-time installation wizard

## Error Handling

**Strategy:** Custom exception handler overriding Laravel's default

**Patterns:**
- `packages/Webkul/Admin/src/Exceptions/Handler.php` is bound in AdminServiceProvider, replacing the default handler
- Controllers use try/catch around delete operations, returning JSON error responses with translated messages
- Form validation via FormRequest classes (e.g., `LeadForm`) with automatic validation error responses
- Repository `findOrFail()` throws `ModelNotFoundException` for missing records
- Bouncer uses `abort(401)` for unauthorized actions

## Cross-Cutting Concerns

**Logging:** Standard Laravel logging (`config/logging.php`), no custom structured logging
**Validation:** FormRequest classes in `packages/Webkul/Admin/src/Http/Requests/` with dynamic EAV attribute validation. Custom validation rules in `packages/Webkul/Core/src/Contracts/Validations/` (Code, Decimal).
**Authentication:** Laravel `auth` guard named `user` (not default). Session-based auth via `packages/Webkul/Admin/src/Http/Controllers/User/SessionController.php`. Password reset via `ForgotPasswordController` and `ResetPasswordController`.
**Authorization:** Custom Bouncer system at `packages/Webkul/Admin/src/Bouncer.php` with ACL config at `packages/Webkul/Admin/src/Config/acl.php`. Three visibility levels: global, group, individual.
**Localization:** Multi-language support via Laravel translation files. Each package has `Resources/lang/` with translations (en, ar, es, fa, pt_BR, tr, vi). Middleware `admin_locale` sets active locale.
**Event System:** Laravel Events dispatched throughout controllers and repositories (`lead.create.before`, `lead.create.after`, `activity.update.after`, etc.). Listeners registered in `packages/Webkul/Admin/src/Providers/EventServiceProvider.php`. Automation workflows listen to entity events via `packages/Webkul/Automation/src/Listeners/Entity.php`.
**View Render Events:** Custom hook system `view_render_event('admin.leads.index.header.before')` in Blade templates allows packages to inject content at predefined points.

---

*Architecture analysis: 2026-02-14*
