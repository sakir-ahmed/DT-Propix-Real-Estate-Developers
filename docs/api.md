# API SPECIFICATION: DT Propix

> Sections 1 and 2 are shared conventions. Sections 3 onward are specific to DT Propix.

---

## 1. Purpose & Conventions

**Who uses the API:** the public website (lead capture and listings), the customer and broker portals, a future mobile app (separate project), and integrations. **The admin web app uses Inertia controllers (session auth)** that call the _same Actions_ as the API. Do not duplicate business logic.

| Topic         | Rule                                                                                                                                                                                                   |
| ------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Base path     | `/api/v1/...` in `routes/api.php`                                                                                                                                                                      |
| Installation  | One deployment is one company. The API never accepts a company id from the client.                                                                                                                    |
| Auth          | Laravel Sanctum bearer tokens. Token abilities: `staff`, `customer`, `broker`. Public endpoints need no token.                                                                                         |
| Docs          | OpenAPI generated automatically (Scramble) at `/api-docs`; every endpoint has request and response examples.                                                                                           |
| Versioning    | No breaking changes inside `v1`: add fields freely, never rename or remove. Clients send `X-App-Version` and `X-Platform`.                                                                             |
| Format        | JSON. Success: `{"success": true, "data": {...}, "message": "Localized message"}`. Error: `{"success": false, "error": {"code": "UNIT_NOT_AVAILABLE", "message": "...", "details": {...}}}`            |
| Language      | `Accept-Language: en` or `bn`                                                                                                                                                                          |
| Money         | Decimal **string** in BDT, e.g. `"2500000.00"`                                                                                                                                                         |
| Area and rate | Decimal **string**, area in sq ft with 2 decimals, rate in BDT per sq ft with 2 decimals                                                                                                               |
| Dates         | ISO 8601 with timezone (`Asia/Dhaka`)                                                                                                                                                                  |
| Pagination    | `?page=1&per_page=25`; response includes `meta` (`current_page`, `last_page`, `total`)                                                                                                                 |
| Filtering     | `?filter[status]=available&filter[project]=12&sort=-created_at`                                                                                                                                        |
| Idempotency   | `POST` of leads (public), bookings, receipts, refunds, vouchers, journals, payroll runs, imports and bulk messages requires an `Idempotency-Key` header (public forms use a submission token).         |
| Rate limits   | Login, OTP and public forms: strict. Others: 120 per minute per token.                                                                                                                                 |
| Scope         | Sales executives see only their own leads and bookings unless widened; brokers see only their referrals; customers see only their own data. Every branch or project route checks access, else **404**. |
| Personal data | No customer data in URLs or query strings (use IDs).                                                                                                                                                   |

### Error codes

| HTTP | Code                                     | When                                                                                                                     |
| ---- | ---------------------------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| 401  | `UNAUTHENTICATED`                        | Missing or invalid token                                                                                                 |
| 403  | `FORBIDDEN`                              | Role lacks permission                                                                                                    |
| 404  | `NOT_FOUND`                              | Missing, or belongs to another scope, user or branch                                                                     |
| 422  | `VALIDATION_FAILED`                      | Invalid input, `details` has field errors                                                                                |
| 409  | `DUPLICATE_LEAD_MERGED`                  | Mobile matched an existing lead; an activity was added (returns the lead id for staff, generic success for public forms) |
| 409  | `UNIT_NOT_AVAILABLE`                     | Unit is on hold, booked, sold, blocked or landowner share (`details` has current status)                                 |
| 409  | `HOLD_EXPIRED`                           | The hold ran out before booking                                                                                          |
| 409  | `ILLEGAL_UNIT_TRANSITION`                | Status change not allowed                                                                                                |
| 422  | `SCHEDULE_TOTAL_MISMATCH`                | Schedule rows do not equal the net price (`details` has difference)                                                      |
| 422  | `DISCOUNT_NOT_ALLOWED`                   | Discount over the role's limit and no approval path                                                                      |
| 202  | `APPROVAL_REQUIRED`                      | Request created and waiting for approvers (`details` has approval id)                                                    |
| 409  | `SCHEDULE_LOCKED`                        | Editing a schedule after the agreement (use a revision)                                                                  |
| 409  | `DOCUMENT_ALREADY_POSTED`                | Editing a posted receipt, voucher or journal (use a reversal)                                                            |
| 409  | `RECEIPT_ALREADY_REVERSED`               | Reversing twice                                                                                                          |
| 422  | `ALLOCATION_EXCEEDS_DUE`                 | Allocation larger than the schedule row due                                                                              |
| 422  | `JOURNAL_UNBALANCED`                     | Debits and credits differ                                                                                                |
| 409  | `PERIOD_LOCKED`                          | Posting into a locked period                                                                                             |
| 422  | `REFUND_EXCEEDS_COLLECTED`               | Refund larger than collected minus deductions                                                                            |
| 409  | `COMMISSION_ALREADY_PAID`                | Editing a paid commission statement                                                                                      |
| 403  | `PRICE_NOT_ALLOWED` / `COST_NOT_ALLOWED` | Reading prices or costs without permission                                                                               |
| 402  | `MESSAGE_CREDIT_INSUFFICIENT`            | Not enough SMS or WhatsApp credits                                                                                       |
| 429  | `TOO_MANY_REQUESTS`                      | Rate limit hit                                                                                                           |
| 500  | `SERVER_ERROR`                           | Unexpected (details never exposed)                                                                                       |

---

## 2. Authentication

| Method | Path                                      | Notes                                                                                          |
| ------ | ----------------------------------------- | ---------------------------------------------------------------------------------------------- |
| POST   | `/api/v1/auth/login`                      | Staff `login` (username, email or mobile) + `password`; returns token.                         |
| POST   | `/api/v1/auth/logout`                     | Revokes the current token                                                                      |
| GET    | `/api/v1/auth/me`                         | User, role, permissions, branches, feature flags                                               |
| POST   | `/api/v1/auth/otp/send` and `/otp/verify` | Customers and brokers; rate limited by IP and mobile; hashed code, 5-minute expiry, 5 attempts |

---

## 3. Public Website Endpoints (`/api/v1/public`)

| Method | Path                                                | Notes                                                                                                            |
| ------ | --------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| GET    | `/projects`, `/projects/{slug}`                     | Publishable data only; filters for location, type, status, budget                                                |
| GET    | `/projects/{slug}/units`                            | Only what the project setting allows (available or also sold), prices shown or "on request"                      |
| GET    | `/pages/{slug}`, `/posts`, `/posts/{slug}`, `/jobs` | Bilingual content with fallback                                                                                  |
| GET    | `/site`                                             | Branding, menus, languages, contact data                                                                         |
| POST   | `/leads`                                            | Enquiry, callback, site visit request. Turnstile, honeypot, submission token; merges by mobile; generic response |
| POST   | `/brochure-requests`                                | Creates a lead and returns a short-lived download link for gated brochures                                       |
| POST   | `/job-applications`                                 | Applicant with CV (private file)                                                                                 |

---

## 4. Back-Office Endpoints

### CRM

| Method       | Path                                      | Notes                                             |
| ------------ | ----------------------------------------- | ------------------------------------------------- |
| GET/POST/PUT | `/leads`, `/leads/{lead}`                 | Scope by role; merge-by-mobile handling           |
| POST         | `/leads/{lead}/activities`                | Call, message, note, outcome, next follow-up date |
| POST         | `/leads/{lead}/stage`                     | Move stage; `lost` requires `reason`              |
| POST         | `/leads/{lead}/assign` and `/leads/merge` | Assignment and manual merge                       |
| GET          | `/followups?filter[due]=today`            | The "today" list                                  |
| GET/POST/PUT | `/site-visits`                            | Schedule, complete (with feedback), cancel        |
| GET/POST/PUT | `/campaigns`, `/lead-sources`             | Cost and source reports                           |
| POST         | `/imports/leads`                          | `dry_run` then commit                             |

### Projects & Inventory

| Method       | Path                                                           | Notes                                                      |
| ------------ | -------------------------------------------------------------- | ---------------------------------------------------------- |
| GET/POST/PUT | `/projects`, `/projects/{project}/blocks`, `/floors`, `/units` | Unit prices only with `units.view-price`                   |
| GET          | `/projects/{project}/availability`                             | Grid data: status counts and units by floor                |
| POST         | `/units/{unit}/hold` and `/release`                            | Row lock; expiry; extend with permission                   |
| POST         | `/units/{unit}/status`                                         | Block, unblock, landowner share (`ChangeUnitStatusAction`) |
| GET          | `/units/{unit}/price`                                          | `PriceUnitAction` breakdown for a plan or offer            |
| POST         | `/units/price-changes`                                         | Bulk price change with preview; approval above threshold   |
| GET/POST/PUT | `/price-lists`, `/offers`                                      | Effective dates                                            |
| POST         | `/imports/units`                                               | `dry_run` then commit                                      |

### Customers, Bookings & Sales

| Method       | Path                                                     | Notes                                                                                      |
| ------------ | -------------------------------------------------------- | ------------------------------------------------------------------------------------------ |
| GET/POST/PUT | `/customers`                                             | Duplicate detection; encrypted NID and passport                                            |
| GET/POST     | `/bookings`, `/bookings/{booking}`                       | Create with unit, customer, price snapshot, discount, schedule; `Idempotency-Key` required |
| POST         | `/bookings/{booking}/confirm`                            | Moves hold to booked                                                                       |
| POST         | `/bookings/{booking}/agreement`                          | Generate agreement, upload signed copy; sale begins and schedule locks                     |
| GET/POST     | `/bookings/{booking}/schedule` and `/schedule/revisions` | New version through approval                                                               |
| POST         | `/bookings/{booking}/transfer`                           | New customer, fee, approval                                                                |
| POST         | `/bookings/{booking}/cancel`                             | Reason, deduction calculation, approval                                                    |
| POST         | `/bookings/{booking}/refunds`                            | Refund schedule; checks `REFUND_EXCEEDS_COLLECTED`                                         |
| POST         | `/bookings/{booking}/handover`                           | Checklist and possession letter                                                            |
| GET/POST/PUT | `/payment-plan-templates`                                | Reusable schedule templates                                                                |

### Collections & Outstanding

| Method  | Path                                                        | Notes                                                                                        |
| ------- | ----------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| GET     | `/bookings/{booking}/dues`                                  | Open schedule rows with paid, due, late charges                                              |
| POST    | `/receipts`                                                 | Cash, cheque, bank, mobile banking, card; allocation and advance; `Idempotency-Key` required |
| POST    | `/receipts/{receipt}/reverse`                               | Reason and approval                                                                          |
| GET/PUT | `/cheques`, `/cheques/{cheque}/status`                      | Received, deposited, cleared, bounced                                                        |
| GET     | `/payment-proofs` and POST `/payment-proofs/{proof}/verify` | Customer-submitted proofs become receipts when verified                                      |
| GET     | `/outstanding?group_by=customer, project or salesperson`    | Aging buckets; totals equal the ledger                                                       |
| POST    | `/demand-notices`                                           | Generate and send                                                                            |
| GET     | `/customers/{customer}/statement`                           | JSON or PDF                                                                                  |
| POST    | `/late-charges/{charge}/waive`                              | Approval                                                                                     |

### Brokers & Commission

| Method       | Path                                  | Notes                                         |
| ------------ | ------------------------------------- | --------------------------------------------- |
| GET/POST/PUT | `/brokers`                            | KYC and bank details (encrypted)              |
| GET/POST/PUT | `/commission-rules`                   | By project, broker category or salesperson    |
| GET          | `/commissions`                        | Accrued, approved, paid; `commission.view`    |
| POST         | `/commissions/payouts`                | Approval then voucher; paid statements locked |
| GET          | `/commissions/statements/{statement}` | JSON or PDF                                   |

### Accounts

| Method       | Path                                                  | Notes                                                                                                                                |
| ------------ | ----------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| GET/POST/PUT | `/accounts`, `/fiscal-years`, `/periods`              | Lock and unlock a period (approval for unlock)                                                                                       |
| POST         | `/vouchers`                                           | Receipt, payment, journal, contra; goes through `PostJournalEntryAction`                                                             |
| POST         | `/vouchers/{voucher}/reverse`                         | Reason and approval                                                                                                                  |
| GET/POST     | `/vendors`, `/bills`, `/expenses`, `/purchase-orders` | Simple procurement; project cost centre                                                                                              |
| GET          | `/reports/{report}`                                   | `trial-balance`, `ledger`, `income-statement`, `balance-sheet`, `cash-flow`, `receivables`, `payables`, `project-profit`, `day-book` |
| GET/POST     | `/bank-accounts`, `/bank-reconciliations`             | Manual matching                                                                                                                      |

### HR & Payroll

| Method       | Path                                          | Notes                                            |
| ------------ | --------------------------------------------- | ------------------------------------------------ |
| GET/POST/PUT | `/employees`, `/departments`, `/designations` | Payroll data needs `payroll.view`                |
| GET/POST     | `/attendance`, `/attendance/import`           | Manual, import and web check-in                  |
| GET/POST     | `/leave-types`, `/leave-requests`             | Approval engine                                  |
| POST         | `/payroll/runs`                               | Generate for a month; `Idempotency-Key` required |
| POST         | `/payroll/runs/{run}/approve` and `/post`     | Locks the month and posts the journal            |
| GET          | `/payslips/{payslip}`                         | JSON or PDF                                      |
| GET/POST     | `/job-posts`, `/applicants`                   | Recruitment                                      |

### Projects Costing & Construction

| Method       | Path                             | Notes                                      |
| ------------ | -------------------------------- | ------------------------------------------ |
| GET/POST/PUT | `/projects/{project}/budget`     | Categories; `projects.view-cost`           |
| GET          | `/projects/{project}/costing`    | Budget versus actual, cost per sq ft       |
| GET/POST/PUT | `/projects/{project}/milestones` | Percentage, notes, `customer_visible` flag |
| POST         | `/milestones/{milestone}/photos` | Dated photos                               |
| GET/POST     | `/work-orders`                   | Contractor work orders (basic)             |

### Documents, Tickets, Tasks & Approvals

| Method       | Path                                         | Notes                                                              |
| ------------ | -------------------------------------------- | ------------------------------------------------------------------ |
| GET/POST     | `/documents`                                 | Category, entity, visibility, expiry; new upload creates a version |
| GET          | `/documents/{document}/download`             | Signed, short-lived URL; access logged                             |
| GET/POST/PUT | `/document-templates`                        | Versioned merge templates                                          |
| POST         | `/documents/generate`                        | Template plus entity; stored with template version                 |
| GET/POST     | `/tickets`, `/tickets/{ticket}/replies`      | SLA, assignment, escalation, rating                                |
| GET/POST/PUT | `/tasks`                                     | Related entity, due date, priority                                 |
| GET/POST/PUT | `/approval-workflows`                        | Configuration                                                      |
| GET          | `/approvals/inbox`                           | Pending for the current user                                       |
| POST         | `/approvals/{request}/approve` and `/reject` | Comment required on reject                                         |

### Messaging

| Method       | Path                 | Notes                                                                      |
| ------------ | -------------------- | -------------------------------------------------------------------------- |
| GET/POST/PUT | `/message-templates` | Channel, language, placeholders, preview                                   |
| POST         | `/messages`          | Single send                                                                |
| POST         | `/message-campaigns` | Audience filter, **estimate first**, then send; `Idempotency-Key` required |
| GET          | `/message-logs`      | Status and cost                                                            |
| GET          | `/message-credits`   | Balance and ledger                                                         |

### Website Management

| Method       | Path                                                                        | Notes                                            |
| ------------ | --------------------------------------------------------------------------- | ------------------------------------------------ |
| GET/POST/PUT | `/website/pages`, `/posts`, `/menus`, `/sections`, `/banners`, `/redirects` | Bilingual fields                                 |
| POST         | `/website/publish`                                                          | Purges caches, updates sitemap                   |
| GET/PUT      | `/website/settings`                                                         | Branding, accent contrast check, tag manager IDs |
| POST         | `/website/domains` and `/domains/{domain}/verify`                           | DNS verification and TLS status                  |
| POST         | `/media`                                                                    | Upload into the processing queue                 |

### Dashboard & Reports

| Method | Path                           | Notes                                                                                                                                                              |
| ------ | ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| GET    | `/dashboard?from=&to=&branch=` | Funnel (definitions in `prd.md` section 5), collection, outstanding, expenses, profit, alerts                                                                      |
| GET    | `/reports/{report}`            | Sales, collection, outstanding aging, project performance, broker and team performance, lead source, cancellations; PDF and Excel export; long reports run as jobs |
| GET    | `/exports/{export}`            | Download a finished export                                                                                                                                         |

---

## 5. Customer & Broker Portal Endpoints

Tokens with ability `customer` or `broker`; everything is scoped to the signed-in person.

| Method   | Path                                                  | Notes                                         |
| -------- | ----------------------------------------------------- | --------------------------------------------- |
| GET      | `/portal/bookings` and `/portal/bookings/{booking}`   | Own bookings only                             |
| GET      | `/portal/bookings/{booking}/schedule` and `/receipts` | Schedule with paid and due; receipts list     |
| GET      | `/portal/statement`                                   | PDF through a signed URL                      |
| GET      | `/portal/documents`                                   | Only documents marked customer-visible        |
| GET      | `/portal/projects/{project}/progress`                 | Milestones and photos marked visible          |
| POST     | `/portal/payment-proofs`                              | Upload proof for verification                 |
| GET/POST | `/portal/tickets`                                     | Requests and complaints                       |
| GET      | `/portal/notices`                                     | Notices                                       |
| GET/POST | `/broker/leads`                                       | Brokers submit leads, see own referral status |
| GET      | `/broker/commissions`                                 | Own statements only                           |

---

## 6. Messaging Providers

- `MessageChannel` implementations: `SmsChannel` (BdBulkSMS), `EmailChannel` (Laravel Mail), `WhatsAppChannel` (WhatsApp Cloud API, pre-approved templates only). All sends are queued with retries. Credits are reserved and deducted in the same transaction that writes the message log; a failed send refunds the reservation. Message logs store template, channel, status and cost, not extra personal data. Sender ID and WhatsApp template approval depend on the provider and local rules; confirm with them.
- **Mobile banking receipts** (bKash, Nagad) are recorded with the TrxID and flagged `unverified` until the accountant matches them to the merchant statement. Direct payment gateway integration is out of scope for v1.
- Webhooks: provider delivery status and WhatsApp inbound status are public web routes, signature-verified, idempotent and processed in a queue.

---

## 7. Dashboards and Real-Time

There is no real-time server in v1. Dashboards refresh on load and every few minutes; the availability grid refreshes by light polling; approvals reach users through the in-app inbox, optional SMS, and later push notifications.
