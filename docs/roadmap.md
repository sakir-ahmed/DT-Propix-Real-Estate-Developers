# ROADMAP: DT Propix

> The AI builds **one phase at a time**, runs tests, and stops. Each phase must satisfy the Definition of Done in `architecture.md`.
> **First sellable release = Phases 0 to 7** (public website, CRM, projects and units, booking with payment schedules, collections, dashboard). Phases 8 to 13 are upgrades for Growth and Pro plans.

Mark finished phases with `[x]`.

---

## Phase 0: Foundation, Tenancy & UI Kit

- [ ] Docker Compose (nginx, php-fpm with Chromium, mysql, redis, queue, scheduler), `.env.example`
- [ ] Multi-tenancy: `Tenant`, `ResolveTenant`, `ResolveWebsite`, `BelongsToTenant`, `BelongsToBranch`, tenant-aware jobs, prefixed cache and storage
- [ ] Architecture test that fails when a tenant-owned model lacks the trait
- [ ] Inertia + Vue 3 + Tailwind v4 tokens (`design.md`), Sonner, dark mode, SSR on public pages only
- [ ] Localization middleware, `en` / `bn` files, language switcher
- [ ] Staff auth (username / email / mobile), OTP service, rate limits, idle timeout
- [ ] Spatie Permission with teams, per-tenant role seeding, platform `super-admin`
- [ ] Activity log, access log for exports and document views, encrypted backups, tenant export command
- [ ] Minimal `/platform` console: create tenant in under 5 minutes, plan, limits, trial dates, message credits
- [ ] `PlanGate` skeleton (limits, modules, trial and suspension rules)
- [ ] **Shared UI kit** from `design.md` section 22 with `AppIcon` and `NoEmojiTest` in CI
- [ ] OpenAPI docs generator at `/api-docs`
- [ ] Hidden `/admin/ui-kit` showcase page that renders every component in light, dark, `en` and `bn`

**Acceptance:** `migrate:fresh --seed` works; two seeded tenants cannot see each other's data (tested); each role sees only its menu; the UI-kit page renders.

## Phase 1: Company Setup, Projects & Unit Inventory

- [ ] Setup wizard, branches, company profile, document number series
- [ ] Projects, blocks, floors, units and plots; unit types and attributes; landowner share
- [ ] `PriceUnitAction`, price lists, offers, permission-protected prices
- [ ] `ChangeUnitStatusAction` with history and illegal transition rules
- [ ] Availability grid and plot list/map views, unit detail slide-over
- [ ] Unit and project import (dry run, row errors, safe re-run), bulk price change with approval
- [ ] Project documents (floor plans, brochures) on the private disk

**Acceptance:** price breakdowns match hand calculations for area, rate, premiums and offers (tested); illegal status transitions are rejected (tested); a user without `units.view-price` never receives prices from any route or API (tested); importing the same file twice creates no duplicates.

## Phase 2: Public Website, SEO & Media

- [ ] `ResolveWebsite` host mapping, subdomain and custom domain with verification and TLS
- [ ] Pages, menus, sections, banners, blog, careers, testimonials, team, custom pages (bilingual)
- [ ] Project and property pages with gallery, floor plans, map, and availability summary according to project settings
- [ ] Media pipeline (WebP, responsive sizes, alt text required), gated brochures
- [ ] SEO: meta fields, sitemap per tenant, `robots.txt`, redirects, JSON-LD, `hreflang`
- [ ] Per-tenant cache by language with purge on publish and on unit status or price change
- [ ] Branding settings with accent contrast check; privacy and terms pages

**Acceptance:** an unknown host returns 404; one tenant's site never shows another's content (tested); a unit that becomes booked leaves public search within a minute; Lighthouse SEO 95+ and accessibility 90+ on the seeded demo site; LCP under 2.5 seconds on the mobile profile.

## Phase 3: CRM & Lead Capture

- [ ] `CaptureLeadAction` (idempotent, merge by mobile, Turnstile, honeypot, rate limits, UTM capture) and website forms
- [ ] Leads, sources, campaigns, assignment rules, duplicate merge
- [ ] Pipeline board, today's follow-ups, lead 360 with timeline, lost reasons
- [ ] Site visit calendar, confirmation messages, feedback
- [ ] Lead import, telecaller call lists
- [ ] Source and campaign reports

**Acceptance:** the same submission sent twice creates one lead; a repeat enquiry by mobile adds an activity, not a duplicate; sales executives see only their own leads (tested); logging a follow-up outcome takes three taps; spam is blocked.

## Phase 4: Customers, Holds & Booking

- [ ] Customers with joint buyers, nominee, KYC documents, encrypted NID and passport, duplicate detection
- [ ] Holds with expiry and the idempotent release job
- [ ] Booking desk from `design.md` section 6: price snapshot, discount rules, booking money
- [ ] Payment plan templates and the schedule builder with total check
- [ ] Approval engine (used here for discounts) with inbox, reminders and escalation
- [ ] Booking form and allotment letter PDFs, QR verification

**Acceptance:** two simultaneous bookings of the same unit produce exactly one booking (concurrency test plus DB constraint); the snapshot never changes after later price edits; schedule totals equal the net price; a discount over the limit waits for approval.

## Phase 5: Agreement, Collections & Outstanding

- [ ] Agreement generation from versioned templates, signed scan upload, schedule lock, sale status
- [ ] Schedule revisions through approval
- [ ] Collection desk: allocation, advances, cash, cheque flow, bank transfer, mobile banking, card or POS; receipt PDF and reversal with approval
- [ ] Late charges job and waiver approval
- [ ] Outstanding and aging, demand notices, customer statements (single and bulk), follow-up notes and promises
- [ ] Payment proof verification queue

**Acceptance:** allocations plus advances equal collections exactly (tested); a bounced cheque reverses its receipt correctly; aging totals equal customer ledger balances; a locked schedule cannot be edited except by revision; receipts and numbers are gapless under concurrency.

## Phase 6: Messaging, Tasks & Documents

- [ ] `MessageChannel` with SMS, email and WhatsApp, templates, credits wallet, logs, opt-out
- [ ] Automatic messages: receipts, due reminders, visit reminders, hold expiry
- [ ] Campaigns with audience filter and cost estimate first
- [ ] Tasks (generic) and "my tasks"
- [ ] Document management: library, versions, visibility, expiry alerts, signed downloads, access log
- [ ] Document generation for demand letters, no-objection letters and others

**Acceptance:** bulk sends show segments and credits first and fail safely at zero credits; credits and logs never diverge (tested); a document download by an unauthorized user returns 404; reminder jobs running twice send once.

## Phase 7: Management Dashboard & Reports

- [ ] Dashboard with the funnel from `prd.md` section 5 (Leads, Site visits, Bookings, Sales, Collection, Outstanding, Expenses, Profit) and drill-down
- [ ] Reports with PDF and Excel export following `design.md` section 17: sales, collection, aging, project performance, team and source performance, cancellations
- [ ] Aggregated tables and queued long reports
- [ ] Sales targets and leaderboards
- [ ] Onboarding checklist and demo data

**Acceptance:** dashboard totals equal report totals and ledger totals exactly (tested); the dashboard loads in under 3 seconds on a seeded tenant with 20,000 leads, 5,000 units and 3 years of data. **Expenses and Profit use the accounts module when it is enabled; before Phase 9 they show the manual expense totals only. This completes the first sellable release.**

## Phase 8: Customer Portal & Support Tickets

- [ ] Customer guard with mobile OTP, portal pages (bookings, schedule, receipts, statements, documents, notices)
- [ ] Payment proof upload, requests and complaints with SLA, assignment, escalation, rating
- [ ] Construction progress view (data from Phase 11 when present)
- [ ] Notice board

**Acceptance:** a customer sees only their own data and anything else returns 404 (tested); statements download through short-lived signed URLs; SLA breaches escalate once.

## Phase 9: Accounts

- [ ] Chart of accounts, fiscal years, periods with locking
- [ ] `PostJournalEntryAction`, automatic postings from receipts, refunds, expenses, bills
- [ ] Vouchers, cash and bank books, manual bank reconciliation
- [ ] Vendors, bills, payables, simple purchase orders, expenses with approval
- [ ] Reports: trial balance, ledger, income statement, balance sheet, cash flow, receivables, payables, day book
- [ ] Cost centers by project

**Acceptance:** every journal balances (tested); posted entries cannot be edited; a locked period rejects postings; the trial balance equals the ledgers; the customer receivable equals scheduled minus collected.

## Phase 10: Brokers, Agents & Commission

- [ ] Broker profiles and KYC, broker portal (limited), referral tracking and conflict rules
- [ ] Commission rules (percentage or fixed, on price or collection, staged)
- [ ] Accrual on collection, reversal on cancellation, approval, payout vouchers, locked statements
- [ ] Employee sales incentives linked to bookings

**Acceptance:** commission amounts match hand-calculated examples for every rule type; a cancellation reverses accrued commission; a paid statement cannot be edited; a broker never sees another broker's data (tested).

## Phase 11: Cancellation, Refund, Transfer, Project Costing & Construction Progress

- [ ] Cancellation with deduction rules, refund schedule and payments, unit release after approval
- [ ] Booking transfer with fee and history
- [ ] Handover checklist and possession letter
- [ ] Project budgets, actual cost from bills and payroll allocation, cost per sq ft, project profit
- [ ] Milestones, percentage, photos, customer-visible flag, work orders

**Acceptance:** refunds never exceed collected minus deductions (tested); every cancellation and transfer keeps full history and balanced journals; project profit equals journal results for the cost center.

## Phase 12: HR & Payroll

- [ ] Employees, departments, designations, documents, contracts
- [ ] Attendance (manual, import, web check-in), shifts, holidays, leave types and requests
- [ ] Payroll: structures, allowances, deductions, advances and loans, monthly run, approval, lock, payslips, journal posting
- [ ] Recruitment from website careers

**Acceptance:** payroll for a seeded month matches hand-calculated payslips; an approved month cannot be edited; salary journal balances; payroll data is visible only to permitted roles.

## Phase 13: Subscription, Billing, Polish & Hardening

- [ ] Platform console: plans, module toggles, limits, suspend or resume, usage, impersonation with audit
- [ ] Trial banner, 7-day read-only grace, suspension, scheduled retention, subscription records, message credit top-ups
- [ ] API review for the future mobile app, OpenAPI examples, contract tests that keep `v1` stable
- [ ] Security review (access logs, encryption, backup restore drill), accessibility pass, performance check with 50,000 leads and 10,000 units
- [ ] User guide (English and Bangla), onboarding guide for Datatech staff, deployment guide (including custom domain TLS)

**Acceptance:** full test suite green, Pint and Larastan clean, a restore drill passes, nightly reconciliation shows zero differences on the performance dataset, limits for projects, units and users are enforced everywhere (tested), and a new company can be set up with its website live and first lead captured within one day.

---

## Later Modules (not in this project scope)

Mobile apps for sales staff and customers, online installment payments (bKash, Nagad, cards), electronic signature, rental and facility management (leases, rent, maintenance), advanced procurement and inventory, statutory tax filing, marketplace listings, 3D and BIM tours.
