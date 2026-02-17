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

### Status: NOT STARTED

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

### Status: NOT STARTED

### Objective
Build the full contest lifecycle — creation, publishing, browsing, and application submission with file upload. Implement the automated status transitions (accepting -> evaluation). After this stage, the core competition flow works end-to-end (minus evaluation).

### Deliverables

#### 3.1 Contest CRUD
- **Create contest** — form: title, description, rules, dates (start, app deadline, eval deadline), categories (add/remove), diploma background upload
- **Gate:** Only users with `can_create` on a **verified** org can create contests
- **Edit contest** — editable while in `draft` or `accepting` status (not evaluation/archive)
- **Delete contest** — admin only, soft or hard delete
- **Contest detail page** — public info, categories, status badge, dates, org name

#### 3.2 Contest Category Management
- Add/remove categories during contest creation and editing
- Each category: name + description
- Dynamic form (add/remove rows with JS)

#### 3.3 Contest Listing & Browsing
- **Public contest list** — all contests with status `accepting` (filterable, searchable)
- **Org contest list** — all contests for an org (visible to org representatives)
- **Contest card component** — reusable Blade component: title, org, dates, status, category count
- **Status filter:** accepting / evaluation / archive

#### 3.4 Contest Status State Machine
- `draft` -> `accepting`: manual button "Publish" (by org rep with `can_create`)
- `accepting` -> `evaluation`: **automatic** via scheduled command when `applications_end_at` passes
- Laravel scheduled command: `php artisan contests:transition` runs every minute via cron
- Log status transitions via ActionLogService

#### 3.5 Application Submission
- **Submit application form:** select category (optional), attach file OR paste cloud link
- **File upload:** max 1 file, max 4MB, types: image (jpg/png/gif), document (pdf/doc/docx)
- **Cloud link:** alternative to file upload — paste link to Google Drive, Yandex Disk, etc.
- **Validation:** cannot submit if contest is not in `accepting` status
- **Duplicate guard:** one application per user per contest (or per category? — clarify)
- **Application status:** starts as `new`
- **My applications page:** participant sees their submitted applications with status

#### 3.6 Application for Child Participants
- Parent can submit application on behalf of any of their children
- "Submit as" dropdown shows parent + children
- Application `user_id` set to the selected child (or parent if self)

#### 3.7 Org Application Viewer
- Org reps with `can_manage` or `can_evaluate` see all applications for their org's contests
- Table: applicant name, contest, category, status, date, file link
- Filterable by contest, status

#### 3.8 Form Requests
- `StoreContestRequest`
- `UpdateContestRequest`
- `StoreApplicationRequest`

### Routes Added
```
/contests                            — public contest listing
/contests/{contest}                  — contest detail

/organizations/{org}/contests/create — create contest
/organizations/{org}/contests/{contest}/edit — edit contest

/contests/{contest}/apply            — submit application
/dashboard/applications              — my applications list

/organizations/{org}/applications    — org's applications viewer
```

### Scheduled Commands
```
contests:transition — moves accepting -> evaluation when deadline passes
```

---

## Stage 4: Evaluation, Diplomas & Notifications — 30,000 RUB

### Status: NOT STARTED

### Objective
Build the jury evaluation interface, automatic PDF diploma generation, email notification system, and do final testing/polishing. After this stage, the full platform flow works: create contest -> submit -> evaluate -> diplomas -> notifications.

### Deliverables

#### 4.1 Evaluation Interface
- **Jury dashboard:** list of contests in `evaluation` status for the evaluator's org
- **Evaluation page:** for each contest, show all applications grouped by category
- **Evaluate an application:** assign one of: Participant (just participated), 1st Place, 2nd Place, 3rd Place, Rejected (must provide reason)
- **Editable until finalized:** evaluator can change assessment until "Finish Evaluation" is clicked
- **Ties allowed:** multiple applications can get the same place
- **Status indicators:** color-coded badges per application (new=gray, placed=gold/silver/bronze, rejected=red)

#### 4.2 Finalization Gate
- "Finish Evaluation" button per contest
- **Enabled only when:** all applications evaluated (none with `new` status)
- **On click:** contest -> `archive`, diplomas generated, notifications sent
- **Irreversible:** archived contests cannot be reopened in MVP

#### 4.3 PDF Diploma Generation
- **Library:** barryvdh/laravel-dompdf (or snappy)
- **Diploma contents:** organization name, participant full name, contest title, category, place, date, signature (from template)
- **Background:** uses `diploma_background` image uploaded during contest creation
- **Preview diploma:** generated at application submission time (with placeholder place)
- **Final diploma:** generated automatically when place is assigned
- **Storage:** saved to `storage/app/public/diplomas/`, record in `diplomas` table
- **Download:** participant can download from their dashboard

#### 4.4 Email Notifications
- **Triggers:**
  1. Application submitted (to applicant)
  2. Contest status changed — published, moved to evaluation, archived (to all applicants)
  3. Place awarded (to applicant — include diploma link)
  4. Application rejected (to applicant — include reason)
  5. New contest published (to all users who haven't opted out? — or only org followers? Clarify.)
- **Implementation:** Laravel Mailables + Queued jobs
- **Opt-out:** users.email_notifications flag — if false, skip all emails
- **Templates:** clean, branded Blade email templates

#### 4.5 Admin Tools (Final)
- Admin can manually correct evaluation results (override place assignment)
- Admin can manage diploma templates (upload background images)
- Admin can re-trigger diploma generation for a contest

#### 4.6 Final Testing & Polish
- Test complete flow: register -> create org -> verify -> create contest -> publish -> submit application -> evaluate -> finalize -> diplomas -> notifications
- Test parent-child flow end to end
- Test role access controls (participant can't access admin, etc.)
- Test edge cases: expired contests, blocked users, unverified orgs
- Responsive testing on mobile widths
- Fix any Blade rendering issues, flash messages, validation errors
- Performance check on seeded data

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
