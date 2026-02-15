# Roadmap: Qroll CRM — Telegram Integration

**Milestone:** v1.0 — Telegram Stage Notifications
**Created:** 2026-02-14
**Phases:** 2

## Phase Overview

| # | Phase | Goal | Requirements | Criteria |
|---|-------|------|--------------|----------|
| 1 | Telegram Settings | Admin can configure and test Telegram bot connection | TGRAM-01, TGRAM-02, TGRAM-03 | 3 |
| 2 | Stage Notifications | Lead stage changes send formatted Telegram messages | TGRAM-04, TGRAM-05, TGRAM-06 | 3 |

---

## Phase 1: Telegram Settings

**Goal:** Admin can configure Telegram bot token and group chat ID in CRM settings, and verify the connection works by sending a test message.

**Requirements:** TGRAM-01, TGRAM-02, TGRAM-03

**Success Criteria:**
1. Admin navigates to Settings and sees Telegram configuration section with bot token and chat ID fields
2. Admin enters bot token and chat ID, saves, and values persist across page reloads
3. Admin clicks "Test Connection" and receives a test message in the configured Telegram group

**Dependencies:** None — first phase.

**Plans:** 2 plans

Plans:
- [ ] 01-01-PLAN.md — Configuration structure and test endpoint (backend)
- [ ] 01-02-PLAN.md — Test button UI and end-to-end verification

---

## Phase 2: Stage Change Notifications

**Goal:** When any lead moves to a different pipeline stage, the system automatically sends a formatted message to the configured Telegram group with lead details and a link back to the CRM.

**Requirements:** TGRAM-04, TGRAM-05, TGRAM-06

**Success Criteria:**
1. Moving a lead to a different stage in the kanban view triggers a Telegram message in the configured group
2. The message displays: sales rep name, lead name, new stage name, pipeline name, and a clickable link to the lead
3. If Telegram is not configured (no token/chat ID), stage changes work normally without errors

**Dependencies:** Phase 1 (needs Telegram config to know where to send).

---

**Coverage:** 6/6 requirements mapped (100%)

---
*Roadmap created: 2026-02-14*
