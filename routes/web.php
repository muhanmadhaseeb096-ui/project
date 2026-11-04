<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});

// Invitation Public/Guest & Inspection Routes
Route::get('/invitations/{token}', [InvitationController::class, 'show'])
    ->name('invitations.show');
Route::post('/invitations/{token}/register', [InvitationController::class, 'registerAndAccept'])
    ->name('invitations.register');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Invitation Authenticated Accept
    Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])
        ->name('invitations.accept');

    // Projects Management
    Route::resource('projects', ProjectController::class);
    Route::get('/projects/{project}/members', [ProjectController::class, 'members'])
        ->name('projects.members');

    // Project Invitations Management
    Route::post('/projects/{project}/invitations', [InvitationController::class, 'store'])
        ->name('projects.invitations.store');
    Route::post('/projects/{project}/invitations/{invitation}/resend', [InvitationController::class, 'resend'])
        ->name('projects.invitations.resend');
    Route::delete('/projects/{project}/invitations/{invitation}', [InvitationController::class, 'destroy'])
        ->name('projects.invitations.destroy');

    // Tasks Management with Scoped Route Model Binding
    Route::scopeBindings()->group(function () {
        Route::resource('projects.tasks', TaskController::class);
        Route::patch('/projects/{project}/tasks/{task}/status', [TaskStatusController::class, 'update'])
            ->name('projects.tasks.status');
    });

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
