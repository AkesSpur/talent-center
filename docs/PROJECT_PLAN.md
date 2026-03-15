# Talent Center — 4-Stage Project Plan

**Total Budget:** 120,000 RUB (30,000 per stage)
**Stack:** Laravel 12 + Blade + Tailwind CSS + MySQL
**Timeline:** 4 stages, sequential delivery

---

## Stage 1: Infrastructure & Database Foundation — 30,000 RUB

### Status: LARGELY COMPLETE

### Objective
Set up the full development environment, database architecture, authentication system, and RBAC foundation that all future stages build on.

### What's Been Built
- [x] Laravel 12 project initialized with MySQL
- [x] `.env` configured (local dev)
- [x] **10 migrations** — users (with parent_id self-ref, role, is_blocked), organizations (with verification), organization_user pivot (granular permissions), contests, contest_categories, applications, diplomas, action_logs (json)
- [x] **7 Eloquent models** — User, Organization, Contest, ContestCategory, Application, Diploma, ActionLog — all with relationships, casts, helper methods
- [x] **5 PHP enums** — UserRole, OrganizationStatus, ContestStatus, ApplicationStatus, FileType
- [x] **3 custom middleware** — `role:admin`, `org.permission:create`, `verified.org`
- [x] **4 authorization policies** — UserPolicy, OrganizationPolicy, ContestPolicy, ApplicationPolicy
- [x] **Auth scaffolding (Breeze Blade)** — registration with first_name/last_name/patronymic, role-based login redirect, email verification, password reset
- [x] **Profile editing** — updated form with first_name, last_name, patronymic, phone, email
- [x] **3 dashboard stubs** — admin (with stat cards), support (with pending orgs count), participant (welcome message)
- [x] **Role-aware navigation** — colored role badge, different nav links per role
- [x] **Flash message component** — success/error/warning
- [x] **ActionLogService** — static logger with polymorphic targets
- [x] **Seeders** — AdminSeeder + TestDataSeeder (admin, support, parent with 2 children, participant as org admin, 2 orgs)
- [x] Storage link (`php artisan storage:link`)
- [x] Migrations run + rollback verified clean

### What Remains
- [ ] Git init + initial commit
- [ ] Production server setup on Reg.ru + ispmanager (client handles)
- [ ] Review & edge-case testing of auth flows

### Test Accounts (all password: `password`)
| Role | Email |
|------|-------|
| Admin | admin@talentcenter.ru |
| Support | support@talentcenter.ru |
| Parent (2 children) | parent@example.com |
| Participant + Org Admin | participant@example.com |

---

## Stage 2: Dashboards, Profiles & Organizations — 30,000 RUB

### Status: COMPLETE

### Objective
Build the real dashboards for every role, the parent-child participant management UI, organization CRUD with verification workflow, and admin/support management panels. After this stage, users can fully manage their accounts, create organizations, and admins can govern the platform.

### Deliverables

#### 2.1 Participant Dashboard (Full)
- Personal info summary card
- List of child participants (if any)
- "My Organizations" list (orgs where user is a representative)
- "My Applications" placeholder (wired up in Stage 3)
- "My Diplomas" placeholder (wired up in Stage 4)

#### 2.2 Parent-Child Participant Management
- **Create child participant** — form with first_name, last_name, patronymic, email
- **Edit child profile** — parent can update child's info
- **Switch context** — parent can submit on behalf of a child (tracked via `user_id` on the application)
- **List children** — table/card view under parent dashboard

#### 2.3 Organization CRUD
- **Create organization** — form: name, description, INN, legal address, contact email/phone
- **Business rule:** Creator auto-gets all 3 permissions (can_create, can_manage, can_evaluate)
- **Organization detail page** — shows status, info, list of representatives
- **Edit organization** — only users with `can_manage` or admin/support
- **Manage representatives** — add/remove users, set granular permissions (create/manage/evaluate)
- **Organization status badge** — "Pending" / "Verified" shown on all org pages

#### 2.4 Organization Verification Workflow
- **Admin panel:** List all organizations, filter by status (pending/verified)
- **Admin/support action:** "Verify" button sets status to verified, records `verified_by` and `verified_at`
- **Rejection:** admin can leave org as pending (no explicit reject status in MVP)
- Verification gate prevents contest creation for unverified orgs (middleware already exists)

#### 2.5 Admin Dashboard (Full)
- Stats: total users, organizations (pending/verified split), contests, applications
- **User management:** List, search, filter by role. View/edit any user. Block/unblock users.
- **Organization management:** List, search, filter by status. Verify/view/edit orgs.
- **Action logs viewer:** searchable table of action_logs with user, action, target, timestamp

#### 2.6 Support Dashboard (Full)
- Stats: pending orgs, total users, recent applications
- **User management:** Same as admin but no role assignment, no delete
- **Organization management:** List, verify, view. No delete.
- **Application review:** read-only list (full editing in Stage 4)

#### 2.7 Form Requests
- `StoreOrganizationRequest`
- `UpdateOrganizationRequest`
- `StoreChildParticipantRequest`
- `UpdateUserRequest` (admin)

#### 2.8 Action Logging
- Log: user creation, org creation, org verification, profile updates, user block/unblock
- All via ActionLogService

### Routes Added
```
/dashboard                           — participant dashboard (enhanced)
/dashboard/children                  — list children
/dashboard/children/create           — create child form
/dashboard/children/{user}/edit      — edit child

/organizations                       — list user's organizations
/organizations/create                — create org form
/organizations/{organization}        — org detail
/organizations/{organization}/edit   — edit org
/organizations/{organization}/representatives — manage reps

/admin/dashboard                     — admin dashboard (enhanced)
/admin/users                         — user CRUD
/admin/organizations                 — org management
/admin/action-logs                   — log viewer

/support/dashboard                   — support dashboard (enhanced)
/support/users                       — user list/edit
/support/organizations               — org list/verify
```

---

## Stage 3: Contests & Application Submission — 30,000 RUB

### Status: COMPLETE

### Objective
Build the full contest lifecycle — creation, browsing, and application submission with file upload. Implement automated status transitions via a scheduled command. After this stage, the core competition flow works end-to-end (minus evaluation).

### Deliverables

#### 3.1 Contest CRUD
- **Create contest** — form: title, description, rules, dates (start, app deadline, results date), categories (add/remove), diploma background upload
- **Gate:** Only users with `can_create` on a **verified** org can create contests
- **Edit contest** — editable while in `pending` or `accepting` status only
- **Cancel contest** — soft cancel; accessible to org reps with `can_create` or `can_manage`, or admin
- **Contest detail page** — public info, categories, status badge, dates, org name, status-aware apply card

#### 3.2 Contest Category Management
- Add/remove categories during contest creation and editing
- Each category: name + description
- Dynamic form rows powered by Alpine.js; `Js::from()` used for safe PHP→JS state passing

#### 3.3 Contest Listing & Browsing
- **Public contest list** — all non-draft/non-cancelled contests; searchable by title, filterable by status tabs
- **Org contest section** — organization detail page shows up to 5 recent contests for the org
- **Status filter tabs:** Все / Приём заявок / Ожидает / Оценка / Архив

#### 3.4 Contest Status State Machine

Status is **automatically computed on creation** from the contest's dates vs. current date — there is no manual "Publish" step. The machine has 6 states:

| Status | Value | Description |
|--------|-------|-------------|
| Черновик | `draft` | Transient state only during `store()` before `determineCurrentStatus()` is called |
| Ожидает начала приёма | `pending` | Created; `applications_start_at` is in the future |
| Приём заявок | `accepting` | Today is between `applications_start_at` and `applications_end_at` |
| Оценка заявок | `evaluation` | `applications_end_at` has passed; before `evaluation_end_at` |
| Архив | `archive` | `evaluation_end_at` has passed; auto-archived |
| Отменён | `cancelled` | Manual cancel by authorized user |

**Transitions (scheduled):** `php artisan contests:transition` runs every minute via cron:
- `pending` → `accepting` when `applications_start_at ≤ now`
- `accepting` → `evaluation` when `applications_end_at < now`
- `evaluation` → `archive` when `evaluation_end_at < now`

Each transition is logged via `ActionLogService` (requires `action_logs.user_id` to be nullable since `Auth::id()` returns null during cron).

**Note:** The `evaluation_end_at` column is labeled **"Дата публикации результатов"** in the UI.

#### 3.5 Application Submission
- **Submit application form:** select category (optional), attach file OR paste cloud link
- **File upload:** max 1 file, max 4MB, types: jpg/jpeg/png/gif/pdf/doc/docx (stored as-is, not converted to WebP, to preserve document integrity)
- **Cloud link:** alternative to file upload — paste link to Google Drive, Yandex Disk, etc.
- **Validation:** cannot submit if contest is not in `accepting` status (re-checked server-side in `store()` to guard race conditions)
- **Duplicate guard:** one application per `user_id` per contest. A parent submitting for themselves AND for a child creates two separate, valid applications (one per physical user). The "submit as" dropdown excludes users who have already applied.
- **Ties:** multiple users can hold the same rank — this is handled in Stage 4 evaluation with no unique constraint needed here
- **Application status:** starts as `new`
- **My applications page:** participant + their children's applications with status badges, file/link column

#### 3.6 Application for Child Participants
- Parent can submit application on behalf of any of their children
- "Submit as" dropdown shows parent + each child, excluding any who have already applied to that contest
- Application `user_id` set to the selected child's ID (or parent's if self-submitting)

#### 3.7 Org Application Viewer
- Org reps with `can_manage` OR `can_evaluate` see all applications for their org's contests
- Table: applicant name, contest, category, status badge, date, file link
- Filterable by contest (dropdown) and status (dropdown)
- OR logic handled in controller (middleware only supports AND)

#### 3.8 Form Requests
- `StoreContestRequest` — title, description, rules, 3 date fields (after: constraints), diploma_background image, categories array
- `UpdateContestRequest` — same as Store plus `delete_diploma_background` boolean
- `StoreApplicationRequest` — contest_id, category_id, submitted_for_user_id, file, external_link; custom `withValidator()` enforces at least one of file/link

### Routes Added
```
/contests                                        — public contest listing
/contests/{contest}                              — contest detail

/organizations/{org}/contests/create             — create contest form
/organizations/{org}/contests                    — store contest (POST)
/organizations/{org}/contests/{contest}/edit     — edit contest form
/organizations/{org}/contests/{contest}          — update contest (PUT)
/organizations/{org}/contests/{contest}/cancel   — cancel contest (POST)

/contests/{contest}/apply                        — submit application (GET + POST)
/dashboard/applications                          — my applications list
/organizations/{org}/applications                — org's applications viewer
```

### Scheduled Commands
```
contests:transition — runs every minute; transitions pending→accepting→evaluation→archive
```

### Key Files
```
app/Enums/ContestStatus.php                      — 6-status enum with label(), color(), canEdit(), canCancel()
app/Enums/ApplicationStatus.php                  — label() + color() added
app/Models/Contest.php                           — determineCurrentStatus() + status helper booleans
app/Policies/ContestPolicy.php                   — updated view/update + new cancel()
app/Http/Controllers/ContestController.php       — full CRUD + cancel
app/Http/Controllers/ApplicationController.php   — create/store/myIndex/orgIndex
app/Console/Commands/TransitionContestStatuses.php
resources/views/contests/{index,show,create,edit}.blade.php
resources/views/applications/{create,index}.blade.php
resources/views/organizations/applications.blade.php
database/migrations/2026_02_23_085255_make_action_logs_user_id_nullable.php
```

---

## Stage 4: Evaluation, Diplomas & Notifications — 30,000 RUB

### Status: LARGELY COMPLETE

### Objective
Build the jury evaluation interface, automatic PDF diploma generation, email notification system, and do final testing/polishing. After this stage, the full platform flow works: create contest -> submit -> evaluate -> diplomas -> notifications.

### Deliverables

#### 4.1 Evaluation Interface
- [x] **Jury dashboard:** list of contests in `evaluation` status for the evaluator's org
- [x] **Evaluation page:** for each contest, show all applications grouped by category
- [x] **Evaluate an application:** assign one of: Participant (just participated), 1st Place, 2nd Place, 3rd Place, Rejected (must provide reason)
- [x] **Editable until finalized:** evaluator can change assessment until "Finish Evaluation" is clicked
- [x] **Ties allowed:** multiple applications can get the same place
- [x] **Status indicators:** color-coded badges per application (new=gray, placed=gold/silver/bronze, rejected=red)

#### 4.2 Finalization Gate
- [x] "Finish Evaluation" button per contest
- [x] **Enabled only when:** all applications evaluated (none with `new` status)
- [x] **On click:** contest -> `archive`, diplomas generated, notifications sent
- [x] **Irreversible:** archived contests cannot be reopened in MVP

#### 4.3 PDF Diploma Generation
- [x] **Library:** barryvdh/laravel-dompdf installed and configured
- [x] **Diploma contents:** organization name, participant full name, contest title, category, place, date, QR code
- [x] **Background:** uses `diploma_background` image uploaded during contest creation
- [x] **Final diploma:** generated automatically when place is assigned / contest finalized
- [x] **Storage:** saved to `storage/app/public/diplomas/`, record in `diplomas` table with `diploma_number`
- [x] **Download:** participant can download from their dashboard
- [x] **Verification:** public QR code + `/diplomvtrifi` search page to verify diploma authenticity

#### 4.4 Email Notifications
- [x] Application submitted (to applicant)
- [x] Contest finalized (to all applicants)
- [x] Place awarded / diploma ready (to applicant — include diploma link)
- [x] Application rejected (to applicant — include reason)
- [x] **Implementation:** Laravel Mailables
- [x] **Opt-out:** users.email_notifications flag — if false, skip all emails
- [x] **Templates:** branded Blade email templates

#### 4.5 Admin Tools (Final)
- [x] Admin can re-trigger diploma generation for a contest
- [x] Admin can override/correct evaluation results
- [ ] Admin diploma template management UI (upload background images per contest — handled via contest edit form)

#### 4.6 Final Testing & Polish
- [ ] Test complete flow: register -> create org -> verify -> create contest -> publish -> submit application -> evaluate -> finalize -> diplomas -> notifications
- [ ] Test parent-child flow end to end
- [ ] Test role access controls (participant can't access admin, etc.)
- [ ] Test edge cases: expired contests, blocked users, unverified orgs
- [ ] Responsive testing on mobile widths
- [ ] Fix any Blade rendering issues, flash messages, validation errors
- [ ] Performance check on seeded data

### Routes Added
```
/organizations/{org}/contests/{contest}/evaluate — evaluation page
/organizations/{org}/contests/{contest}/finalize — finalize action

/dashboard/diplomas                  — my diplomas list
/diplomas/{diploma}/download         — download PDF

/admin/contests/{contest}/evaluate   — admin override evaluation
/admin/diploma-templates             — manage templates
```

### Packages to Install
```
barryvdh/laravel-dompdf   — PDF generation
```

### Scheduled Commands (already from Stage 3)
```
contests:transition — also logs notifications when status changes
```

---

## Cross-Stage Dependencies

```
Stage 1 ─── foundation for everything
  │
  ├── Stage 2 ─── dashboards, profiles, orgs
  │     │
  │     └── Stage 3 ─── contests, applications
  │           │
  │           └── Stage 4 ─── evaluation, diplomas, emails, testing
```

Each stage **requires** the previous stage to be complete. No parallel development between stages.

---

## Risk Registry

| Risk | Impact | Mitigation |
|------|--------|------------|
| MySQL not available on Reg.ru plan | Blocks everything | Verify hosting plan supports MySQL before deployment |
| File upload >4MB edge cases | Broken submissions | Validate both client-side and server-side, clear error messages |
| Cron not available on hosting | Contest auto-transition fails | Use `supervisor` or hosting's scheduled task feature |
| PDF generation slow | Timeouts on finalization | Queue diploma generation as background jobs |
| Email deliverability | Notifications go to spam | Configure SPF/DKIM/DMARC on domain, use reputable SMTP (Mailgun, etc.) |
| Parent submitting for wrong child | Data integrity | Validate parent_id ownership before allowing "submit as" |
                  