# DT Propix

**Run your entire real-estate business from one platform.**
Developed by **Datatech BD Ltd**, Dhaka.

DT Propix gives developers, agencies and property companies one system for their public website, leads, projects and units, bookings and installments, collections, accounts, HR, customer service and a customer portal, with a management dashboard that shows the whole funnel.

```text
Leads -> Site Visits -> Bookings -> Sales -> Collection -> Outstanding -> Expenses -> Profit
```

Built for real-estate developers, agencies, property management companies (sales side), land and project businesses, and commercial property businesses. Bilingual (English and Bangla). Multi-tenant SaaS: many companies on one platform.

> This project is built phase by phase from the specification in `docs/`. Start with `docs/roadmap.md`. The mobile app is a separate project; this repository provides the API.

---

## Features

- **Public website:** bilingual pages, projects and listings, galleries, floor plans, maps, blog, careers, SEO, custom domain, lead forms
- **CRM:** leads from every source, pipeline, follow-ups, site visits, telecaller lists, campaign reports
- **Projects and inventory:** blocks, floors, units and plots, pricing engine, availability grid, holds, landowner share
- **Booking and sales:** customers, price snapshot, discount approvals, payment schedules, agreements, cancellation, refund, transfer, handover
- **Collections:** installments, cheques, mobile banking, receipts, late charges, aging, statements, due reminders
- **Brokers and commission:** referral tracking, commission rules, payouts, broker portal
- **Accounts:** double-entry books, vouchers, bank and cash, vendors and bills, project cost centers, financial reports
- **HR and payroll:** employees, attendance, leave, payroll, recruitment
- **Project costing and construction progress:** budget versus actual, milestones with photos
- **Documents, tickets, tasks and approvals:** document library with versions and expiry, complaints with SLA, one approval engine
- **Messaging:** SMS, email and WhatsApp with templates and prepaid credits
- **Customer portal:** schedule, receipts, documents, construction progress, requests
- **Management dashboard and reports:** the full funnel with drill-down
- **Platform console (Datatech):** tenants, plans, trials, credits, domains, import monitor

Full scope and business rules: `docs/prd.md`.

---

## Tech Stack

| Area                  | Technology                                                              |
| --------------------- | ----------------------------------------------------------------------- |
| Backend               | Laravel 13.x, PHP 8.3+                                                  |
| Database              | MySQL 8 (shared database, `tenant_id` isolation)                        |
| Cache, session, queue | Redis                                                                   |
| Frontend              | Inertia.js v2, Vue 3, Tailwind CSS v4, shadcn-vue, SSR for public pages |
| Icons, charts, maps   | Font Awesome Free, Chart.js, Leaflet                                    |
| Auth and roles        | Laravel Sanctum, Spatie Permission (teams), customer OTP guard          |
| Audit and backup      | Spatie Activitylog, Spatie Backup                                       |
| PDF, import, media    | spatie/laravel-pdf (Browsershot), Laravel Excel, Intervention Image     |
| Messaging             | BdBulkSMS, Laravel Mail, WhatsApp Cloud API                             |
| SEO                   | spatie/laravel-sitemap, JSON-LD, hreflang                               |
| Testing and quality   | Pest, Playwright, Laravel Pint, Larastan                                |
| Containers            | Docker and Docker Compose                                               |

---

## Documentation

AI coding agents read `CLAUDE.md` first.

| File                   | Purpose                                                                                                              |
| ---------------------- | -------------------------------------------------------------------------------------------------------------------- |
| `docs/prd.md`          | Product requirements: roles, modules, funnel definitions, business rules, plans, scope                               |
| `docs/architecture.md` | Stack, structure, coding rules, multi-tenancy, website and SEO, booking and accounting integrity, Definition of Done |
| `docs/api.md`          | API conventions, public, back-office, portal and platform endpoints                                                  |
| `docs/design.md`       | Design system, booking desk, availability grid, CRM, website and portal, icons, sizing, tables                       |
| `docs/roadmap.md`      | Build phases with acceptance criteria                                                                                |
| `docs/ui-preview.html` | Static visual mockup of the intended UI (open in a browser)                                                          |

---

## Getting Started

### Requirements

- Docker and Docker Compose, Git
- Node.js 20+ and npm (only if you run the frontend outside Docker)

### Setup

```sh
git clone <repository-url> dt-propix
cd dt-propix

cp .env.example .env
# Edit .env: database, Redis, mail, BdBulkSMS, WhatsApp, Turnstile, map tiles

docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app npm install
docker compose exec app npm run dev
```

The service name `app` follows the Docker Compose file created in Phase 0; adjust if yours differs.

The platform administrator is created by the seeder from `.env` (never commit real values):

```text
PLATFORM_ADMIN_NAME=
PLATFORM_ADMIN_EMAIL=
PLATFORM_ADMIN_PASSWORD=
```

For local development the seeder also creates two demo companies so tenant isolation can be tested. Demo websites are served on `{slug}.propix.test` hosts (add them to your hosts file or use a local wildcard DNS).

### Background processes

| Process                                                                         | Command                     |
| ------------------------------------------------------------------------------- | --------------------------- |
| Queue worker                                                                    | `php artisan queue:work`    |
| Scheduler (hold expiry, reminders, late charges, alerts, trial expiry, backups) | `php artisan schedule:work` |

---

## Development Workflow

```sh
php artisan test            # or ./vendor/bin/pest
./vendor/bin/pint           # code style
./vendor/bin/phpstan        # static analysis
php artisan migrate:fresh --seed
```

Rules:

- Build one roadmap phase at a time; finish its acceptance criteria and the Definition of Done in `docs/architecture.md`.
- Every feature has Pest tests, including authorization, scope, and **cross-tenant isolation**.
- Unit status only through `ChangeUnitStatusAction`; journals only through `PostJournalEntryAction`; reconciliation tests must pass.
- Never put customer personal data in logs or URLs.
- No emojis anywhere. Icons come from Font Awesome through `AppIcon`.
- Every text key exists in both `lang/en` and `lang/bn`.

---

## Roles

Tenant roles: `owner`, `general-manager`, `sales-manager`, `sales-executive`, `telecaller`, `broker`, `sales-accountant`, `accountant`, `project-manager`, `hr-manager`, `customer-support`, `web-editor`. Customers log in to the portal with mobile OTP. Platform role: `super-admin`.

---

## Deployment Notes

- HTTPS only; `APP_ENV=production`, `APP_DEBUG=false`.
- Custom domains need a front proxy with automatic TLS (for example Caddy on-demand TLS or a CDN service) and DNS verification.
- Run the queue worker and scheduler under a process supervisor.
- The PHP image must include Chromium and the Inter and Noto Sans Bengali fonts for PDFs.
- Schedule encrypted daily backups and test a restore before onboarding real companies.
- Run the nightly reconciliation (receivables, cash and bank, project profit) and alert on any difference.
- Confirm registration and stamp duty forms, VAT, referral and commission rules, data protection, and message sender ID rules with a lawyer and accountant. Templates ship as editable defaults without legal guarantee.

---

## Support

Datatech BD Ltd, Dhaka, Bangladesh.

## License

Proprietary. All rights reserved by Datatech BD Ltd. Not for redistribution.
