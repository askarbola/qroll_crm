---
phase: 01-telegram-settings
plan: 01
subsystem: admin-configuration
tags: [telegram-bot-api, guzzle, laravel-config, admin-settings]

# Dependency graph
requires:
  - phase: codebase-mapping
    provides: ARCHITECTURE and CONVENTIONS documentation
provides:
  - Telegram configuration section in admin settings (bot_token, chat_id fields)
  - Test connection endpoint validating Telegram bot credentials
  - Translation keys for Telegram settings UI
affects: [02-stage-notifications, telegram-integration]

# Tech tracking
tech-stack:
  added: []
  patterns: [core_config.php for admin settings, Guzzle HTTP client for API calls]

key-files:
  created: []
  modified:
    - packages/Webkul/Admin/src/Config/core_config.php
    - packages/Webkul/Admin/src/Resources/lang/en/app.php
    - packages/Webkul/Admin/src/Http/Controllers/Configuration/ConfigurationController.php
    - packages/Webkul/Admin/src/Routes/Admin/configuration-routes.php

key-decisions:
  - "Used required_with validation to ensure both bot_token and chat_id are present or both empty"
  - "Implemented two-step validation: getMe for token, sendMessage for chat accessibility"
  - "Cast chat_id to integer for Telegram API compatibility with negative group IDs"

patterns-established:
  - "HTTP client pattern: timeout=10s, connect_timeout=5s, http_errors=false"
  - "Test endpoint pattern: validate token first, then test actual operation"

# Metrics
duration: 64 seconds
completed: 2026-02-15
---

# Phase 01 Plan 01: Telegram Configuration Structure

**Telegram bot settings in admin UI with live connection testing via Telegram API (getMe + sendMessage)**

## Performance

- **Duration:** 1 min 4 sec
- **Started:** 2026-02-15T19:31:41Z
- **Completed:** 2026-02-15T19:32:45Z
- **Tasks:** 2
- **Files modified:** 4

## Accomplishments
- Admin settings now include Telegram configuration section with bot token (password-masked) and group chat ID fields
- Test connection endpoint validates bot token via Telegram getMe API and sends test message to verify chat accessibility
- Complete validation with descriptive error messages for common failures (invalid token, chat not found, connection timeout)

## Task Commits

Each task was committed atomically:

1. **Task 1: Add Telegram configuration structure** - `b8fd3dc5` (feat)
2. **Task 2: Implement test connection endpoint** - `b7abec6e` (feat)

## Files Created/Modified

- `packages/Webkul/Admin/src/Config/core_config.php` - Added Telegram section (sort 4) with settings.connection group containing bot_token (password) and chat_id (text with integer validation) fields
- `packages/Webkul/Admin/src/Resources/lang/en/app.php` - Added translation keys for Telegram settings (title, info, field labels, help text)
- `packages/Webkul/Admin/src/Http/Controllers/Configuration/ConfigurationController.php` - Added testTelegramConnection method with two-step validation (getMe, sendMessage)
- `packages/Webkul/Admin/src/Routes/Admin/configuration-routes.php` - Added POST route admin/settings/configuration/telegram/test

## Decisions Made

- **Validation strategy:** Used required_with validation rule to ensure both bot_token and chat_id are provided together or both left empty, preventing partial configuration
- **Two-step test:** First validate bot token with getMe API, then test message sending to catch both token and chat ID issues separately with clear error messages
- **Integer casting:** Explicitly cast chat_id to integer before sending to Telegram API to handle negative group IDs correctly (e.g., -100123456789)
- **Error handling:** Set http_errors=false on Guzzle client and manually check response codes to provide user-friendly error messages instead of throwing exceptions

## Deviations from Plan

None - plan executed exactly as written. All files modified as specified, validation implemented per plan requirements, test endpoint follows WebhookService pattern from research.

## Issues Encountered

None - implementation was straightforward. Existing core_config.php pattern was clear, Guzzle HTTP client was already available, and Telegram Bot API documentation provided clear endpoint specifications.

## User Setup Required

None - no external service configuration required. Admin users will configure Telegram bot token and chat ID through the admin UI once bot is created via @BotFather on Telegram.

## Next Phase Readiness

- Telegram configuration structure complete and ready for use by notification system
- SystemConfig facade can read telegram.settings.connection.bot_token and telegram.settings.connection.chat_id values
- Ready for Phase 01 Plan 02: Implement lead stage change listener and Telegram notification service

## Self-Check

Verifying all claimed files and commits exist:

**Files:**
- FOUND: packages/Webkul/Admin/src/Config/core_config.php
- FOUND: packages/Webkul/Admin/src/Resources/lang/en/app.php
- FOUND: packages/Webkul/Admin/src/Http/Controllers/Configuration/ConfigurationController.php
- FOUND: packages/Webkul/Admin/src/Routes/Admin/configuration-routes.php

**Commits:**
- b8fd3dc5: feat(01-01): add Telegram configuration structure to admin settings
- b7abec6e: feat(01-01): implement Telegram connection test endpoint

## Self-Check: PASSED

---
*Phase: 01-telegram-settings*
*Completed: 2026-02-15*
