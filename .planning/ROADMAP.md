# Roadmap: Qroll CRM — Telegram Integration

**Milestone:** v1.0 — Telegram Stage Notifications
**Created:** 2026-02-14
**Phases:** 3

## Phase Overview

| # | Phase | Goal | Requirements | Criteria |
|---|-------|------|--------------|----------|
| 1 | Telegram Settings | Admin can configure and test Telegram bot connection | TGRAM-01, TGRAM-02, TGRAM-03 | 3 |
| 2 | Stage Notifications | Lead stage changes send formatted Telegram messages | TGRAM-04, TGRAM-05, TGRAM-06 | 3 |
| 3 | Add Additional Details While Creating Contacts | Unified client entity with Contact Type toggle and conditional fields | CONTACT-01, CONTACT-02, CONTACT-03, CONTACT-04 | 4 |

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

## Phase 3: Add Additional Details While Creating Contacts

**Goal:** Transform the Person entity into a unified client entity for tax/accounting practice. Add a "Contact Type" dropdown (Individual/Business) with type-specific fields: SSN, DOB, Filing Status, Occupation for individuals; Business Name, EIN, Entity Type, Fiscal Year End for businesses. Conditionally show fields based on Contact Type selection.

**Requirements:**
- CONTACT-01: Contact Type dropdown (Individual / Business) on Person entity
- CONTACT-02: Individual fields — SSN, Date of Birth, Filing Status, Occupation
- CONTACT-03: Business fields — Business Name, EIN, Entity Type (LLC, S-Corp, C-Corp), Fiscal Year End
- CONTACT-04: Conditional field visibility — show relevant fields based on Contact Type selection

**Success Criteria:**
1. User creates a contact, selects "Individual" as Contact Type, and sees SSN, Date of Birth, Filing Status, Occupation fields
2. User creates a contact, selects "Business" as Contact Type, and sees Business Name, EIN, Entity Type, Fiscal Year End fields
3. Fields for the non-selected type are hidden (not just empty)
4. All field values persist correctly and display on the contact view page

**Dependencies:** None.

**Plans:** 2 plans

Plans:
- [ ] 03-01-PLAN.md — Database migration for 9 contact attributes + data-attribute-code identifiers on form components
- [ ] 03-02-PLAN.md — Conditional field visibility (create, edit, view pages)

---

**Coverage:** 10/10 requirements mapped (100%)

---
*Roadmap created: 2026-02-14*
