# Talent Center — Online Competition Platform

## Project Overview
A Laravel + Blade + MySQL web platform for hosting online competitions. Organizations create contests, participants submit entries, judges evaluate and assign places, and the system auto-generates PDF diplomas. This is an MVP build.

## Tech Stack
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Blade templates + Tailwind CSS (via Breeze)
- **Database:** MySQL
- **Auth:** Laravel Breeze (Blade stack)
- **PDF Generation:** (Stage 4 — likely dompdf/laravel-dompdf or snappy)
- **Email:** Laravel Mail (SMTP)
- **Server:** Reg.ru + ispmanager

## Project Structure
```
docs/                    # PRD documents per stage
  PRD_STAGE_1.md         # Infrastructure & foundation
app/Models/              # Eloquent models
app/Http/Middleware/      # Role & permission middleware
app/Http/Controllers/    # Organized by domain (Admin/, Support/, etc.)
app/Http/Policies/       # Authorization policies
app/Services/            # Business logic services
database/migrations/     # All DB migrations
database/seeders/        # Admin + test data seeders
resources/views/         # Blade templates
  layouts/               # app.blade.php, guest.blade.php
  components/            # Reusable Blade components
  auth/                  # Breeze auth views
  admin/                 # Admin panel views
  support/               # Support panel views
  dashboard/             # Participant dashboard views
  organizations/         # Org management views
  contests/              # Contest views
  applications/          # Application views
```

## Architecture Decisions

### Roles (stored on `users.role` enum)
- `admin` — System administrator, full access
- `participant` — Regular user, can submit to contests
- `support` — Limited admin, can moderate users/orgs/applications

### Organization Permissions (pivot table `organization_user`)
Organization representatives are NOT a separate role. They are any user (typically participant) linked to an organization via the `organization_user` pivot with granular boolean permissions:
- `can_create` — Create and edit contests for the org
- `can_manage` — Manage org data, org admins, view applications
- `can_evaluate` — Jury access: evaluate applications, assign places, finalize

### Parent-Child Participants
A participant can create other participant accounts (e.g., parent creates children, teacher creates students). This is modeled via `users.parent_id` self-referencing FK. The parent manages child profiles and can submit applications on their behalf.

### Contest Status State Machine
`draft` -> `accepting` -> `evaluation` -> `archive`
- `draft` -> `accepting`: manual (publish)
- `accepting` -> `evaluation`: automatic (when `applications_end_at` passes)
- `evaluation` -> `archive`: manual ("Finish Evaluation" button, requires all applications evaluated)

## Key Conventions

### Code Style
- Follow PSR-12
- Use strict typing: `declare(strict_types=1)` in all PHP files
- Use Laravel conventions: Resource controllers, Form Requests, Eloquent scopes
- Fat models, thin controllers — business logic in Service classes when complex

### Naming
- **Models:** Singular PascalCase (`Contest`, `ContestCategory`)
- **Tables:** Plural snake_case (`contests`, `contest_categories`)
- **Pivot tables:** Alphabetical snake_case (`organization_user`)
- **Controllers:** `{Model}Controller` or `Admin\{Model}Controller` for namespaced
- **Migrations:** Laravel default timestamp format
- **Views:** Dot-notation directory nesting (`admin.dashboard`, `contests.show`)
- **Routes:** RESTful resource routes where possible, named routes always

### Database
- MySQL — use `json` for flexible metadata fields
- Always add foreign key constraints with `->constrained()->cascadeOnDelete()` or `->nullOnDelete()` as appropriate
- Index all foreign keys and status/role enum columns
- Use Laravel enums (backed enums in PHP 8.1+) for status fields when possible

### Authorization
- Middleware `role:admin` on route groups for role-based gating
- Middleware `org.permission:create` for org-level permission checks
- Policies for model-level authorization (`ContestPolicy`, `ApplicationPolicy`, etc.)
- Gate checks in Blade: `@can`, `@role` directive (custom)

### Views
- Master layout: `layouts.app` (authenticated, role-aware nav)
- Guest layout: `layouts.guest` (login, register)
- Use Blade components for reusable UI (`<x-flash-message>`, `<x-nav-sidebar>`)
- All templates must be responsive (mobile-first via Tailwind)

### API / Routes
- No API for MVP — all server-rendered Blade
- Routes grouped by middleware: `auth`, `verified`, `role:admin`, etc.
- Admin routes: `/admin/*`
- Support routes: `/support/*`
- Participant routes: `/dashboard`, `/contests`, `/applications`, etc.
- Org management: `/organizations/*`

## Stage Roadmap
| Stage | Focus | Status | Budget |
|-------|-------|--------|--------|
| 1 | Infrastructure & Database Foundation | LARGELY COMPLETE | 30,000 RUB |
| 2 | Dashboards, Profiles & Organizations | NOT STARTED | 30,000 RUB |
| 3 | Contests & Application Submission | NOT STARTED | 30,000 RUB |
| 4 | Evaluation, Diplomas & Notifications | NOT STARTED | 30,000 RUB |

**Full plan details:** See `docs/PROJECT_PLAN.md`

### Stage 1 — What's Built
- 10 migrations (users, organizations, organization_user, contests, contest_categories, applications, diplomas, action_logs, sessions, password_reset_tokens)
- 7 Eloquent models with full relationships and enum casts
- 5 PHP backed enums (UserRole, OrganizationStatus, ContestStatus, ApplicationStatus, FileType)
- 3 custom middleware (CheckRole, CheckOrgPermission, EnsureOrgVerified)
- 4 authorization policies (User, Organization, Contest, Application)
- Auth scaffolding via Breeze (registration with first_name/last_name/patronymic, role-based login redirect, email verification, password reset)
- Profile editing (first_name, last_name, patronymic, phone, email)
- 3 dashboard stubs (admin with stats, support with pending orgs count, participant with welcome)
- Role-aware navigation with colored role badges
- Flash message component
- ActionLogService (static logger with polymorphic targets)
- Seeders (AdminSeeder + TestDataSeeder with test accounts)

### Stage 1 — What Remains
- Git init + initial commit
- Production server setup on Reg.ru (client handles)
- Edge-case testing of auth flows

## Development Commands
```bash
# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback

# Seed admin account
php artisan db:seed --class=AdminSeeder

# Seed test data (dev only)
php artisan db:seed --class=TestDataSeeder

# Run all seeders
php artisan db:seed

# Clear caches
php artisan optimize:clear

# Run dev server
php artisan serve

# Compile assets (Vite)
npm run dev        # dev with hot reload
npm run build      # production build
```

## Environment Setup
Requires `.env` with:
- `DB_CONNECTION=mysql` + MySQL credentials
- SMTP mail config for email verification and notifications
- `APP_URL` set correctly for links in emails

## Important Business Rules
1. **Organization auto-admin:** The user who creates an organization automatically gets all 3 permissions (create, manage, evaluate)
2. **Org verification required:** Contests cannot be created for unverified organizations
3. **One file per application:** Max 4MB, types: image, PDF/DOC, or cloud storage link
4. **Diploma generation:** Happens automatically when a place is assigned (preview on submission)
5. **Contest auto-transition:** System must automatically move contests from `accepting` to `evaluation` when `applications_end_at` passes (scheduled task / cron)
6. **Finalization gate:** "Finish Evaluation" button only enabled when ALL applications have been evaluated (no `new` status remaining)
7. **Email opt-out:** Users can disable email notifications globally
