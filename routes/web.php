<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Support\DashboardController as SupportDashboardController;
use Illuminate\Support\Facades\Route;

// ── Public ──────────────────────────────────────────────

Route::get('/', function () {
    return view('welcome');
})->name('home');

// ── Authenticated (any role) ────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {

    // Participant dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile management (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Participant (children) management
    Route::post('/participants', [ParticipantController::class, 'store'])->name('participants.store');
    Route::put('/participants/{participant}', [ParticipantController::class, 'update'])->name('participants.update');
    Route::delete('/participants/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');
});

// ── Admin ───────────────────────────────────────────────

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
});

// ── Support ─────────────────────────────────────────────

Route::middleware(['auth', 'verified', 'role:support'])->prefix('support')->name('support.')->group(function () {
    Route::get('/dashboard', SupportDashboardController::class)->name('dashboard');
});

// ── Auth routes (Breeze) ────────────────────────────────

require __DIR__.'/auth.php';
