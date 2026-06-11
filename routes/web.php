<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserSearchController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('projects', ProjectController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::post('projects/{project}/tasks', [TaskController::class, 'store'])
        ->name('projects.tasks.store');
    Route::put('projects/{project}/tasks/{task}', [TaskController::class, 'update'])
        ->name('projects.tasks.update');
    Route::delete('projects/{project}/tasks/{task}', [TaskController::class, 'destroy'])
        ->name('projects.tasks.destroy');
    Route::post('projects/{project}/tasks/{task}/comments', [TaskCommentController::class, 'store'])
        ->name('projects.tasks.comments.store');
    Route::put('projects/{project}/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'update'])
        ->name('projects.tasks.comments.update');
    Route::delete('projects/{project}/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])
        ->name('projects.tasks.comments.destroy');

    Route::get('users/search', UserSearchController::class)->name('users.search');
});

require __DIR__.'/settings.php';
