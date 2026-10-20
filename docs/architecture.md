# ARCHITECTURE & CODING RULES: DT Propix

> Shared engineering rules for Datatech products, adapted for DT Propix (one installation per company for real-estate developers and agencies: public website, CRM, unit inventory, booking and installments, accounts, HR, customer portal). Sections 11 to 17 are specific to this product.
> If this file conflicts with the product's `prd.md`, **the PRD wins** for business rules and roles. This file wins for code structure.

---

## 1. Tech Stack

| Area                    | Choice                                                                                                                                                     |
| ----------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Framework               | Laravel 13.x (latest stable), PHP 8.3+. Check with `composer show laravel/framework`.                                                                      |
| Database                | MySQL 8                                                                                                                                                    |
| Cache / Session / Queue | Redis (`CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION` = `redis`)                                                                                      |
| Frontend                | Inertia.js v2, Vue 3 (`<script setup>`), Tailwind CSS v4, shadcn-vue (Radix Vue)                                                                           |
| Toasts                  | `vue-sonner`                                                                                                                                               |
| Auth                    | Staff: username / email / mobile login. Customers: mobile OTP (separate `customer` guard, portal only). Spatie Permission without teams. This installation is one company. |
| Real-time               | Not required in v1. Use page refresh and light polling; notifications go through the in-app bell, SMS and email.                                           |
| Audit                   | `spatie/laravel-activitylog`                                                                                                                               |
| Backup                  | `spatie/laravel-backup` (daily, via scheduler)                                                                                                             |
| Localization            | `en` and `bn` (`lang/en`, `lang/bn`)                                                                                                                       |
| SMS                     | BdBulkSMS through `SmsChannel` (sections 5 and 16)                                                                                                         |
| Testing                 | Pest                                                                                                                                                       |
| Code quality            | Laravel Pint, Larastan (level 5 minimum)                                                                                                                   |
| Containers              | Docker Compose: nginx, php-fpm (with Chromium), mysql, redis, queue worker, scheduler                                                                      |
| Icons                   | Font Awesome Free via `@fortawesome/vue-fontawesome` (tree-shaken SVG), wrapped in `AppIcon`                                                               |
| Charts                  | `chart.js` + `vue-chartjs` (one library only)                                                                                                              |
| Deployment              | One app and one database per company. No tenant id and no platform billing (section 11)                                                                    |
| PDF                     | `spatie/laravel-pdf` with the Browsershot (headless Chromium) driver                                                                                       |
| QR                      | `endroid/qr-code` (receipt and document verification codes)                                                                                                |
| Translatable content    | `spatie/laravel-translatable` (English and Bangla content columns for projects, pages, posts)                                                              |
| Images                  | `intervention/image` (WebP derivatives, responsive sizes, watermark option)                                                                                |
| Sitemap / SEO           | `spatie/laravel-sitemap`, `robots.txt`, JSON-LD helpers                                                                                                    |
| Maps                    | Leaflet with OpenStreetMap-compatible tiles (provider in `.env`); Google Maps only if the client supplies a key                                            |
| Email                   | Laravel Mail with the company's SMTP or a shared provider (SES, Mailgun)                                                                                   |
| WhatsApp                | WhatsApp Business Cloud API (Meta) through `MessageChannel` (section 16)                                                                                   |
| Excel import / export   | `maatwebsite/excel`                                                                                                                                        |
| API docs                | `dedoc/scramble` (OpenAPI at `/api-docs`)                                                                                                                  |
| Bot protection          | Cloudflare Turnstile on public inquiry forms, careers and OTP forms                                                                                        |

**Rendering**

- Public pages (`Web/` controllers): the company's public website (home, projects, properties, blog, careers, contact, and more). Inertia SSR with dynamic meta tags, the company's own domain and cached output (section 13).
- The customer portal after login is client-side only.
- Admin panel (`/admin`): client-side SPA, **SSR disabled**. No Filament.

---

## 2. Folder Structure

```text
app/
├── Actions/            # One class = one business operation (CreateOrderAction)
├── Services/           # External integrations + orchestration (SmsService, BkashService)
├── DataTransferObjects/# Only when an Action has 4+ inputs
├── Enums/              # OrderStatus, PaymentMethod ...
├── Http/
│   ├── Controllers/
│   │   ├── Admin/      # Inertia controllers for the SPA (booking and collection desks included)
│   │   ├── Api/V1/     # JSON API (see api.md)
│   │   └── Web/        # Public SSR pages
│   ├── Requests/       # Form Requests (all validation lives here)
│   └── Resources/      # API Resources
└── Models/             # Relationships, scopes, casts, LogsActivity. No business logic.
```

**No Repository layer.** Eloquent is the data layer. Add an interface only when there are two or more real implementations. In this product that applies to one place: `MessageChannel` (SMS, email, WhatsApp) in `app/Services/Messaging/`. There are no payment gateways in v1 (collections are cash, cheque, bank and mobile money entered by staff, or submitted by customers as payment proof). Approval workflows use one engine, not one class per request type.

---

## 3. Core Rules

1. **Thin controllers.** Validate (Form Request) → call an Action → return a response.
2. **Admin controllers and API controllers call the same Actions.** Never duplicate business logic.
3. **Strict types.** `declare(strict_types=1);`, typed parameters, return types and properties everywhere.
4. **No over-engineering.** No factories, abstractions or patterns without a concrete need today.
5. **Money:** store as `decimal(12,2)` in BDT. Never use floats. API returns money as strings (`"1250.00"`).
6. **Bookings, payment schedules, collections, allocations, journal postings and payroll run inside `DB::transaction()`.** Use `lockForUpdate()` on the unit row (to stop double booking), on document number counters and on balances.
7. **Branch scoping:** every branch-owned model uses a global branch scope (section 11); non-owner users must never read another branch's data. Test this.
8. **All user-facing text uses translation keys** (`__('orders.created')`), never hard-coded strings.
9. **Secrets only in `.env`**, accessed via `config()`. Never call `env()` outside config files.
10. **No emojis anywhere** (UI, translations, SMS, receipts, seeders, comments, commits). Icons come from Font Awesome through `AppIcon`; see `design.md` section 23.
11. **Eager load relations** to avoid N+1. Add DB indexes on foreign keys and common filters (unit status, project, lead mobile, lead assignee and stage, customer code, booking number, due date, receipt number, account and period).
12. **Customer and property data rules apply everywhere** (section 12): no customer personal data in logs, URLs or error trackers, and unit prices are shown only to permitted roles and portal owners.
13. **Property areas** use `decimal(12,2)` square feet, rates use `decimal(12,2)` BDT per square foot, and totals are calculated by one `PriceUnitAction` and stored as a snapshot on the booking.

---

## 4. Roles & Permissions

Spatie Permission without teams. This installation serves one company; roles are defined in `prd.md` and seeded when the installation is created. `super-admin` is a hidden Datatech login on that server, seeded from `.env`.

Use permissions (`units.change-price`, `bookings.discount`, `bookings.cancel`, `refunds.approve`, `journals.post`, `payroll.approve`), not role names, in policies and `@can` checks. The `super-admin` credentials come from `.env` values (never hard-code a password).

---

## 5. Authentication, OTP & SMS

### Login (username, email or mobile)

```php
public function getCredentials(): array
{
    $login = trim($this->string('login')->toString());

    $field = match (true) {
        filter_var($login, FILTER_VALIDATE_EMAIL) !== false => 'email',
        preg_match('/^(\+?88)?01[3-9]\d{8}$/', $login) === 1 => 'mobile',
        default => 'username',
    };

    if ($field === 'mobile') {
        $login = normalize_bd_mobile($login); // -> 8801XXXXXXXXX, store mobiles in this format
    }

    return [$field => $login, 'password' => $this->input('password')];
}
```

Throttle login with `RateLimiter` (5 attempts per minute per login + IP).

### SmsService (transport only)

```php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $mobile, string $message): bool
    {
        $response = Http::asForm()
            ->timeout(10)
            ->post(config('services.bdbulksms.url'), [
                'to' => $mobile,
                'message' => $message,
                'token' => config('services.bdbulksms.token'),
            ]);

        // Confirm the exact success format in the BdBulkSMS API docs.
        $sent = $response->successful() && str_starts_with($response->body(), 'Ok');

        $sent
            ? Log::info('SMS sent.', ['mobile' => $mobile])
            : Log::error('SMS failed.', ['mobile' => $mobile, 'status' => $response->status()]);

        return $sent;
    }
}
```

- **Never disable SSL verification** (`'verify' => false` is forbidden).
- Config in `config/services.php`: `bdbulksms.url`, `bdbulksms.token`.
- Send SMS through a queued Job so a slow gateway never blocks a request.

### OtpService (rules)

- Generate with `random_int(100000, 999999)`.
- Store **hashed** (`Hash::make`) in Redis cache, key `otp:{mobile}`, **TTL 5 minutes**.
- Send limit: `RateLimiter` keyed by **IP + mobile** (max 5 per hour). Not session-based; clearing cookies must not bypass it.
- Verify limit: max 5 wrong attempts per code, then invalidate the code.
- Delete the code after successful verification.

---

## 6. Logging

Use `Log` with PSR-3 levels and context arrays. Never log passwords, OTPs, tokens, card or wallet numbers, or customer personal data (section 12).

```php
Log::info('Order created.', ['order_id' => $order->id, 'branch_id' => $order->branch_id]);
Log::warning('Stock below threshold.', ['unit_id' => $unitId]);
Log::error('Payment failed.', ['order_id' => $id, 'error' => $e->getMessage()]);
```

Do not log the stack trace in every message; the exception handler already does it. Log with `'exception' => $e` only when needed.

---

## 7. Activity Log & Backup

`LogsActivity` is a **trait**, not an interface:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'mobile', 'username'])
            ->logOnlyDirty();
    }
}
```

- Use `logOnly([...])`, never `logAll()` (it can log password hashes).
- Explicitly log sensitive actions with `activity()->causedBy($user)->performedOn($model)->log('order.voided')`.
- Backup: `backup:run --only-db` daily via scheduler to S3 or a mounted volume, `backup:clean` weekly, and notify on failure.

---

## 8. Localization

- Files: `lang/en/*.php` and `lang/bn/*.php`. Every key must exist in both.
- A middleware sets the locale from user preference → session → default `en`.
- Vue receives translations through Inertia shared props.
- Dates and numbers: format through one helper so Bangla digits can be toggled.

---

## 9. Frontend Rules

- `<script setup>` only. One component = one responsibility.
- Show feedback on every save/delete/error with `toast.success()` / `toast.error()`.
- Handle loading, empty and error states in every list and form.
- Inertia forms use `useForm`. Show server validation errors beside fields.
- Visual and interaction rules are in `design.md`. Dialogs, confirmations, validation messages, dashboards, charts, reports and tables **must** follow sections 12 to 22 there.
- Build the shared UI kit (`resources/js/components/ui-kit/`) first and reuse it. Never use `window.alert()`, `confirm()` or `prompt()`, and never build a one-off dialog, table or chart inside a page.
- Validation lives in Form Requests. Display errors with the `FormField` component, focus the first invalid field, and never clear the form on error.

---

## 10. Testing & Definition of Done

A feature is **done** only when all of these are true:

- [ ] Pest feature tests cover the happy path and main failure cases
- [ ] Authorization tested (allowed role passes, other role gets 403, other branch gets 404)
- [ ] No customer personal data in logs, URLs or error trackers (section 12)
- [ ] Where money is involved, journals balance and the reconciliation tests pass (customer ledger, installments, accounts, commissions)
- [ ] `./vendor/bin/pint` is clean and `./vendor/bin/phpstan` passes
- [ ] Both `en` and `bn` translations added
- [ ] Sensitive actions write an activity log entry
- [ ] `NoEmojiTest` passes (no emojis or symbol icons in code, lang files, views or docs)
- [ ] The UI Definition of Done checklist in `design.md` (section 22) passes
- [ ] Migrations run fresh (`migrate:fresh --seed`) without errors
- [ ] The acceptance criteria for the feature in `roadmap.md` are met

---

## 11. One Company per Installation

- **Model:** one Laravel application and one MySQL database per real-estate company. Datatech deploys both by hand and hands that server to the company. There is no `tenant_id`, no `ResolveTenant` middleware, and no client-supplied company id. Branches of one company share that server.
- **Branches:** the company may add branches in that same database. `BelongsToBranch` scopes non-owner users to their assigned branches. Owners and the super-admin see every branch. An architecture test fails if a model with a `branch_id` column lacks the trait. Unique keys are per this database (for example `unique(customer_code)`).
- **Queues and scheduler:** jobs run for this installation only. There is no loop over tenants.
- **Storage:** private disk paths stay inside this server (`customers/{id}/...`). Never use public storage for customer documents.
- **Accounts:** the seeder creates the first `owner` and the hidden Datatech `super-admin` from `.env`. There is no `/platform` console, no impersonation, no plan gate, and no subscription state.
- **Tests:** every branch-owned model and endpoint has a cross-branch test (a manager of branch A gets 404 for branch B, including the public website and portal).
- **Export:** a command exports this installation's data (CSV or JSON plus files).

---

## 12. Customer, Financial & Property Data Rules

Real-estate data is sensitive: national ID and passport copies, TIN, addresses, large payments, prices and discounts, and legal documents.

- **Never put personal data** (customer or lead names, mobiles, NID or passport numbers, addresses, payment details) in logs, exception messages, error trackers, URLs or query strings. Use IDs only.
- **Encrypt** NID, passport, TIN and bank account numbers with the `encrypted` cast. Searchable fields (name, mobile, customer code, booking number) stay plain so they can be indexed.
- **Files** (agreements, deeds, NID copies, cheque images, payment proofs, floor plan originals, brochures not meant for public download) live on a private disk and are served only through authorized controllers or short-lived signed URLs. Public website media (gallery, brochures marked public) are copied to a separate public disk on publish.
- **Least access:** sales executives see only their own leads and bookings unless the owner widens it; unit prices, discounts and costs are permission-protected (`units.view-price`, `bookings.view-discount`, `projects.view-cost`); HR and payroll are visible only to HR roles and the owner; commission data only to permitted roles.
- **Customer portal:** a customer sees only their own bookings, schedules, receipts and documents marked as visible. Anything else returns 404.
- **Access log:** viewing a customer profile, a document, or exporting leads or customers writes an access log row. Bulk exports need a permission and are logged. Broker and employee data have the same protection.
- **No hard deletes** of leads with activity, customers, bookings, payments, journals or documents. Use statuses or voiding with a reason. Anonymize only through a documented command.
- **Sessions:** idle timeout (default 30 minutes in the admin), strong password policy, optional TOTP for owners and accountants.
- **Backups** are encrypted and a restore drill is documented.
- Confirm applicable data protection, real-estate sales and registration rules with a legal advisor before launch.

---

## 13. Public Website, SEO & Media

- **One installation, one website.** The company's public website is served on the company's own domain (a local host in development). The domain needs DNS verification and automatic TLS through a front proxy (for example Caddy with on-demand TLS, or a CDN service).
- **Rendering:** Inertia SSR with dynamic meta, OpenGraph and Twitter tags. Public pages are cached per language (Redis tags), purged when the editor publishes or a project, unit status or price changes. Target: LCP under 2.5 seconds on a mid-range phone, Lighthouse SEO and accessibility 95 or more.
- **URLs and languages:** clean slugs, `/en/...` and `/bn/...` with `hreflang`, canonical URLs, a language switcher that keeps the page. Translatable content is stored per language; a missing translation falls back to the default language and is flagged to editors.
- **SEO:** per-page meta fields with sensible defaults, a sitemap, `robots.txt`, a redirects manager (301 for changed slugs), and JSON-LD: `Organization`, `BreadcrumbList`, `Article`, `JobPosting`, and a listing type for projects and units.
- **Media pipeline:** uploads are validated and processed in a queue into WebP derivatives with responsive sizes; originals stay private; alt text is required for published gallery images; video is an external embed (YouTube or Vimeo) by default, with uploaded files optional and size-limited; PDFs (brochures, floor plans) can be public or **gated** (download requires lead capture).
- **Availability on the site:** only data marked publishable appears. Each project chooses what the website shows: available units only, or also booked and sold (shown as "Sold" for social proof), and whether prices are shown or "on request". A unit that becomes booked disappears from search within a minute (cache purge).
- **Lead capture:** every public form posts to one `CaptureLeadAction` that validates, applies anti-spam (Turnstile, honeypot, rate limit), stores UTM and referrer data, merges into an existing lead when the mobile matches (adds an activity instead of a duplicate), assigns the lead, notifies the assignee, and returns a success message. It is idempotent per submission token.
- **Forms** never expose whether a mobile already exists. Errors are generic and rate limited.
- **Maps:** coordinates per project and unit; map tiles load only on the page that needs them; no personal data in map requests.
- **Careers:** job posts publish as `JobPosting` markup; applications create applicant records with private CV files.
- **Privacy:** cookie and consent banner only if analytics or advertising scripts are enabled; privacy policy and terms pages are required before publishing a form.

---

## 14. Inventory, Booking & Payment Schedule Integrity

- **Unit status is a state machine** changed only by `ChangeUnitStatusAction`: `available -> on_hold -> booked -> sold -> handed_over`, plus `landowner_share`, `blocked` and the return paths (`on_hold` expiry, cancellation, transfer). Illegal transitions are rejected and every change writes a history row.
- **No double booking:** booking or holding a unit locks the unit row (`lockForUpdate()`) inside a transaction, checks the status, and writes the booking. A database constraint also guarantees one active booking per unit. Concurrency tests are mandatory.
- **Holds expire:** a reservation or hold has an expiry; a scheduled job releases it (idempotent) and notifies the salesperson.
- **Price snapshot:** a booking stores the unit's area, rate, premiums, discounts, offers, extra charges and total at booking time. Later price changes never change an existing booking.
- **Payment schedules** are generated from a template (booking money, down payment, installments, special or milestone payments, handover payments, other charges). A schedule is a set of dated, numbered rows with amounts. After the **agreement is signed**, the schedule is immutable; changes (reschedule, waiver, partial date shifts) create a new schedule **version** through the approval workflow and keep the old one for history.
- **Allocation:** a collection is allocated to schedule rows (earliest due first, or as chosen) as stored rows. The paid and due amounts of every row are calculated from allocations. Unallocated money is an explicit **advance** on the booking. Nobody types a due amount.
- **Late charges** (optional company setting): computed by an idempotent job after a grace period, added as separate charge rows with a reason, waivable only through approval.
- **Cancellation, refund and transfer** are documents with their own workflow and journal postings: cancellation applies the company's deduction rules; a refund schedules payments and cannot exceed what was collected minus deductions; a transfer moves the booking to a new customer with a fee and an approval, keeping the full history.
- **Document numbers** (booking, agreement, receipt, voucher) are sequential per series under a lock, without gaps; voided numbers stay visible.

---

## 15. Accounting & Collections Integrity

- **Double-entry ledger.** All postings go through a single `PostJournalEntryAction`. Controllers and other Actions never write journal lines directly. A journal must balance (debits equal credits), checked in code and in tests.
- **Same transaction:** the business event (receipt, payment, expense, commission, payroll) and its journal posting happen in the same `DB::transaction()`. If posting fails, the event fails.
- **Immutable:** posted journals, receipts and vouchers are never edited. Corrections are reversal entries that reference the original, with a reason and approval.
- **Periods:** fiscal years and monthly periods can be locked; a locked period rejects postings.
- **Cost centers:** every income and expense line can carry a `project_id` (and optionally building). Project-wise profitability is computed from journal lines, not from a separate shadow calculation.
- **Account mapping** (which account a booking money, installment, refund, expense category or commission posts to) is configuration, not hard-coded IDs.
- **Customer ledger** is the set of journal lines for a customer's receivable account and booking; the balance equals scheduled amounts minus allocated collections. Reconcile in tests.
- **Collections:** money receipt per collection; methods cash, cheque, bank transfer, mobile money (TrxID) and card or POS. Cheques have a status flow (received, deposited, cleared, bounced) and a bounce reverses the receipt. Customer-submitted **payment proofs** wait for verification by the accountant before becoming a receipt.
- **Commissions** accrue by rule (for example a share when the down payment completes and the rest as installments are collected), reverse on cancellation, and are paid through vouchers; paid statements are locked.
- **Payroll** is generated per month from employee records, attendance and leave, approved, locked, and posted as a journal. Corrections are adjustments in the next month or an approved reversal.
- Totals shown in dashboards must equal report totals; add reconciliation tests for sales, collection, receivables, cash and bank, and project profit.

---

## 16. Workflows, Documents, Messaging, Tasks & Scheduled Jobs

- **One approval engine.** Workflows are company configuration: a request type (discount above a limit, booking cancellation, refund, transfer, price change, payment schedule change, expense above a limit, leave, payroll, commission payout, purchase), ordered steps (role or user, optional amount thresholds), and outcomes. `RequestApprovalAction` creates a request that blocks the business action until approved; every decision is logged with user, time and comment. The engine has an inbox, reminders and escalation after a timeout. Do not write special approval code per feature.
- **Document management:** one `documents` table with category, version history, owner entity (customer, booking, unit, project, land parcel, employee, vendor), visibility (internal, customer-visible), optional expiry date with alerts, and private storage. A document is never overwritten; a new upload creates a version.
- **Document generation:** booking forms, agreements, allotment letters, demand letters and receipts come from HTML templates with placeholders, rendered with Browsershot (headless Chromium) so Bangla renders correctly; each generated file is stored as a document with the template version used. Signed copies are uploaded scans (no e-signature in v1).
- **Messaging:** a `MessageChannel` interface with `SmsChannel` (BdBulkSMS), `EmailChannel` and `WhatsAppChannel`. Templates per channel and per language with placeholders and preview. Sends are queued, logged (template, channel, status, cost), rate limited, respect opt-out for non-essential messages, and SMS and WhatsApp consume prepaid credits in the same transaction that writes the log. WhatsApp uses pre-approved templates; manual chat is out of scope. Bangla SMS is Unicode (70 characters per segment); show the segment count and cost before every bulk send.
- **Tasks:** one `tasks` table for generic work items (assignee, due date, priority, status, related entity). **Lead follow-ups are activities** with a due date and appear in the CRM "today" list; they are not duplicated as tasks.
- **Imports:** `maatwebsite/excel`, queued and chunked, with a dry-run, a row-level error report, a safe re-run keyed on an external ID, and a summary. Legacy import covers leads, customers, units, bookings with payment schedules, and received payments.
- **Scheduled jobs** (hold expiry, installment due reminders, overdue and late charge calculation, follow-up reminders, lead reassignment of inactive leads, document expiry alerts, backups, daily owner summary) must be **idempotent**: running twice never duplicates anything.

---

## 17. Reports & Dashboards

- Dashboards read from aggregated tables refreshed by queued jobs plus live queries for today's numbers. Every KPI links to a report with the same filters and exactly the same total.
- The funnel (Leads, Site Visits, Bookings, Sales, Collection, Outstanding, Expenses, Profit) uses fixed definitions documented in `prd.md` section 5 so every screen agrees.
- Long reports (more than about 10,000 rows) run as queued jobs with a notification and download link.

---

## 18. Working Method (for the AI)

1. Read `docs/prd.md`, `docs/api.md`, `docs/design.md`, `docs/roadmap.md`.
2. Build **one phase at a time** from `roadmap.md`. Do not start the next phase.
3. For each feature: migration → model → Form Request → Action → controller → Vue page → tests.
4. If something in the docs is unclear or contradictory, **ask before assuming**.
5. Run tests and fix failures before reporting a feature as finished.
