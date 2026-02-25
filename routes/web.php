<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ActionLogController;
use App\Http\Controllers\Admin\ContestController as AdminContestController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\PlatformCategoryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepresentativeController;
use App\Http\Controllers\Support\ContestController as SupportContestController;
use App\Http\Controllers\Support\DashboardController as SupportDashboardController;
use App\Http\Controllers\Support\OrganizationController as SupportOrganizationController;
use App\Http\Controllers\Support\UserController as SupportUserController;
use Illuminate\Support\Facades\Route;

// ── Public ──────────────────────────────────────────────

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/development-plan', function () {
    return view('development-plan.index');
})->name('development-plan');

Route::get('/development-plan/{stage}', function (string $stage) {
    $allowed = ['stage-1', 'stage-2', 'stage-3', 'stage-4'];
    if (!in_array($stage, $allowed, true)) {
        abort(404);
    }
    return view("development-plan.{$stage}");
})->name('development-plan.stage');

// Contests — public (must define /contests/create BEFORE /contests/{contest}
// so the literal segment is matched first)
Route::get('/contests', [ContestController::class, 'index'])->name('contests.index');
Route::get('/contests/create', [ContestController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('contests.create');
Route::get('/contests/{contest}', [ContestController::class, 'show'])->name('contests.show');

// ── Authenticated (any role) ────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {

    // Participant dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile management (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

    // Participant (children) management
    Route::get('/participants', [ParticipantController::class, 'index'])->name('participants.index');
    Route::get('/participants/{participant}/edit', [ParticipantController::class, 'edit'])->name('participants.edit');
    Route::post('/participants', [ParticipantController::class, 'store'])->name('participants.store');
    Route::put('/participants/{participant}', [ParticipantController::class, 'update'])->name('participants.update');
    Route::delete('/participants/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');

    // Organizations
    Route::resource('organizations', OrganizationController::class)->except(['destroy']);

    // Organization representatives
    Route::get('/organizations/{organization}/representatives', [RepresentativeController::class, 'index'])->name('organizations.representatives.index');
    Route::post('/organizations/{organization}/representatives', [RepresentativeController::class, 'store'])->name('organizations.representatives.store');
    Route::put('/organizations/{organization}/representatives/{user}', [RepresentativeController::class, 'update'])->name('organizations.representatives.update');
    Route::delete('/organizations/{organization}/representatives/{user}', [RepresentativeController::class, 'destroy'])->name('organizations.representatives.destroy');

    // ── Contests (auth-required management) ─────────────
    Route::get('/dashboard/contests', [ContestController::class, 'myIndex'])->name('dashboard.contests');
    Route::post('/contests', [ContestController::class, 'store'])->name('contests.store');
    Route::get('/contests/{contest}/edit', [ContestController::class, 'edit'])->name('contests.edit');
    Route::put('/contests/{contest}', [ContestController::class, 'update'])->name('contests.update');
    Route::post('/contests/{contest}/cancel', [ContestController::class, 'cancel'])->name('contests.cancel');

    // ── Applications ─────────────────────────────────────
    Route::get('/contests/{contest}/apply', [ApplicationController::class, 'create'])->name('applications.create');
    Route::post('/contests/{contest}/apply', [ApplicationController::class, 'store'])->name('applications.store');
    Route::get('/dashboard/applications', [ApplicationController::class, 'myIndex'])->name('dashboard.applications');
    Route::get('/organizations/{organization}/applications', [ApplicationController::class, 'orgIndex'])
        ->name('organizations.applications');
});

// ── Admin ───────────────────────────────────────────────

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

    // User management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');

    // Organization management
    Route::get('/organizations', [AdminOrganizationController::class, 'index'])->name('organizations.index');
    Route::post('/organizations', [AdminOrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/{organization}', [AdminOrganizationController::class, 'show'])->name('organizations.show');
    Route::get('/organizations/{organization}/edit', [AdminOrganizationController::class, 'edit'])->name('organizations.edit');
    Route::put('/organizations/{organization}', [AdminOrganizationController::class, 'update'])->name('organizations.update');
    Route::post('/organizations/{organization}/verify', [AdminOrganizationController::class, 'verify'])->name('organizations.verify');
    Route::post('/organizations/{organization}/toggle-block', [AdminOrganizationController::class, 'toggleBlock'])->name('organizations.toggle-block');
    Route::post('/organizations/{organization}/representatives', [AdminOrganizationController::class, 'addRepresentative'])->name('organizations.representatives.store');
    Route::put('/organizations/{organization}/representatives/{user}', [AdminOrganizationController::class, 'updateRepresentative'])->name('organizations.representatives.update');
    Route::delete('/organizations/{organization}/representatives/{user}', [AdminOrganizationController::class, 'removeRepresentative'])->name('organizations.representatives.destroy');

    // Platform categories
    Route::get('/platform-categories', [PlatformCategoryController::class, 'index'])->name('platform-categories.index');
    Route::post('/platform-categories', [PlatformCategoryController::class, 'store'])->name('platform-categories.store');
    Route::put('/platform-categories/{platformCategory}', [PlatformCategoryController::class, 'update'])->name('platform-categories.update');
    Route::delete('/platform-categories/{platformCategory}', [PlatformCategoryController::class, 'destroy'])->name('platform-categories.destroy');

    // Contest management
    Route::get('/contests', [AdminContestController::class, 'index'])->name('contests.index');
    Route::get('/contests/{contest}/applications', [AdminContestController::class, 'applications'])->name('contests.applications');
    Route::delete('/contests/{contest}', [AdminContestController::class, 'destroy'])->name('contests.destroy');

    // Action logs
    Route::get('/action-logs', [ActionLogController::class, 'index'])->name('action-logs.index');
});

// ── Support ─────────────────────────────────────────────

Route::middleware(['auth', 'verified', 'role:support'])->prefix('support')->name('support.')->group(function () {
    Route::get('/dashboard', SupportDashboardController::class)->name('dashboard');

    // User management
    Route::get('/users', [SupportUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [SupportUserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [SupportUserController::class, 'update'])->name('users.update');

    // Organization management
    Route::get('/organizations', [SupportOrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/{organization}', [SupportOrganizationController::class, 'show'])->name('organizations.show');
    Route::post('/organizations/{organization}/verify', [SupportOrganizationController::class, 'verify'])->name('organizations.verify');
    Route::delete('/organizations/{organization}/representatives/{user}', [SupportOrganizationController::class, 'removeRepresentative'])->name('organizations.representatives.destroy');

    // Contest management (read-only)
    Route::get('/contests', [SupportContestController::class, 'index'])->name('contests.index');
    Route::get('/contests/{contest}', [SupportContestController::class, 'show'])->name('contests.show');
});

// ── Auth routes (Breeze) ────────────────────────────────

require __DIR__.'/auth.php';
