# Qroll CRM — Telegram Integration

## What This Is

Krayin CRM (Laravel 10 + Vue.js 3) extended with Telegram bot integration. When leads move through pipeline stages, the system sends formatted notifications to a Telegram group so the team stays informed in real-time.

## Core Value

Sales team sees every lead stage change in Telegram instantly — no one misses a pipeline move.

## Requirements

### Validated

<!-- Shipped and confirmed valuable. -->

(None yet — ship to validate)

### Active

<!-- Current scope. Building toward these. -->

- [ ] Admin can configure Telegram bot token from CRM settings page
- [ ] Admin can configure Telegram group chat ID from CRM settings page
- [ ] Admin can test the Telegram connection from settings
- [ ] System sends Telegram message when any lead changes pipeline stage
- [ ] Message includes lead name, new stage, sales rep, pipeline name
- [ ] Message includes clickable link to lead detail page

### Out of Scope

<!-- Explicit boundaries. Includes reasoning to prevent re-adding. -->

- Per-pipeline Telegram groups — single group decided, can add later
- Selective stage triggers — all stages trigger, no filtering
- Other notification types (lead created, won/lost specific) — stage moves only
- Two-way Telegram interaction — one-way notifications only
- Other messaging platforms (Slack, WhatsApp) — Telegram only

## Context

- Existing codebase: Krayin CRM with 18 Webkul packages, modular architecture via Konekt Concord
- Lead stage changes dispatch `lead.update.after` events — hook point exists
- Automation package has workflow/webhook infrastructure that can be referenced
- Admin settings use `core_config.php` pattern with `SystemConfig` facade
- Frontend is Blade + Vue.js 3 hybrid with Tailwind CSS

## Constraints

- **Tech stack**: Laravel 10, PHP 8.2+, Vue.js 3 — must follow existing Webkul package patterns
- **API**: Telegram Bot API (HTTP-based, no SDK needed — Guzzle already available)
- **Architecture**: Follow existing package conventions (Repository pattern, Concord modules, event-driven)

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Single Telegram group | Simpler config, team sees everything | — Pending |
| All stages trigger | No need for selective — user wants full visibility | — Pending |
| Admin settings page for config | User preference over .env | — Pending |
| Hook into existing lead.update.after event | Event already dispatched on stage changes | — Pending |

---
*Last updated: 2026-02-14 after milestone v1.0 initialization*
