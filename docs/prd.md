# PRODUCT REQUIREMENTS: DT Propix

> **This is the file that changes for each product.** Keep the same section headings.

---

## 1. Vision

**DT Propix**, a product of **Datatech BD Ltd**, is a cloud platform that lets a real-estate business run everything from one place: its public website, lead management, project and unit inventory, bookings and installments, collections, accounts, HR and payroll, customer service and a customer portal. **Run your entire real-estate business from one platform.**

It answers the questions every developer, agency and property company owner asks and cannot answer today:

- How many leads came in, from where, and how many became site visits, bookings and sales?
- Which units are available, on hold, booked or sold, right now?
- How much did we sell, how much did we collect, and who is overdue?
- What did each project cost, earn and leave as profit?
- What commission do we owe each broker and salesperson?
- Which customers are waiting for a document, a handover or a complaint reply?

The flow:

```text
Website / Ads / Broker -> Lead -> Site Visit -> Booking -> Sale (Agreement) -> Collection -> Handover
                                                      |                          |
                                                      +-> Commission             +-> Accounts -> Profit
```

Owner: **Dashboard -> Funnel -> Collection -> Outstanding -> Profit.**
Sales staff: **Lead -> Follow-up -> Visit -> Booking -> Receipt.**
Customer: **Website -> Enquiry -> Portal (schedule, receipts, documents, progress, requests).**

**Company:** Datatech BD Ltd, Dhaka.
**Target customers:** real-estate and property developers, real-estate agencies, property management companies (sales and administration; see section 9 for rental scope), land and project development businesses, and commercial property businesses.
**Languages:** English and Bangla (UI, website, documents, messages).
**Mobile app:** not in this project. The `/api/v1` is built so a separate mobile app project can use it later. Sales staff and customers use the responsive web app in the meantime.

---

## 2. Business Model & Deployment _(decision)_

**One installation per company.** Datatech copies this codebase, creates that company's database, and points a web address at it. The app does not provision companies, and it does not bill Datatech's fee. If the company has several branches, those branches live in that same codebase and that same database. Datatech charges the customer outside this app (one-time or yearly, decided per deal).

There is no `tenant_id`, no shared database, no subdomain tenant resolver, no platform console, no impersonation, no trial, and no subscription ledger.

> Branch isolation, export and security rules are in `architecture.md` sections 11 and 12.

Change this decision only if Datatech decides to build a shared SaaS; that changes the database design.

---

## 3. Roles

| Role               | Scope                             | Main needs                                                                  |
| ------------------ | --------------------------------- | --------------------------------------------------------------------------- |
| `owner`            | All branches                      | Dashboard, funnel, collection, outstanding, profit, approvals, settings     |
| `general-manager`  | All branches                      | Operations, approvals, reports, team performance                            |
| `sales-manager`    | Own team                          | Pipeline, targets, bookings, discounts within limit, site visits            |
| `sales-executive`  | Own leads and bookings            | Leads, follow-ups, site visits, hold and book units                         |
| `telecaller`       | Assigned leads                    | Call lists, log outcomes, schedule visits                                   |
| `broker`           | Own referrals only (portal login) | Submit leads, see referral status, commission statements                    |
| `sales-accountant` | Collections                       | Collection desk, receipts, cheques, customer statements                     |
| `accountant`       | Finance, all branches             | Journals, vouchers, bank and cash, reports, period close, commission payout |
| `project-manager`  | Assigned projects                 | Construction progress, costing, handover                                    |
| `hr-manager`       | HR                                | Employees, attendance, leave, payroll                                       |
| `customer-support` | Complaints and requests           | Tickets, document requests, messaging                                       |
| `web-editor`       | Website                           | Pages, projects, blog, careers, media, SEO                                  |

`super-admin` — hidden Datatech login on that server, seeded by `CompanySeeder` with a starter password that is changed at handover.
**Customers** log in to the portal with mobile OTP and see only their own data. **Brokers** log in to a limited broker portal; they never see other brokers' leads.
Unit prices, discounts, project costs, payroll and commission rates are visible only with their permissions (`units.view-price`, `bookings.view-discount`, `projects.view-cost`, `payroll.view`, `commission.view`).

---

## 4. Modules

### A. Company Profile & Setup

Company profile and logo, branches (with the head office marked), document number formats, payment plan templates, chart of accounts template; setup wizard with onboarding checklist; import wizard (dry run, row-level errors, safe re-run) for leads, customers, units with prices, bookings with payment schedules, and received payments from Excel; website theme, branding, menus and pages, publishing on the company's own domain; Datatech hands over a fresh installation (app and database, `owner` and hidden `super-admin` seeded with starter credentials that are changed at handover).

### B. Public Website

- **Pages:** home, about, projects (ongoing, upcoming, completed), project detail (gallery, video, floor plans, specifications, amenities, map, available units, brochure), property listings (for agencies), services, news and blog, careers with applications, testimonials, team, contact, privacy and terms, and custom pages.
- **Builder:** theme and branding (logo, accent, fonts), menus, sections with ordering, banners and sliders, bilingual content, per-page SEO fields, redirects, sitemap.
- **Lead capture:** enquiry, callback request, site visit request, brochure download (gated), and career application forms, all through one lead pipeline with anti-spam.
- **Media:** WebP pipeline, galleries, 360 or video embeds, public and gated PDFs.
- **Domains:** the company's own domain, with verification and automatic TLS.
- **Analytics hooks:** UTM capture, optional tag manager and pixel IDs.

### C. CRM & Lead Management

- **Leads** from website, phone, walk-in, ads (manual and CSV import), brokers and referrals, with source, campaign, project of interest, budget, and assignment rules (round robin or by project).
- **Duplicate handling:** merge by mobile; a repeat enquiry adds an activity instead of a new lead.
- **Pipeline stages:** New, Contacted, Site visit planned, Visited, Negotiation, Booked, Lost (with reason). Stages are configurable within these fixed outcomes.
- **Follow-ups** with due dates, reminders, and the "today" list; call, WhatsApp and SMS shortcuts; activity timeline.
- **Site visits:** schedule, vehicle or person, confirmation message, feedback after the visit, visit counted when marked completed.
- **Telecaller workflow:** call lists, outcomes, scheduling.
- **Inactive lead reassignment** and lead scoring by simple rules.
- **Campaign and source reports** with cost per lead when campaign cost is entered.

### D. Projects & Property Inventory

- **Projects:** type (apartment, commercial, plot, mixed), location and map, status, launch and handover dates, land owner details, description in two languages.
- **Structure:** blocks, floors, units (or plots); unit type, size, facing, view, bedrooms, bathrooms, parking, and attributes.
- **Pricing:** area times rate, premiums (floor, view, corner), parking, utility and other charges, registration estimate, offers; `PriceUnitAction` produces the breakdown; price lists with effective dates.
- **Unit status:** available, on hold, booked, sold, handed over, landowner share, blocked; every change goes through `ChangeUnitStatusAction` and writes history.
- **Landowner share:** units allocated to landowners under a joint venture; tracked separately and excluded from sale totals unless sold.
- **Availability grid** (stacking plan) and plot list/map views; documents per project and unit (floor plans, approvals).
- **Bulk tools:** import units, bulk price change with approval, block or release.
- **Brokers' view** shows only what the owner allows.

### E. Booking & Sales

- **Customers** with joint buyers, nominee, photos, KYC documents, and duplicate detection.
- **Hold/reservation** with expiry and an optional token amount.
- **Booking** flow: customer, unit, price snapshot, discount (limit and approval), booking money, **payment schedule** from a template or manual lines.
- **Agreement:** generated from a template, signed copy uploaded; once signed, the schedule is locked and the booking becomes a **sale**.
- **Modifications:** schedule revision (versioned, approval), waivers, unit change, name change, **transfer** to a new customer with a fee, **cancellation** with deduction rules, and **refund** schedule.
- **Handover:** checklist, possession letter, and status change to handed over.
- **Documents:** booking form, allotment letter, agreement, demand letter, no-objection and others from templates.

### F. Collections, Receipts & Outstanding

- **Collection desk** with allocation to schedule lines and advance handling.
- **Methods:** cash, cheque (with status flow), bank transfer, mobile banking with TrxID, card or POS.
- **Receipts** printed and sent by SMS, WhatsApp or email; reversal with approval.
- **Late charges** (optional) with waiver approval.
- **Due reminders** before and after the due date by SMS, WhatsApp or email.
- **Outstanding and aging** by customer, project and salesperson; demand notices; customer statements; follow-up notes and promises to pay.
- **Customer payment proofs** submitted from the portal are verified by the accountant before they become receipts.

### G. Brokers, Agents & Commission

- Broker and agent profiles with KYC, agreement, and bank details (encrypted).
- Referral tracking: a lead and booking carry the broker; conflicts between brokers are resolved by the first-registered rule with manager override.
- **Commission rules** per project, broker category or salesperson: percentage or fixed, on net price or on collected amount, with stages (for example part when the down payment completes, the rest as installments are collected).
- **Accrual, reversal on cancellation, approval and payment** by voucher; commission statements (broker and employee) with print and message.
- Broker portal for leads submitted, status, and statements.

### H. Accounts

- **Chart of accounts** with templates; fiscal years and monthly periods with locking.
- **Vouchers:** receipt, payment, journal, contra; bank and cash books; bank reconciliation (manual matching in v1).
- **Cost centers:** project (and optional building) on income and expense lines.
- **Automatic postings** from receipts, refunds, commissions, payroll, expenses and purchases.
- **Expenses, vendors/suppliers and bills,** payments and payables aging; simple purchase orders for project materials.
- **Reports:** trial balance, ledger, income statement, balance sheet, cash flow summary, receivables, payables, project-wise profitability, and day book.
- **Taxes:** VAT/TDS fields are configurable and off by default; confirm applicable rules with the client's accountant.

### I. HR & Payroll

- Employees, departments, designations, documents, and contracts.
- Attendance (manual entry, import from a device file, and web check-in option), shifts, holidays, leave types, requests and balances.
- **Payroll:** salary structures, allowances and deductions, advances and loans, monthly generation, approval, lock, payslips, and journal posting; salary sheet for bank transfer.
- Recruitment: job posts (from the website careers), applicants, interview notes, and hire.
- Sales targets and incentives for sales staff linked to bookings.

### J. Project Costing & Construction Progress

- **Project budget** by category; actual costs from bills, expenses and payroll allocation; budget versus actual; cost per sq ft; project profitability.
- **Construction progress:** milestones/stages per project or building, percentage complete, dated photo updates, notes; a customer-visible flag so progress appears on the portal.
- Contractor/vendor work orders and bills (basic); material purchase requests through the approval engine.

### K. Document Management

- Central library by category and entity (customer, booking, unit, project, land parcel, employee, vendor), versions, expiry dates with alerts, tags, search, and visibility (internal or customer-visible).
- Land and legal document register per project (deeds, mutation, approvals) with expiry or renewal alerts.
- Generated documents from versioned templates; private storage and signed links.

### L. Complaints, Support & Requests

- Tickets from the portal, phone, or staff entry with category, priority, SLA due time, assignment, status flow, internal notes and customer replies.
- Requests: document copy, statement, name change, schedule inquiry, handover appointment.
- Escalation on SLA breach; satisfaction rating on close; ticket reports.

### M. Communication

- SMS (BdBulkSMS), email and WhatsApp Cloud API through `MessageChannel`; templates by channel and language; campaigns to a filtered audience with a cost estimate first; automatic messages (receipt, due reminder, visit reminder, document ready, ticket updates); opt-out; delivery logs; prepaid credits.

### N. Tasks & Approval Workflows

- **Tasks:** assignee, due date, priority, status, related entity; "my tasks" and team views.
- **Approval engine:** configurable workflows for discount, cancellation, refund, transfer, price change, schedule change, expense, purchase, leave, payroll, and commission payout; inbox, reminders, escalation, full audit trail.

### O. Customer Portal

Login by mobile OTP; my bookings and units; payment schedule, receipts, statements, due reminders; documents marked visible; construction progress; submit payment proof; raise requests and complaints; notices. Online gateway payment is a later module (section 9).

### P. Management Dashboard & Reports

The dashboard shows the funnel in section 5 with trends and drill-down, plus collection, outstanding aging, project performance, sales team and broker leaderboards, cash and bank position, upcoming installments, expiring holds, approvals waiting, and alerts. Reports with PDF and Excel export follow `design.md` section 17.

### Q. Data Import, Onboarding & API

Import wizards (dry run, row errors, safe re-run), onboarding checklist and demo data, and a documented `/api/v1` ready for a future mobile app.

---

## 5. Business Rules & Funnel Definitions

### Funnel definitions _(fixed; every screen and report must use these)_

| Metric          | Definition                                                                                                                                                                                                                                                                                             |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Leads**       | Count of distinct leads created in the period (duplicates merged by mobile are one lead).                                                                                                                                                                                                              |
| **Site visits** | Count of site visits marked **completed** in the period (scheduled but not completed visits are shown separately).                                                                                                                                                                                     |
| **Bookings**    | Count of bookings **confirmed** in the period (hold only is not a booking; cancelled bookings are subtracted in the period they are cancelled and shown as a separate line).                                                                                                                           |
| **Sales**       | Total net price (after discount, excluding registration and pass-through charges) of bookings that have reached **agreement signed** in the period. Count and value are both shown.                                                                                                                    |
| **Collection**  | Sum of **verified, non-reversed** receipts dated in the period; a cheque counts when it is **cleared**; refunds are shown as a separate negative line.                                                                                                                                                 |
| **Outstanding** | At the as-of date, the total of schedule rows due on or before that date minus allocated collections (this is **overdue**), and separately the total of all future rows (this is **upcoming**). Outstanding on the dashboard = overdue plus due today.                                                 |
| **Expenses**    | Posted expense and payment vouchers in the period (operating and project costs), excluding transfers, refunds and customer receipts.                                                                                                                                                                   |
| **Profit**      | Income statement result for the period from posted journals: recognized revenue minus expenses (revenue recognition basis is a company setting: on **collection** by default, or on **sale**; confirm the treatment with the client's accountant). Project profit uses the same method per cost center. |

Conversion percentages shown between funnel steps are lead to visit, visit to booking, booking to sale. Dashboard totals must equal report totals and ledger totals exactly.

### Rules

| Topic                       | Rule                                                                                                                                                                                                                                                                                                             |
| --------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Unit status**             | Changes only through `ChangeUnitStatusAction` with a history row. Allowed paths: available to on hold to booked to sold to handed over; on hold returns to available on expiry or release; booked or sold returns to available on approved cancellation; landowner share and blocked are set by permitted users. |
| **Double booking**          | One active booking per unit, enforced by a row lock and a database constraint. A second attempt gets a clear message and the current status.                                                                                                                                                                     |
| **Holds**                   | A hold expires after a configurable time (default 48 hours; a token payment can extend). Expiry releases the unit and notifies the salesperson.                                                                                                                                                                  |
| **Pricing**                 | `PriceUnitAction` computes area times rate plus charges and offers. A booking stores a full snapshot; later price changes never alter it. Price changes need `units.change-price` and, above a threshold, approval.                                                                                              |
| **Discount**                | Limited by role; above the limit an approval is required. The reason is mandatory. Visible only with `bookings.view-discount`.                                                                                                                                                                                   |
| **Payment schedule**        | The sum of rows must equal the net price plus agreed charges. After the agreement is signed, changes create a new version through approval.                                                                                                                                                                      |
| **Allocation**              | Receipts allocate to the earliest due rows by default, or as chosen. Overpayment becomes an advance. Due amounts are calculated from allocations, never typed.                                                                                                                                                   |
| **Late charges**            | Optional; apply after a grace period at a configured rate; waivable only through approval.                                                                                                                                                                                                                       |
| **Receipts**                | Immutable once posted. Corrections are reversals with a reason and approval. A cheque receipt is provisional until cleared; a bounce reverses it and records a charge.                                                                                                                                           |
| **Cancellation and refund** | The deduction rule (percentage or fixed, by stage) applies automatically; the refund cannot exceed collected minus deductions and follows an approval and payment schedule. The unit returns to available only after approval.                                                                                   |
| **Transfer**                | Moves the booking to a new customer with a fee and approval; the old customer's history is kept.                                                                                                                                                                                                                 |
| **Commission**              | Calculated from rules at the stages defined by the company; reversed (or clawed back through a deduction) on cancellation; paid only after approval; a paid statement is locked. A broker's commission is never visible to other brokers.                                                                         |
| **Accounting**              | All postings go through `PostJournalEntryAction`; journals balance; posted entries are immutable; locked periods reject postings.                                                                                                                                                                                |
| **Documents**               | Numbers are sequential per series without gaps; voided numbers remain visible. Generated documents are stored with the template version.                                                                                                                                                                         |
| **Approvals**               | One engine; a blocked action proceeds only after approval; every decision is logged.                                                                                                                                                                                                                             |
| **Leads**                   | Duplicates merge by mobile; a lost lead needs a reason; reassignment rules are configurable; a converted lead keeps its history on the customer.                                                                                                                                                                 |
| **Website data**            | Only publishable data appears. Booked or sold units are shown according to the project's setting; a status change purges caches within a minute.                                                                                                                                                                 |
| **Customer privacy**        | Customer data is never shown on the public site; portal users see only their own data; exports are permission-protected and logged.                                                                                                                                                                              |
| **Messaging**               | Non-essential messages respect opt-out; credits are deducted in the same transaction as the log; bulk sends show segments and cost first.                                                                                                                                                                        |
| **Records**                 | No hard deletes of leads with activity, customers, bookings, payments, journals or documents.                                                                                                                                                                                                                    |
| **Branch isolation**        | Non-owner users see only their assigned branches; another branch returns 404. Tested for every model and endpoint.                                                                                                                                                                                                |
| **Regulatory**              | Registration, stamp duty, VAT, referral/commission and data protection rules must be confirmed with a lawyer and accountant before the client uses the forms and templates. Templates are editable and carry no legal guarantee.                                                                                 |

---

## 6. Hardware

- Desktop or laptop for the back office; phones for sales staff (responsive web).
- Optional: A4 laser printer for documents, 80mm thermal printer for receipts, camera or scanner for document uploads.
- Website visitors use any modern browser; the site targets mid-range phones on 4G.

---

## 7. Non-Functional Requirements

- **Localization:** UI, website, documents and messages in `en` and `bn`.
- **Performance:** dashboard under 3 seconds for a company with 20,000 leads, 5,000 units and 3 years of data; availability grid under 1 second for 1,000 units; booking save under 1 second; public pages LCP under 2.5 seconds on mid-range phones; cached public pages served in under 200 ms.
- **Scale targets:** a company with 50,000 leads, 10,000 units, 5,000 bookings and 100,000 journal lines remains responsive using indexes and aggregated tables.
- **Security:** HTTPS only, encrypted sensitive fields, private file storage with signed URLs, access logs for exports and document views, rate limits, idle timeout, encrypted backups, branch isolation.
- **Audit:** activity log for price changes, discounts, status changes, cancellations, refunds, transfers, receipt reversals, cheque changes, approvals, role changes, and exports.
- **SEO and accessibility:** Lighthouse SEO and accessibility scores of 95 and 90 or more on public pages.
- **Availability:** target 99.5% monthly; planned maintenance outside working hours.
- **Browsers:** latest Chrome, Edge, Safari and Firefox for the public site; latest Chrome and Edge for the back office.

---

## 8. Screen List (per role)

| Role                | Screens                                                                                                               |
| ------------------- | --------------------------------------------------------------------------------------------------------------------- |
| owner               | Dashboard and funnel, collection, outstanding and aging, project performance, profit, approvals, settings, access log |
| general-manager     | Operations dashboard, approvals, bookings, reports, team performance                                                  |
| sales-manager       | Pipeline, team follow-ups, targets, bookings, discount approvals, site visit calendar                                 |
| sales-executive     | Today's follow-ups, my leads, lead 360, site visits, availability grid, booking desk                                  |
| telecaller          | Call list, quick lead add, outcomes                                                                                   |
| broker (portal)     | My leads, submit lead, referral status, commission statements                                                         |
| sales-accountant    | Collection desk, receipts, cheque register, statements, demand notices                                                |
| accountant          | Vouchers, journals, bank and cash books, reports, period close, commission payout                                     |
| project-manager     | Project costing, construction progress, handover, vendors                                                             |
| hr-manager          | Employees, attendance, leave, payroll, recruitment                                                                    |
| customer-support    | Tickets, requests, document requests, messages                                                                        |
| web-editor          | Pages, projects, blog, careers, media, SEO, redirects                                                                 |
| customer (portal)   | Home, payments, documents, construction progress, requests, notices                                                   |

---

## 9. Out of Scope

- Native mobile apps (separate project; this project provides the API).
- Online gateway payments (bKash, Nagad, cards) for installments; payment proof upload with verification is included. Gateway integration is a later module.
- Electronic signatures; signed agreements are uploaded scans.
- **Rental and facility management** (leases, rent invoices, maintenance contracts, service charges). The package targets sales; a property-management module is a later pack and should be confirmed with prospects who manage rented property.
- Full ERP procurement, inventory and manufacturing; only simple project purchases and bills are included.
- Government registration or e-filing integrations.
- Advanced BIM, drone, or 3D walkthrough tools (video and 360 embeds are supported).
- Deep payroll tax compliance and statutory filing.
- Marketplace portal combining multiple companies' listings.
- Multi-tenant SaaS, a shared database, a platform console, subscription billing, trials, plan limits, impersonation.

---

## 10. Key User Stories & Acceptance Criteria

1. **Website enquiry.** _As a visitor, I ask about a project._
    - Done when: the form creates one lead (or adds an activity to an existing one by mobile), the assigned salesperson is notified, spam is blocked, and the visitor sees a clear success message.
2. **Follow-up.** _As a sales executive, I work my day._
    - Done when: the today list shows overdue and due follow-ups, logging an outcome takes three taps, and the next follow-up is scheduled.
3. **Site visit.** _As a sales manager, I plan visits._
    - Done when: a visit is scheduled with confirmation to the customer, conflicts are warned, feedback is captured, and completed visits count in the funnel.
4. **Hold a unit.** _As a salesperson, I reserve a unit._
    - Done when: the unit shows on hold with a countdown, nobody else can book it, and it releases automatically on expiry.
5. **Booking.** _As a sales executive, I book a unit._
    - Done when: the price is a snapshot, the discount follows limits and approval, the payment schedule sums correctly, the booking form prints, and two simultaneous attempts on the same unit result in one booking (tested).
6. **Agreement.** _As a manager, I convert a booking to a sale._
    - Done when: the agreement generates from the template, the signed scan is uploaded, the schedule locks, and the sale counts in the funnel.
7. **Collection.** _As a sales accountant, I collect an installment._
    - Done when: the amount is allocated to the earliest due rows, advances and late charges are handled, the receipt prints and is sent, the cheque flow works, and the customer's outstanding updates.
8. **Due reminders.** _As an owner, my customers pay on time._
    - Done when: reminders go before and after due dates, logs show delivery and cost, and opt-outs are respected.
9. **Cancellation and refund.** _As a manager, I cancel a booking._
    - Done when: deduction rules apply, approvals are required, the refund schedule is created, commission reverses, the unit is released after approval, and journals balance.
10. **Transfer.** _As a manager, I transfer a booking._
    - Done when: the new customer holds the booking, the fee is posted, history is preserved, and approvals are logged.
11. **Broker commission.** _As an accountant, I pay a broker._
    - Done when: commission accrues by rule, the statement matches collections, payment needs approval and posts a voucher, a paid statement is locked, and a cancellation reverses it.
12. **Accounts.** _As an accountant, my books are right._
    - Done when: all postings are balanced, periods can be locked, the trial balance equals the ledgers, and project profit matches journals.
13. **Project costing.** _As a project manager, I track cost._
    - Done when: budget versus actual by category is shown, costs come from bills and expenses, and cost per sq ft and profit are available to permitted users.
14. **Construction progress.** _As a project manager, I update progress._
    - Done when: milestones with percentage and photos are saved, and customer-visible updates appear in the portal.
15. **HR and payroll.** _As an HR manager, I run payroll._
    - Done when: attendance and leave feed the salary sheet, approval locks the month, payslips print, and the journal posts.
16. **Documents.** _As staff, I find any document._
    - Done when: documents are searchable by customer, unit and project, versions are kept, expiry alerts fire, and customer-visible documents appear in the portal.
17. **Complaints.** _As a customer, I raise a complaint._
    - Done when: the ticket is created with an SLA, assigned, updated with messages, escalated on breach, and rated at close.
18. **Customer portal.** _As a customer, I see my account._
    - Done when: schedule, receipts, documents and progress show only my data (404 for anything else), and a payment proof reaches the accountant for verification.
19. **Website editing.** _As a web editor, I publish a project page._
    - Done when: bilingual content, gallery, SEO fields and brochure gate are saved, the page is live within a minute, and the sitemap updates.
20. **Dashboard.** _As an owner, my numbers agree._
    - Done when: the funnel and all KPIs equal the reports and ledgers (tested), and nightly reconciliation shows no differences.
21. **Import.** _As Datatech support, I digitize a company in a day._
    - Done when: a dry run reports row errors, the real import is repeatable without duplicates, and opening balances appear in the customer ledger.
22. **Branch isolation.** _As a company, my data is never visible to another branch._
    - Done when: automated tests prove that every model and endpoint returns 404 across branches, including the public website and portal.

---

## 11. Phasing

See `roadmap.md`. Build phase by phase; do not implement later phases early. The first sellable release is Phases 0 to 7 (website, CRM, inventory, booking, collections, dashboard); the rest are later modules.
