# Testing Patterns

**Analysis Date:** 2026-02-14

## Test Framework

**Runner:**
- Pest PHP v2.6+ (wrapper around PHPUnit 10)
- Pest Laravel Plugin v2.1+
- Config: `phpunit.xml`

**Assertion Library:**
- Pest's `expect()` API (fluent expectations)
- PHPUnit assertions also available via `$this->assert*()`

**Mocking Library:**
- Mockery v1.4+ (available via `composer.json` dev dependency)

**Run Commands:**
```bash
php artisan test              # Run all tests via Artisan
./vendor/bin/pest             # Run all tests directly via Pest
./vendor/bin/pest --filter=AuthenticationTest  # Run specific test file
./vendor/bin/pest tests/Unit  # Run unit tests only
./vendor/bin/pest tests/Feature  # Run feature tests only
./vendor/bin/pest --coverage  # Run with coverage (requires Xdebug/PCOV)
```

## Test File Organization

**Location:** Separate `tests/` directory at project root (not co-located with source):
```
tests/
├── CreatesApplication.php    # Trait for bootstrapping Laravel app
├── Feature/
│   └── AuthenticationTest.php  # Feature/integration tests
├── Pest.php                  # Pest configuration and helpers
├── TestCase.php              # Base test case class
└── Unit/
    └── BasicTest.php         # Unit tests
```

**Naming:**
- Test files: `{Feature}Test.php` (PascalCase with `Test` suffix)
- PHPUnit requires files to end in `Test.php` (configured in `phpunit.xml` via `suffix="Test.php"`)

**Test Suites (from `phpunit.xml`):**
- `Unit` suite: `./tests/Unit` directory
- `Feature` suite: `./tests/Feature` directory

## Test Configuration

**PHPUnit config (`phpunit.xml`):**
```xml
<php>
    <server name="APP_ENV" value="testing" />
    <server name="BCRYPT_ROUNDS" value="4" />
    <server name="CACHE_DRIVER" value="array" />
    <server name="MAIL_MAILER" value="array" />
    <server name="QUEUE_CONNECTION" value="sync" />
    <server name="SESSION_DRIVER" value="array" />
    <server name="TELESCOPE_ENABLED" value="false" />
</php>
```

**Key test environment settings:**
- `BCRYPT_ROUNDS=4` for faster password hashing in tests
- Array drivers for cache, mail, session (in-memory, no external services)
- Sync queue (no async processing in tests)
- Telescope disabled

## Pest Configuration

**File:** `tests/Pest.php`

**Base TestCase binding:**
```php
uses(\Tests\TestCase::class)->in('Feature');
```
Feature tests automatically use the Laravel TestCase. Unit tests use the default PHPUnit TestCase.

**Custom Expectations:**
```php
expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
```

**Global Helper Functions:**
```php
function getDefaultAdmin()
{
    $admin = \Webkul\User\Models\User::find(1);
    return $admin;
}

function actingAsSanctumAuthenticatedAdmin()
{
    return \Laravel\Sanctum\Sanctum::actingAs(
        getDefaultAdmin(),
        ['*']
    );
}

function getFirstName($fullName)
{
    return explode(' ', $fullName)[0];
}
```

## Test Structure

**Feature Test Pattern (Pest syntax):**
```php
<?php

it('can see the admin login page', function () {
    test()->get(route('admin.session.create'))
        ->assertOK();
});

it('can see the dashboard page after login', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->get(route('admin.dashboard.index'))
        ->assertOK();

    expect(auth()->guard('user')->user()->name)->toBe($admin->name);
});

it('can logout from the admin panel', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->delete(route('admin.session.destroy'), [
            '_token' => csrf_token(),
        ])
        ->assertStatus(302);

    expect(auth()->guard('user')->user())->toBeNull();
});
```

**Unit Test Pattern (Pest syntax):**
```php
<?php

test('check basic unit test', function () {
    $this->assertTrue(true);

    expect(true)->toBeTrue();
});
```

**Patterns:**
- Use `it('description', fn)` for behavior-driven feature tests
- Use `test('description', fn)` for unit tests
- Access the test instance via `test()` or `$this`
- Use `getDefaultAdmin()` helper to get the seeded admin user
- Use `actingAs($admin)` for authenticated requests
- Use named routes: `route('admin.leads.index')` not raw URLs
- Combine PHPUnit assertions (`->assertOK()`) with Pest expectations (`expect()->toBe()`)

## Authentication in Tests

**Web authentication (session-based):**
```php
$admin = getDefaultAdmin();
test()->actingAs($admin)->get(route('admin.dashboard.index'));
```

**API authentication (Sanctum):**
```php
actingAsSanctumAuthenticatedAdmin();
// Then make API requests...
```

**Guard:** The admin guard is `user` (not `web`):
```php
auth()->guard('user')->user()
```

## Base Test Case

**File:** `tests/TestCase.php`
```php
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
```

**File:** `tests/CreatesApplication.php`
```php
trait CreatesApplication
{
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }
}
```

## Mocking

**Framework:** Mockery v1.4+ (Laravel's default)

**Current Usage:** No mock examples found in the existing test files. The existing tests use real database queries against a test database (integration-style).

**Available Patterns (from framework):**
```php
// Mockery style
$mock = \Mockery::mock(LeadRepository::class);
$mock->shouldReceive('create')->once()->andReturn($lead);

// Laravel facade mocking
Event::fake();
Event::assertDispatched('lead.create.after');

// Laravel HTTP mocking
Http::fake([
    'openrouter.ai/*' => Http::response(['choices' => [...]], 200),
]);
```

**What to Mock:**
- External API calls (OpenRouter AI, IMAP)
- Event dispatching when testing controllers in isolation
- Mail sending
- File storage operations

**What NOT to Mock:**
- Database queries in feature tests (use actual database with transactions)
- Laravel's core request/response pipeline
- Middleware (test through the full HTTP stack)

## Fixtures and Factories

**Factory Files:**
- `database/factories/UserFactory.php` - App-level User factory
- `packages/Webkul/Contact/src/Database/Factories/PersonFactory.php` - Person factory

**Factory Pattern:**
```php
class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition()
    {
        return [
            'name'            => $this->faker->name(),
            'emails'          => [$this->faker->unique()->safeEmail()],
            'contact_numbers' => [$this->faker->randomNumber(9)],
        ];
    }
}
```

**Test Data Approach:**
- Existing tests rely on **seeded data** (e.g., `User::find(1)` for admin)
- Tests assume a pre-seeded database with at least one admin user
- Factories exist but are sparingly used

**Location:**
- App-level factories: `database/factories/`
- Package-level factories: `packages/Webkul/{Module}/src/Database/Factories/`

## Database Handling in Tests

**Seeders:** `database/seeders/DatabaseSeeder.php`

**Strategy:** Tests appear to rely on a persistent test database with seeded data rather than using `RefreshDatabase` or `DatabaseTransactions` traits.

**Recommendation for new tests:** Use `Illuminate\Foundation\Testing\RefreshDatabase` trait or `Illuminate\Foundation\Testing\DatabaseTransactions` trait to ensure test isolation.

## Coverage

**Requirements:** None enforced. No coverage thresholds configured.

**View Coverage:**
```bash
./vendor/bin/pest --coverage        # Terminal coverage report
./vendor/bin/pest --coverage --min=80  # Enforce minimum coverage
```

**Source coverage scope (from `phpunit.xml`):**
```xml
<source>
    <include>
        <directory suffix=".php">./app</directory>
    </include>
</source>
```

**Note:** Coverage is only configured for `./app` directory. Package code in `packages/` is NOT included in coverage analysis. To cover package code, update `phpunit.xml` to include `<directory suffix=".php">./packages</directory>`.

## Test Types

**Unit Tests (`tests/Unit/`):**
- Scope: Individual functions, pure logic, no Laravel framework
- Current state: 1 placeholder test (`BasicTest.php`)
- Use `test()` syntax

**Feature Tests (`tests/Feature/`):**
- Scope: HTTP request/response cycles, authentication flows
- Current state: 1 authentication test (`AuthenticationTest.php`)
- Use `it()` syntax with `test()->get/post/put/delete()`
- Test through the full middleware stack

**Integration Tests:**
- Not separately categorized; feature tests serve as integration tests

**E2E Tests:**
- Not present. No browser testing framework (Dusk, Cypress, etc.) is configured.

## Common Patterns

**HTTP Request Testing:**
```php
it('can see the admin login page', function () {
    test()->get(route('admin.session.create'))
        ->assertOK();
});
```

**Authenticated Request Testing:**
```php
it('can see the dashboard page after login', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->get(route('admin.dashboard.index'))
        ->assertOK();
});
```

**POST Request with CSRF Testing:**
```php
it('can logout from the admin panel', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->delete(route('admin.session.destroy'), [
            '_token' => csrf_token(),
        ])
        ->assertStatus(302);
});
```

**Assertion + Expectation Combo:**
```php
// HTTP assertion
test()->actingAs($admin)->get(route('admin.dashboard.index'))->assertOK();

// Pest expectation
expect(auth()->guard('user')->user()->name)->toBe($admin->name);
```

## Writing New Tests

**For a new feature test:**

1. Create file in `tests/Feature/` with `Test.php` suffix
2. Use `it()` syntax for behavior descriptions
3. Use `getDefaultAdmin()` for authenticated requests
4. Use named routes (`route('admin.resource.action')`)
5. Assert HTTP status codes and response content
6. Use `expect()` for state verification

**Template for new feature test:**
```php
<?php

it('can list leads', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->get(route('admin.leads.index'))
        ->assertOK();
});

it('can create a lead', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->post(route('admin.leads.store'), [
            'title'      => 'Test Lead',
            'lead_value' => 1000,
            // ... required fields
        ])
        ->assertRedirect();
});

it('can delete a lead', function () {
    $admin = getDefaultAdmin();

    // Create or fetch a lead first
    test()->actingAs($admin)
        ->delete(route('admin.leads.delete', $leadId))
        ->assertOK()
        ->assertJson(['message' => trans('admin::app.leads.destroy-success')]);
});
```

**For a new unit test:**

1. Create file in `tests/Unit/` with `Test.php` suffix
2. Use `test()` syntax
3. No Laravel framework needed (pure logic only)

**Template for new unit test:**
```php
<?php

test('it calculates lead rotten days correctly', function () {
    // Test pure logic
    expect($result)->toBe($expected);
});
```

## Test Gaps

**Current coverage is minimal:**
- Only 2 test files exist (1 feature, 1 unit placeholder)
- No tests for CRUD operations on leads, contacts, quotes, products
- No tests for DataGrid processing
- No tests for API endpoints (Sanctum-authenticated)
- No tests for event listener behavior
- No tests for repository business logic
- No tests for authorization/permission checks
- No tests for the MagicAI service

---

*Testing analysis: 2026-02-14*
