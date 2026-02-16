---
phase: 02-stage-notifications
plan: 01
subsystem: telegram-notifications
tags: [telegram-bot-api, guzzle, laravel-events, lead-pipeline]

# Dependency graph
requires:
  - phase: 01-telegram-settings
    provides: Telegram configuration (bot_token, chat_id)
provides:
  - Telegram notification on lead stage change
  - Event listener registered on lead.update.after
affects: []

# Tech tracking
tech-stack:
  added: []
  patterns: [EventServiceProvider listener registration, Guzzle HTTP client for API calls]

key-files:
  created:
    - packages/Webkul/Admin/src/Listeners/Telegram.php
  modified:
    - packages/Webkul/Admin/src/Providers/EventServiceProvider.php

key-decisions:
  - "Use wasChanged() to detect stage changes vs other lead updates"
  - "Catch Throwable (not Exception) to never break stage changes"
  - "Use loadMissing() for efficient relationship loading"
  - "escapeHtml() on all dynamic values for Telegram HTML safety"

patterns-established:
  - "Telegram notification pattern: check config, check change, build message, send, log errors"

# Metrics
duration: ~2m (19:27 to 19:29)
completed: 2026-02-15
---

# Phase 02 Plan 01: Stage Change Notifications

**Telegram listener sends formatted message when lead changes pipeline stage**

## Accomplishments
- Event listener hooks into lead.update.after and detects stage changes via wasChanged()
- Formatted HTML message sent to Telegram with lead name, stage, rep, pipeline, and CRM link
- Silent failure when Telegram not configured or API errors occur
- HTML escaping prevents injection via lead titles or user names

## Task Commits
1. Task 1: Create Telegram listener and register event - 9d1c53ff (feat)

## Files Created/Modified
- packages/Webkul/Admin/src/Listeners/Telegram.php - New listener with notifyStageChange method
- packages/Webkul/Admin/src/Providers/EventServiceProvider.php - Added lead.update.after registration

## Decisions Made
- wasChanged('lead_pipeline_stage_id') as first check to filter non-stage updates
- Catch \Throwable to ensure stage changes never fail due to Telegram
- loadMissing() to avoid redundant queries if relationships already loaded
- escapeHtml() on all dynamic content for Telegram HTML safety

## Deviations from Plan
None

## Issues Encountered
None

## Self-Check
- [x] Telegram.php exists at packages/Webkul/Admin/src/Listeners/Telegram.php
- [x] EventServiceProvider.php has lead.update.after with Telegram@notifyStageChange
- [x] Commit 9d1c53ff contains both file changes
