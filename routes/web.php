<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ActionLogController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\ContestController as AdminContestController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EvaluationController as AdminEvaluationController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\ContestCoverController as AdminContestCoverController;
use App\Http\Controllers\Admin\DiplomaBackgroundController as AdminDiplomaBackgroundController;
use App\Http\Controllers\Admin\PlatformCategoryController;
use App\Http\Controllers\Admin\SiteSettingsController as AdminSiteSettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PayoutRegistryController as AdminPayoutRegistryController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiplomaController;
use App\Http\Controllers\DiplomaVerifyController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayoutRegistryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepresentativeController;
use App\Http\Controllers\Support\ContestController as SupportContestController;
use App\Http\Controllers\Support\DashboardController as SupportDashboardController;
use App\Http\Controllers\Support\OrganizationController as SupportOrganizationController;
use App\Http\Controllers\Support\UserController as SupportUserController;
use Illuminate\Support\Facades\Route;

// ── Public ──────────────────────────────────────────────

Route::get('/', HomeController::class)->name('home');

// ── Privacy policy (public) ──────────────────────────
Route::get('/privacy-policy', [AdminSiteSettingsController::class, 'privacyPolicy'])->name('privacy-policy');

// ── T-Bank payment callback (public, CSRF-excluded via bootstrap/app.php) ──
Route::post('/payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');

// ── Diploma verification (public) ────────────────────
Route::get('/diplomvtrifi', [DiplomaVerifyController::class, 'index'])->name('diplomvtrifi.search');
Route::post('/diplomvtrifi', [DiplomaVerifyController::class, 'find'])->name('diplomvtrifi.find');
Route::get('/diplomvtrifi/{number}', [DiplomaVerifyController::class, 'show'])->name('diplomvtrifi.show');

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
    Route::post('/organizations/{organization}/add-jury-member', [OrganizationController::class, 'addJuryMember'])->name('organizations.add-jury-member');
    Route::post('/organizations/{organization}/send-jury-invitation', [OrganizationController::class, 'sendJuryInvitation'])->name('organizations.send-jury-invitation');

    // ── Contests (auth-required management) ─────────────
    Route::get('/dashboard/contests', [ContestController::class, 'myIndex'])->name('dashboard.contests');
    Route::post('/contests', [ContestController::class, 'store'])->name('contests.store');
    Route::get('/contests/{contest}/edit', [ContestController::class, 'edit'])->name('contests.edit');
    Route::put('/contests/{contest}', [ContestController::class, 'update'])->name('contests.update');
    Route::post('/contests/{contest}/cancel', [ContestController::class, 'cancel'])->name('contests.cancel');

    // ── Applications ─────────────────────────────────────
    Route::get('/contests/{contest}/apply', [ApplicationController::class, 'create'])->name('applications.create');
    Route::get('/contests/{contest}/diploma-preview', [ApplicationController::class, 'diplomaPreview'])->name('applications.diploma-preview');
    Route::post('/contests/{contest}/apply', [ApplicationController::class, 'store'])->name('applications.store');
    Route::get('/dashboard/applications', [ApplicationController::class, 'myIndex'])->name('dashboard.applications');
    Route::get('/organizations/{organization}/applications', [ApplicationController::class, 'orgIndex'])
        ->name('organizations.applications');

    // ── Evaluation (jury interface) ───────────────────────
    Route::get('/organizations/{organization}/evaluate', [EvaluationController::class, 'index'])->name('evaluation.index');
    Route::get('/organizations/{organization}/contests/{contest}/evaluate', [EvaluationController::class, 'show'])->name('evaluation.show');
    Route::post('/organizations/{organization}/applications/{application}/evaluate', [EvaluationController::class, 'evaluate'])->name('evaluation.evaluate');
    Route::post('/organizations/{organization}/contests/{contest}/finalize', [EvaluationController::class, 'finalize'])->name('evaluation.finalize');

    // ── Diplomas ──────────────────────────────────────────
    Route::get('/dashboard/diplomas', [DiplomaController::class, 'index'])->name('dashboard.diplomas');
    Route::get('/diplomas/{diploma}/download', [DiplomaController::class, 'download'])->name('diplomas.download');

    // ── Payments ──────────────────────────────────────────
    Route::post('/applications/{application}/pay', [PaymentController::class, 'initiate'])->name('payments.initiate');
    Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/fail', [PaymentController::class, 'fail'])->name('payments.fail');

    // ── Org payout registry ───────────────────────────────
    Route::get('/organizations/{organization}/payouts', [PayoutRegistryController::class, 'orgIndex'])->name('organizations.payouts.index');
    Route::post('/organizations/{organization}/payouts/{payoutRegistry}/confirm', [PayoutRegistryController::class, 'confirm'])->name('organizations.payouts.confirm');
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

    // Contest covers
    Route::get('/contest-covers', [AdminContestCoverController::class, 'index'])->name('contest-covers.index');
    Route::post('/contest-covers', [AdminContestCoverController::class, 'store'])->name('contest-covers.store');
    Route::put('/contest-covers/{contestCover}', [AdminContestCoverController::class, 'update'])->name('contest-covers.update');
    Route::delete('/contest-covers/{contestCover}', [AdminContestCoverController::class, 'destroy'])->name('contest-covers.destroy');

    // Diploma backgrounds
    Route::get('/diploma-backgrounds', [AdminDiplomaBackgroundController::class, 'index'])->name('diploma-backgrounds.index');
    Route::post('/diploma-backgrounds', [AdminDiplomaBackgroundController::class, 'store'])->name('diploma-backgrounds.store');
    Route::put('/diploma-backgrounds/{diplomaBackground}', [AdminDiplomaBackgroundController::class, 'update'])->name('diploma-backgrounds.update');
    Route::delete('/diploma-backgrounds/{diplomaBackground}', [AdminDiplomaBackgroundController::class, 'destroy'])->name('diploma-backgrounds.destroy');

    // Contest management
    Route::get('/contests', [AdminContestController::class, 'index'])->name('contests.index');
    Route::get('/contests/{contest}/applications', [AdminContestController::class, 'applications'])->name('contests.applications');
    Route::delete('/contests/{contest}', [AdminContestController::class, 'destroy'])->name('contests.destroy');

    // Application management (global — all contests, all statuses)
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');

    // Evaluation override + diploma regeneration
    Route::get('/contests/{contest}/evaluate', [AdminEvaluationController::class, 'show'])->name('evaluation.show');
    Route::post('/applications/{application}/evaluate', [AdminEvaluationController::class, 'evaluate'])->name('evaluation.evaluate');
    Route::post('/contests/{contest}/regenerate-diplomas', [AdminEvaluationController::class, 'regenerateDiplomas'])->name('contests.regenerate-diplomas');

    // Action logs
    Route::get('/action-logs', [ActionLogController::class, 'index'])->name('action-logs.index');

    // Payments (admin view)
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');

    // Payout registries
    Route::get('/payout-registries', [AdminPayoutRegistryController::class, 'index'])->name('payout-registries.index');
    Route::post('/payout-registries', [AdminPayoutRegistryController::class, 'store'])->name('payout-registries.store');
    Route::put('/payout-registries/{payoutRegistry}', [AdminPayoutRegistryController::class, 'update'])->name('payout-registries.update');
    Route::post('/payout-registries/{payoutRegistry}/document', [AdminPayoutRegistryController::class, 'uploadDocument'])->name('payout-registries.upload-document');
    Route::get('/payout-registries/{payoutRegistry}/requisites', [AdminPayoutRegistryController::class, 'requisites'])->name('payout-registries.requisites');

    // Site settings
    Route::get('/settings', [AdminSiteSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/contacts', [AdminSiteSettingsController::class, 'updateContacts'])->name('settings.contacts');
    Route::post('/settings/privacy-policy', [AdminSiteSettingsController::class, 'updatePrivacyPolicy'])->name('settings.privacy-policy');
    Route::delete('/settings/privacy-policy', [AdminSiteSettingsController::class, 'deletePrivacyPolicy'])->name('settings.privacy-policy.delete');
    Route::post('/settings/logo', [AdminSiteSettingsController::class, 'updateLogo'])->name('settings.logo');
    Route::delete('/settings/logo', [AdminSiteSettingsController::class, 'deleteLogo'])->name('settings.logo.delete');
    Route::post('/settings/favicon', [AdminSiteSettingsController::class, 'updateFavicon'])->name('settings.favicon');
    Route::delete('/settings/favicon', [AdminSiteSettingsController::class, 'deleteFavicon'])->name('settings.favicon.delete');
    Route::post('/settings/brand-text', [AdminSiteSettingsController::class, 'updateBrandText'])->name('settings.brand-text');
    Route::post('/settings/offer-document', [AdminSiteSettingsController::class, 'updateOfferDocument'])->name('settings.offer-document');
    Route::delete('/settings/offer-document', [AdminSiteSettingsController::class, 'deleteOfferDocument'])->name('settings.offer-document.delete');
    Route::post('/settings/contest-settings', [AdminSiteSettingsController::class, 'updateContestSettings'])->name('settings.contest-settings');
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
