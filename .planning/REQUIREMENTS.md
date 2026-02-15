# Requirements: Qroll CRM — Telegram Integration

**Defined:** 2026-02-14
**Core Value:** Sales team sees every lead stage change in Telegram instantly

## v1 Requirements

### Telegram Configuration

- [ ] **TGRAM-01**: Admin can configure Telegram bot token from CRM settings page
- [ ] **TGRAM-02**: Admin can configure Telegram group chat ID from CRM settings page
- [ ] **TGRAM-03**: Admin can test the Telegram connection from settings (sends test message to group)

### Stage Change Notifications

- [ ] **TGRAM-04**: System sends Telegram message when any lead changes pipeline stage
- [ ] **TGRAM-05**: Message includes lead name, new stage name, sales rep who moved it, and pipeline name
- [ ] **TGRAM-06**: Message includes clickable link to the lead detail page in CRM

## v2 Requirements

### Extended Notifications

- **TGRAM-07**: Admin can select which pipelines/stages trigger notifications
- **TGRAM-08**: Admin can configure multiple Telegram groups (per-pipeline)
- **TGRAM-09**: System sends notification when lead is created
- **TGRAM-10**: System sends notification when lead is won or lost

## Out of Scope

| Feature | Reason |
|---------|--------|
| Two-way Telegram interaction | One-way notifications only |
| Other platforms (Slack, WhatsApp) | Telegram only for this milestone |
| Custom message templates | Fixed format sufficient for v1 |
| Per-user notification preferences | Team group notification, not individual |

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| TGRAM-01 | Phase 1 | Pending |
| TGRAM-02 | Phase 1 | Pending |
| TGRAM-03 | Phase 1 | Pending |
| TGRAM-04 | Phase 2 | Pending |
| TGRAM-05 | Phase 2 | Pending |
| TGRAM-06 | Phase 2 | Pending |

**Coverage:**
- v1 requirements: 6 total
- Mapped to phases: 6
- Unmapped: 0

---
*Requirements defined: 2026-02-14*
*Last updated: 2026-02-14 after initial definition*
