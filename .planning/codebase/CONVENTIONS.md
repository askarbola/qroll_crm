# Coding Conventions

**Analysis Date:** 2026-02-14

## Project Architecture Style

This is a **Krayin CRM** project (based on Laravel 10) using a **modular monolith** architecture. All domain logic lives in `packages/Webkul/*/src/` as self-contained modules. The `app/` directory is minimal (standard Laravel scaffolding only).

## Naming Patterns

**Files:**
- PHP classes: PascalCase matching the class name (e.g., `LeadController.php`, `LeadRepository.php`)
- Migrations: `YYYY_MM_DD_HHMMSS_snake_case_description.php` (anonymous classes via `return new class extends Migration`)
- Blade views: kebab-case or dot-notation (e.g., `leads/index.blade.php`, `contacts.persons.index`)
- Route files: kebab-case with `-routes.php` suffix (e.g., `leads-routes.php`, `contacts-routes.php`)
- Config files: snake_case (e.g., `core_config.php`, `attribute_lookups.php`)

**Classes:**
- Models: singular PascalCase (`Lead`, `Person`, `Pipeline`)
- Model Proxies: `{Model}Proxy` (e.g., `LeadProxy`, `PersonProxy`) -- uses Konekt Concord proxy pattern
- Contracts (interfaces): singular PascalCase matching model name in `Contracts/` directory (e.g., `Webkul\Lead\Contracts\Lead`)
- Repositories: `{Model}Repository` (e.g., `LeadRepository`, `PersonRepository`)
- Controllers: `{Model}Controller` (e.g., `LeadController`, `PersonController`)
- DataGrids: `{Model}DataGrid` (e.g., `LeadDataGrid`, `PersonDataGrid`)
- Form Requests: `{Model}Form` (e.g., `LeadForm`, `AttributeForm`, `PipelineForm`)
- API Resources: `{Model}Resource` (e.g., `LeadResource`, `PersonResource`)
- Service Providers: `{Module}ServiceProvider` (e.g., `LeadServiceProvider`, `AdminServiceProvider`)
- Module Service Providers: `ModuleServiceProvider` (registers Concord models)
- Listeners: singular entity name (e.g., `Lead`, `Person`, `Activity`)

**Functions/Methods:**
- camelCase for all methods (`getLeadsQuery`, `findOrFail`, `massDestroy`)
- Accessor pattern: `get{Attribute}Attribute` for Eloquent accessors (`getRottenDaysAttribute`)
- Global helpers: snake_case functions wrapped in `if (! function_exists(...))` guard (e.g., `bouncer()`)

**Variables:**
- camelCase for local variables (`$leadData`, `$pipelineId`, `$errorMessages`)
- snake_case for database columns and array keys (`lead_value`, `person_id`, `lead_pipeline_stage_id`)
- Protected properties with `$` prefix: camelCase (`$fieldSearchable`, `$primaryColumn`)

**Namespaces:**
- Package namespace: `Webkul\{ModuleName}\{SubDir}` (e.g., `Webkul\Lead\Repositories`, `Webkul\Admin\Http\Controllers\Lead`)
- PSR-4 autoloading registered in `composer.json`

**Database Tables:**
- Plural snake_case: `leads`, `persons`, `lead_pipelines`, `lead_pipeline_stages`
- Pivot tables: `{entity1}_{entity2}` (e.g., `lead_tags`, `lead_activities`, `lead_quotes`)
- Foreign keys: `{related_table_singular}_id` (e.g., `lead_pipeline_id`, `person_id`)

**Route Names:**
- Dot-notation: `admin.{resource}.{action}` (e.g., `admin.leads.index`, `admin.leads.store`, `admin.contacts.persons.view`)
- Mass operations: `admin.{resource}.mass_update`, `admin.{resource}.mass_delete`

## Code Style

**Formatting:**
- Tool: Laravel Pint v1.16+ (config in `pint.json`)
- Preset: `laravel` (PSR-12 based)
- Custom rules: Binary operator `=>` alignment enabled

**Key Formatting Rules (from Pint `laravel` preset):**
- 4 spaces indentation (no tabs)
- Opening braces on same line for classes and methods
- Single blank line between methods
- Trailing commas in multi-line arrays
- No closing PHP tag in pure PHP files
- Array alignment using `=>` operator (custom rule)

**Array Alignment Convention:**
Use aligned `=>` operators in associative arrays. This is enforced by Pint:
```php
protected $fillable = [
    'title',
    'description',
    'lead_value',
    'status',
    'lost_reason',
    'expected_close_date',
    'closed_at',
    'user_id',
    'person_id',
    'lead_source_id',
    'lead_type_id',
    'lead_pipeline_id',
    'lead_pipeline_stage_id',
];

protected $casts = [
    'closed_at'           => 'datetime:D M d, Y H:i A',
    'expected_close_date' => 'date:D M d, Y',
];
```

## Dependency Injection

**Pattern:** Use PHP 8 constructor property promotion everywhere.

**Controllers:**
```php
public function __construct(
    protected UserRepository $userRepository,
    protected LeadRepository $leadRepository,
    protected PersonRepository $personRepository
) {
    request()->request->add(['entity_type' => 'leads']);
}
```

**Repositories:**
```php
public function __construct(
    protected StageRepository $stageRepository,
    protected PersonRepository $personRepository,
    Container $container
) {
    parent::__construct($container);
}
```

**Listeners:**
```php
public function __construct(protected EmailRepository $emailRepository) {}
```

## Import Organization

**Order:**
1. PHP built-in classes (e.g., `Exception`)
2. Carbon/external libraries (e.g., `Carbon\Carbon`)
3. Illuminate/Laravel framework classes (grouped by namespace)
4. Webkul package classes (grouped by package namespace)

**Example from `packages/Webkul/Admin/src/Http/Controllers/Lead/LeadController.php`:**
```php
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Prettus\Repository\Criteria\RequestCriteria;
use Webkul\Admin\DataGrids\Lead\LeadDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\LeadForm;
use Webkul\Admin\Http\Resources\LeadResource;
use Webkul\Lead\Repositories\LeadRepository;
```

**Path Aliases:** None. All imports use fully qualified namespace paths.

## Controller Patterns

**Standard CRUD Controller Structure:**
- `index()` - List resources (handles both AJAX/DataGrid and Blade view)
- `create()` - Show creation form
- `store(FormRequest $request)` - Store resource
- `edit(int $id)` - Show edit form
- `view(int $id)` or `show(int $id)` - Show resource detail
- `update(FormRequest $request, int $id)` - Update resource
- `destroy(int $id)` - Delete resource (returns JSON)
- `search()` - Search endpoint returning API resources
- `massUpdate(MassUpdateRequest $request)` - Bulk update
- `massDestroy(MassDestroyRequest $request)` - Bulk delete

**Controller Return Types:**
- Use union return types: `RedirectResponse|JsonResponse`
- Always type-hint return types on methods

**Index Method Pattern (DataGrid + View):**
```php
public function index()
{
    if (request()->ajax()) {
        return datagrid(LeadDataGrid::class)->process();
    }

    return view('admin::leads.index');
}
```

**Store/Update Pattern (Event-Driven):**
```php
public function store(LeadForm $request): RedirectResponse|JsonResponse
{
    Event::dispatch('lead.create.before');

    $lead = $this->leadRepository->create($data);

    Event::dispatch('lead.create.after', $lead);

    if (request()->ajax()) {
        return response()->json([
            'message' => trans('admin::app.leads.create-success'),
            'data'    => new LeadResource($lead),
        ]);
    }

    session()->flash('success', trans('admin::app.leads.create-success'));

    return redirect()->route('admin.leads.index');
}
```

**Destroy Pattern (Try-Catch):**
```php
public function destroy(int $id): JsonResponse
{
    $this->leadRepository->findOrFail($id);

    try {
        Event::dispatch('lead.delete.before', $id);

        $this->leadRepository->delete($id);

        Event::dispatch('lead.delete.after', $id);

        return response()->json([
            'message' => trans('admin::app.leads.destroy-success'),
        ]);
    } catch (\Exception $exception) {
        return response()->json([
            'message' => trans('admin::app.leads.destroy-failed'),
        ], 400);
    }
}
```

## Event System

**Convention:** Dispatch events before and after operations using dot-notation names.

**Naming Pattern:** `{entity}.{action}.{timing}`
- `lead.create.before` / `lead.create.after`
- `lead.update.before` / `lead.update.after`
- `lead.delete.before` / `lead.delete.after`
- `contacts.person.create.before` / `contacts.person.create.after`

**Event Listeners:** Registered in `packages/Webkul/Admin/src/Providers/EventServiceProvider.php` using string-based class@method syntax:
```php
protected $listen = [
    'lead.create.after' => [
        'Webkul\Admin\Listeners\Lead@linkToEmail',
    ],
];
```

**View Render Events (Blade):**
```php
{!! view_render_event('admin.leads.index.header.before') !!}
```

## Error Handling

**Strategy:** Exception handler in `packages/Webkul/Admin/src/Exceptions/Handler.php`.

**Patterns:**
- Controllers wrap destructive operations (delete) in try-catch, returning JSON with translated messages
- Repository `findOrFail()` throws `ModelNotFoundException` (handled by exception handler)
- Validation uses Laravel Form Request classes (`LeadForm`, `AttributeForm`)
- Error responses always use translated strings: `trans('admin::app.leads.destroy-failed')`
- JSON error responses: `response()->json(['message' => '...'], 400)`
- Custom exception handler maps status codes to translated messages (404, 403, 401, 500)

**HTTP Error Codes Used:**
- `200` - Success
- `302` - Redirect
- `400` - Client error (delete failed, update failed)
- `401` - Unauthenticated
- `403` - Forbidden
- `404` - Not found
- `500` - Server error

## Internationalization (i18n)

**All user-facing strings MUST use translation keys.**

**Pattern:** `trans('admin::app.{module}.{action-message}')`
```php
trans('admin::app.leads.create-success')
trans('admin::app.leads.destroy-failed')
trans('admin::app.contacts.persons.index.delete-success')
```

**Blade Pattern:** `@lang('admin::app.leads.index.title')`

**Translation files location:** `packages/Webkul/Admin/src/Resources/lang/{locale}/app.php`

**Supported locales:** en, ar, es, fa, pt_BR, tr, vi

## Repository Pattern

**Base class:** `Webkul\Core\Eloquent\Repository` (extends `Prettus\Repository\Eloquent\BaseRepository`)

**Required methods to implement:**
```php
public function model()
{
    return Lead::class; // Return the Contract interface, not the Model
}
```

**Key conventions:**
- Return the Contract interface from `model()` method
- Use `$fieldSearchable` for search configuration
- Override `create()` and `update()` for complex logic
- Call `parent::create()` / `parent::update()` for base operations
- Use `scopeQuery()` for complex query building
- Use `pushCriteria(app(RequestCriteria::class))` for search filtering

## Model Pattern (Concord)

**Every model requires three files:**
1. **Contract/Interface:** `packages/Webkul/{Module}/src/Contracts/{Model}.php` - Empty interface
2. **Model:** `packages/Webkul/{Module}/src/Models/{Model}.php` - Implements contract
3. **Proxy:** `packages/Webkul/{Module}/src/Models/{Model}Proxy.php` - Extends `ModelProxy`

**Model relationships use Proxy classes:**
```php
public function person(): BelongsTo
{
    return $this->belongsTo(PersonProxy::modelClass());
}
```

**Registration in ModuleServiceProvider:**
```php
class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\Lead\Models\Lead::class,
        \Webkul\Lead\Models\Pipeline::class,
    ];
}
```

## DataGrid Pattern

**Extend `Webkul\DataGrid\DataGrid` and implement:**
1. `prepareQueryBuilder(): Builder` - Build the SQL query
2. `prepareColumns(): void` - Define columns with `$this->addColumn([...])`
3. `prepareActions(): void` - Add row actions with `$this->addAction([...])`
4. `prepareMassActions(): void` - Add bulk operations with `$this->addMassAction([...])`

**Column definition pattern:**
```php
$this->addColumn([
    'index'              => 'sales_person',
    'label'              => trans('admin::app.leads.index.datagrid.sales-person'),
    'type'               => 'string',
    'searchable'         => false,
    'sortable'           => true,
    'filterable'         => true,
    'filterable_type'    => 'searchable_dropdown',
    'filterable_options' => [
        'repository' => UserRepository::class,
        'column'     => [
            'label' => 'name',
            'value' => 'name',
        ],
    ],
]);
```

## Blade/View Conventions

**Component prefix:** `<x-admin::...>` (registered via `Blade::anonymousComponentPath`)

**Layout:** Wrap pages in `<x-admin::layouts>`

**Slot naming:** `<x-slot:title>` for page title

**View rendering:** Use `admin::` namespace prefix (e.g., `view('admin::leads.index')`)

**Blade directives:**
- `@lang()` for translations
- `@if (bouncer()->hasPermission('...'))` for permission checks
- `{!! view_render_event('...') !!}` for extensibility hooks

## Authorization

**Bouncer system:** Custom ACL via `bouncer()` global helper.

**Permission check in controllers:**
```php
if ($userIds = bouncer()->getAuthorizedUserIds()) {
    $query->whereIn('leads.user_id', $userIds);
}
```

**Permission check in views:**
```php
@if (bouncer()->hasPermission('leads.create'))
```

## Logging

**Framework:** Standard Laravel logging (no custom logger).

**Pattern:** Minimal logging in application code. Errors are handled by the exception handler rather than explicitly logged in most controllers.

## Comments

**When to Comment:**
- PHPDoc blocks on all class properties with `@var` type
- PHPDoc blocks on all public methods with `@param` and `@return`
- Inline comments for non-obvious business logic
- Section comments using block format in migrations and config files

**PHPDoc Style:**
```php
/**
 * Get the user that owns the lead.
 */
public function user(): BelongsTo

/**
 * Create a new repository instance.
 *
 * @return void
 */
public function __construct(...)

/**
 * Searchable fields.
 */
protected $fieldSearchable = [...]
```

## Function Design

**Size:** Controller methods are moderate (20-60 lines typical). Complex logic belongs in Repositories or Services.

**Parameters:** Use type hints on all parameters. Use `int $id` for resource identifiers.

**Return Values:** Always declare return types. Use union types where methods can return multiple types (`RedirectResponse|JsonResponse`).

## Module Design

**Exports:** Each package has a ServiceProvider that registers routes, views, translations, and migrations.

**Barrel Files:** Not used. Each class is imported individually.

**Package Registration:** Packages are auto-discovered via Concord's `ModuleServiceProvider` pattern and registered in `config/concord.php`.

---

*Convention analysis: 2026-02-14*
