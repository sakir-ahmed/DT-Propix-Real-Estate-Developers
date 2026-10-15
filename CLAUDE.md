<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

# Project Instructions

This is a Laravel 13 project (DT Propix, a multi-tenant SaaS for real-estate developers, agencies and property companies: public website, CRM, unit inventory, booking with payment schedules, collections, accounts, HR and payroll, customer portal) built from the specification in `docs/`.

Read these files before writing any code, in this order:

1. `docs/prd.md`: what to build (roles, modules, funnel definitions and business rules, plans, scope)
2. `docs/architecture.md`: tech stack, structure, coding rules, multi-tenancy, website and SEO, inventory and booking integrity, accounting integrity, workflows, Definition of Done
3. `docs/api.md`: API conventions, endpoints, public website endpoints, portal endpoints
4. `docs/design.md`: design tokens, components, icons, sizing, tables, booking desk, availability grid, CRM, public site and portal
5. `docs/roadmap.md`: build phases and acceptance criteria
6. `docs/ui-preview.html`: visual reference of the intended look (open in a browser; a static mockup, not production code)

## Rules

- Build **one phase at a time** from `docs/roadmap.md`. Never start the next phase until asked.
- If `prd.md` and `architecture.md` conflict, the PRD wins on business rules and roles; `architecture.md` wins on code structure.
- If Laravel Boost guidelines conflict with `docs/`, the `docs/` files win for product rules, structure and design. Boost guidelines apply to Laravel and package usage details.
- **Tenant isolation is mandatory**, including the public website and portals. Every tenant-owned model uses `BelongsToTenant`; never accept a tenant ID from the client; write a cross-tenant test for every model and endpoint.
- **Units and money must reconcile.** Unit status changes only through `ChangeUnitStatusAction` under a row lock with a database constraint against double booking; prices come from `PriceUnitAction` and are stored as a snapshot on the booking; payment schedules lock after the agreement and change only by approved revisions; collections are allocated as stored rows; all journals go through `PostJournalEntryAction` and balance; posted receipts, vouchers and journals are never edited (use reversals). Add reconciliation tests.
- **One approval engine** for discounts, cancellations, refunds, transfers, price changes, expenses, leave, payroll and commission payouts. Do not write special approval code per feature.
- **Customer privacy and business secrets.** Never put customer data in logs, URLs or error trackers; encrypt NID, passport, TIN and bank numbers; hide prices, discounts, costs, payroll and commission without their permissions; serve files only through authorized controllers or short-lived signed URLs.
- **Funnel numbers use the fixed definitions** in `prd.md` section 5 everywhere; dashboard totals must equal report and ledger totals.
- **Public website:** server-rendered, cached per tenant and language, purged on publish and on unit status or price changes; lead forms go through `CaptureLeadAction` only.
- Scheduled jobs (hold expiry, reminders, late charges, alerts) must be idempotent and tenant-aware.
- The mobile app is a separate project; build the API only.
- Never use emojis or Unicode symbol icons anywhere (UI, lang files, SMS, documents, comments, commits). Use Font Awesome icons through `AppIcon`.
- Unit status colors always come with a text label or letter code.
- Follow the compact control sizes and table alignment rules in `docs/design.md` (sections 23 to 25).
- Never use `window.alert()`, `confirm()` or `prompt()`; use the shared dialog components.
- If something is unclear or contradictory, ask before assuming.
- Write Pest tests for each feature and run them before saying a feature is finished.
- Run `./vendor/bin/pint` and `./vendor/bin/phpstan` before finishing.
