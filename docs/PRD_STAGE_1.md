# PRD: Stage 1 — Infrastructure & Foundation

**Project:** Talent Center (Online Competition Platform)
**Stage:** 1 of 4
**Budget:** 30,000 RUB
**Stack:** Laravel 12 + Blade + MySQL
**Version:** 1.0

---

## 1. Stage Objective

Set up the complete development infrastructure and design the database architecture to support the full role-based access control (RBAC) system described in the technical specification. By the end of this stage, the project has a working Laravel installation, a fully migrated MySQL database, authentication scaffolding, and the RBAC foundation — ready for Stage 2 to build upon.

---

## 2. Deliverables

### 2.1 Server & Environment Setup
- [ ] Laravel 12 project initialized
- [ ] MySQL database created and connected
- [ ] `.env` configured for local development
- [ ] Production server setup on Reg.ru + ispmanager (deployment-ready)
- [ ] Git repository initialized with `.gitignore`

### 2.2 Database Architecture (Migrations)
All migrations must be created, tested, and runnable via `php artisan migrate`.

#### Core User Tables

**`users`**
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| email | varchar(255) | unique, used for login |
| password | varchar(255) | hashed |
| first_name | varchar(100) | |
| last_name | varchar(100) | |
| patronymic | varchar(100) | nullable (Russian naming convention) |
| phone | varchar(20) | nullable |
| role | enum | `admin`, `participant`, `support` |
| parent_id | bigint FK nullable | self-referencing — points to the parent user who created this participant |
| email_notifications | boolean | default true |
| is_blocked | boolean | default false |
| email_verified_at | timestamp | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

> **Parent-Child Logic:** When `parent_id` is set, this user was created by another participant (the "parent"). A parent can have many child participants. Children are full user records but managed by the parent account.

**`organizations`**
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | varchar(255) | |
| description | text | nullable |
| inn | varchar(12) | nullable, tax ID |
| legal_address | varchar(500) | nullable |
| contact_email | varchar(255) | nullable |
| contact_phone | varchar(20) | nullable |
| status | enum | `pending`, `verified` |
| created_by | bigint FK | user who created the org |
| verified_by | bigint FK nullable | admin/support who verified |
| verified_at | timestamp | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

**`organization_user` (pivot — Organization Representatives)**
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| organization_id | bigint FK | |
| user_id | bigint FK | |
| can_create | boolean | default false — create/edit contests |
| can_manage | boolean | default false — manage org data & admins, view applications |
| can_evaluate | boolean | default false — jury: evaluate, assign places, finalize |
| created_at | timestamp | |
| updated_at | timestamp | |

> **Unique constraint** on `(organization_id, user_id)` — a user can only have one permission set per org but can belong to multiple orgs.

#### Contest Tables (schema only — functionality in Stage 3)

**`contests`**
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| organization_id | bigint FK | |
| created_by | bigint FK | representative who created it |
| title | varchar(255) | |
| description | text | |
| rules | text | nullable |
| status | enum | `draft`, `accepting`, `evaluation`, `archive` |
| applications_start_at | timestamp | |
| applications_end_at | timestamp | |
| evaluation_end_at | timestamp | |
| diploma_background | varchar(500) | nullable, file path |
| created_at | timestamp | |
| updated_at | timestamp | |

**`contest_categories`**
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| contest_id | bigint FK | |
| name | varchar(255) | |
| description | text | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

#### Application Tables (schema only — functionality in Stage 3)

**`applications`**
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| contest_id | bigint FK | |
| category_id | bigint FK | nullable |
| user_id | bigint FK | the participant |
| status | enum | `new`, `participant`, `place_1`, `place_2`, `place_3`, `rejected` |
| rejection_reason | text | nullable |
| file_path | varchar(500) | nullable |
| file_type | enum | `image`, `document`, `link` |
| external_link | varchar(500) | nullable, cloud storage link |
| evaluated_by | bigint FK nullable | judge who evaluated |
| evaluated_at | timestamp | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

#### Diploma Tables (schema only — functionality in Stage 4)

**`diplomas`**
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| application_id | bigint FK | |
| user_id | bigint FK | |
| contest_id | bigint FK | |
| file_path | varchar(500) | generated PDF path |
| is_preview | boolean | default false |
| created_at | timestamp | |
| updated_at | timestamp | |

#### System Tables

**`action_logs`**
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| user_id | bigint FK nullable | |
| action | varchar(255) | e.g. `user.created`, `contest.published` |
| target_type | varchar(100) | polymorphic — model class |
| target_id | bigint | polymorphic — model id |
| metadata | json | nullable, extra context |
| ip_address | varchar(45) | |
| created_at | timestamp | |

### 2.3 Eloquent Models & Relationships

Create all models with properly defined relationships:

```
User
  hasMany  User (children, via parent_id)
  belongsTo User (parent, via parent_id)
  belongsToMany Organization (via organization_user, with pivot: can_create, can_manage, can_evaluate)
  hasMany Application
  hasMany Diploma
  hasMany ActionLog

Organization
  belongsTo User (createdBy)
  belongsTo User (verifiedBy)
  belongsToMany User (representatives, with pivot permissions)
  hasMany Contest

Contest
  belongsTo Organization
  belongsTo User (createdBy)
  hasMany ContestCategory
  hasMany Application

ContestCategory
  belongsTo Contest
  hasMany Application

Application
  belongsTo Contest
  belongsTo ContestCategory
  belongsTo User (participant)
  belongsTo User (evaluatedBy)
  hasOne Diploma

Diploma
  belongsTo Application
  belongsTo User
  belongsTo Contest

ActionLog
  belongsTo User
  morphTo target
```

### 2.4 RBAC Foundation

#### Middleware
- `role:{role}` — gate by user role (`admin`, `participant`, `support`)
- `org.permission:{permission}` — gate by org-level permission (`create`, `manage`, `evaluate`)
- `verified.org` — ensure the org is verified before allowing contest operations

#### Policy Classes (scaffolded, full logic in later stages)
- `UserPolicy`
- `OrganizationPolicy`
- `ContestPolicy`
- `ApplicationPolicy`

#### Helper Methods on User Model
```php
$user->isAdmin(): bool
$user->isSupport(): bool
$user->isParticipant(): bool
$user->isBlocked(): bool
$user->children(): HasMany  // child participants
$user->parent(): BelongsTo  // parent account
$user->organizationPermissions(Organization $org): object  // {can_create, can_manage, can_evaluate}
$user->canInOrg(string $permission, Organization $org): bool
$user->isOrgAdmin(Organization $org): bool  // has all 3 permissions
```

### 2.5 Auth Scaffolding
- Laravel Breeze installed (Blade stack)
- Registration page (email, password, name fields)
- Login page
- Email verification flow configured
- Password reset flow configured
- Redirect after login based on role:
  - `admin` -> `/admin/dashboard`
  - `support` -> `/support/dashboard`
  - `participant` -> `/dashboard`
  - org representative -> `/dashboard` (same as participant, org panel accessible from there)

### 2.6 Base Blade Layout
- Master layout (`layouts/app.blade.php`) with:
  - Responsive sidebar/navbar structure
  - Role-aware navigation (show different menu items per role)
  - Flash message components
  - Footer
- Guest layout (`layouts/guest.blade.php`) for login/register
- Basic dashboard stub pages per role (content built in Stage 2)

### 2.7 Seeders
- `AdminSeeder` — create default admin account
- `TestDataSeeder` (dev only) — create sample users of each role, sample org, sample data for development

---

## 3. Database ER Diagram (Conceptual)

```
users ──────────┐
  |  parent_id  │ (self-ref)
  |<────────────┘
  |
  ├──< organization_user >──┤ organizations
  |    (pivot w/ perms)      |
  |                          ├──< contests
  |                          |      ├──< contest_categories
  |                          |      └──< applications
  ├──< applications                      |
  |      └── diplomas                    |
  └──< action_logs                       |
                                         |
  applications.category_id ─────> contest_categories
  applications.evaluated_by ────> users
  organizations.created_by ─────> users
  organizations.verified_by ────> users
  contests.created_by ──────────> users
```

---

## 4. Acceptance Criteria

### Must Pass
- [ ] `php artisan migrate` runs cleanly on fresh MySQL database
- [ ] `php artisan migrate:rollback` rolls back without errors
- [ ] All Eloquent models exist with correct relationships
- [ ] User registration, login, logout, email verification, password reset all work
- [ ] Role middleware blocks unauthorized access (e.g., participant can't access `/admin/*`)
- [ ] Organization-user pivot correctly stores granular permissions
- [ ] Parent-child user relationship works (parent_id FK is functional)
- [ ] Admin seeder creates a working admin account
- [ ] Base Blade layouts render correctly on desktop and mobile widths
- [ ] Git repository is clean with meaningful initial commits

### Non-Goals (Later Stages)
- No contest creation UI (Stage 3)
- No application submission (Stage 3)
- No evaluation UI (Stage 4)
- No diploma generation (Stage 4)
- No email notifications (Stage 4)
- No full dashboard content (Stage 2)

---

## 5. Technical Notes

### MySQL-Specific
- Use `json` for `action_logs.metadata` (native MySQL JSON support)
- Use proper enum types via migrations (Laravel casts)
- Index foreign keys and frequently queried columns (`status`, `role`, `parent_id`)

### File Storage
- Configure `storage/app/public` for uploaded files
- Run `php artisan storage:link`
- Diploma backgrounds and uploaded files will live here

### Environment Variables Required
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=talent_center
DB_USERNAME=...
DB_PASSWORD=...

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=...
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=...
```

---

## 6. File Structure (Expected After Stage 1)

```
app/
  Models/
    User.php
    Organization.php
    Contest.php
    ContestCategory.php
    Application.php
    Diploma.php
    ActionLog.php
  Http/
    Middleware/
      CheckRole.php
      CheckOrgPermission.php
      EnsureOrgVerified.php
    Controllers/
      Auth/          (Breeze defaults)
      DashboardController.php
      Admin/
        DashboardController.php
      Support/
        DashboardController.php
    Policies/
      UserPolicy.php
      OrganizationPolicy.php
      ContestPolicy.php
      ApplicationPolicy.php
  Services/
    ActionLogService.php
database/
  migrations/
    ...create_users_table.php
    ...create_organizations_table.php
    ...create_organization_user_table.php
    ...create_contests_table.php
    ...create_contest_categories_table.php
    ...create_applications_table.php
    ...create_diplomas_table.php
    ...create_action_logs_table.php
  seeders/
    AdminSeeder.php
    TestDataSeeder.php
resources/
  views/
    layouts/
      app.blade.php
      guest.blade.php
    components/
      nav/
        sidebar.blade.php
      flash-message.blade.php
    auth/        (Breeze defaults)
    dashboard.blade.php
    admin/
      dashboard.blade.php
    support/
      dashboard.blade.php
routes/
  web.php
  auth.php      (Breeze)
```
