# Phase 02 Research: Stage Change Notifications

## 1. Event System

The `lead.update.after` event is dispatched with full Lead object as payload.

**Dispatch points:**
- `LeadController::update()` — standard lead updates
- `LeadController::updateAttributes()` — attribute-only updates
- `LeadController::updateStage()` — kanban drag-and-drop stage changes (line 303-334)
- `LeadController::massUpdate()` — bulk stage updates

## 2. Lead Model & Relationships

**File:** `packages/Webkul/Lead/src/Models/Lead.php`

Key fields: `id`, `title`, `user_id`, `lead_pipeline_id`, `lead_pipeline_stage_id`

Relationships needed for message:
- `$lead->user` → BelongsTo(User) — sales rep
- `$lead->stage` → BelongsTo(Stage) — new stage (has `name`)
- `$lead->pipeline` → BelongsTo(Pipeline) — pipeline (has `name`)

## 3. Listener Registration Pattern

**File:** `packages/Webkul/Admin/src/Providers/EventServiceProvider.php`

```php
protected $listen = [
    'lead.update.after' => [
        'Webkul\Admin\Listeners\Lead@linkToEmail',  // Already listening
    ],
];
```

Add: `'Webkul\Admin\Listeners\Telegram@notifyStageChange'`

**Listener receives:** `$lead` — full Lead model instance

## 4. Stage Change Detection

Use Eloquent's dirty tracking:
```php
if (!$lead->wasChanged('lead_pipeline_stage_id')) {
    return; // Not a stage change
}
```

## 5. URL Generation

**Route:** `admin.leads.view` — `packages/Webkul/Admin/src/Routes/Admin/leads-routes.php` line 19
```php
$url = route('admin.leads.view', ['id' => $lead->id]);
```

## 6. Config Access

```php
$botToken = system_config()->getConfigData('telegram.settings.connection.bot_token');
$chatId = system_config()->getConfigData('telegram.settings.connection.chat_id');
```

## 7. Telegram Message Format

parse_mode=HTML supports: `<b>`, `<i>`, `<a href="">`, `<code>`, `<pre>`

Recommended format:
```html
<b>Lead Stage Updated</b>
Rep: <b>John Doe</b>
Lead: <b>Acme Corp</b>
Pipeline: <b>Sales</b>
New Stage: <b>Negotiation</b>
<a href="https://domain.com/admin/leads/view/123">View in CRM</a>
```

## 8. Files to Create/Modify

1. **NEW:** `packages/Webkul/Admin/src/Listeners/Telegram.php` — listener class
2. **MODIFY:** `packages/Webkul/Admin/src/Providers/EventServiceProvider.php` — register listener

## Key Constraints

- Must NOT throw exceptions during stage change (silent fail if Telegram unavailable)
- Chat ID must be cast to integer (negative group IDs)
- Guzzle: timeout=10s, connect_timeout=5s, http_errors=false
- 4096 char limit on Telegram messages
- If no config, skip silently

---
*Research completed: 2026-02-15*
