<?php

use App\Http\Controllers\AdoptionRequestController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectImageController;
use App\Http\Controllers\ProjectUpdateController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/projects/{project}/images/{image}', [ProjectImageController::class, 'show'])
    ->scopeBindings()
    ->name('projects.images.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [ProjectController::class, 'dashboard'])->name('dashboard');

    Route::prefix('my/projects')->name('my.projects.')->controller(ProjectController::class)->group(function () {
        Route::get('/', 'myIndex')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{project}/edit', 'edit')->name('edit');
        Route::put('/{project}', 'update')->name('update');
        Route::patch('/{project}/publish', 'publish')->name('publish');
        Route::patch('/{project}/archive', 'archive')->name('archive');
        Route::delete('/{project}', 'destroy')->name('destroy');
    });

    Route::post('/my/projects/{project}/images', [ProjectImageController::class, 'store'])
        ->name('my.projects.images.store');
    Route::delete('/my/projects/{project}/images/{image}', [ProjectImageController::class, 'destroy'])
        ->scopeBindings()
        ->name('my.projects.images.destroy');

    Route::post('/my/projects/{project}/updates', [ProjectUpdateController::class, 'store'])
        ->name('my.projects.updates.store');
    Route::put('/my/projects/{project}/updates/{update}', [ProjectUpdateController::class, 'update'])
        ->scopeBindings()
        ->name('my.projects.updates.update');
    Route::delete('/my/projects/{project}/updates/{update}', [ProjectUpdateController::class, 'destroy'])
        ->scopeBindings()
        ->name('my.projects.updates.destroy');

    Route::post('/projects/{project}/adoption-requests', [AdoptionRequestController::class, 'store'])
        ->name('projects.adoption-requests.store');

    Route::prefix('my/adoption-requests')->name('my.adoption-requests.')->controller(AdoptionRequestController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('/{adoptionRequest}/approve', 'approve')->name('approve');
        Route::patch('/{adoptionRequest}/reject', 'reject')->name('reject');
        Route::patch('/{adoptionRequest}/cancel', 'cancel')->name('cancel');
    });
});

require __DIR__.'/settings.php';
