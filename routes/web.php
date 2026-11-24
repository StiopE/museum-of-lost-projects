<?php

use App\Http\Controllers\AdoptionRequestController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('my/projects')->name('my.projects.')->controller(ProjectController::class)->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{project}', 'update')->name('update');
        Route::patch('/{project}/publish', 'publish')->name('publish');
        Route::patch('/{project}/archive', 'archive')->name('archive');
        Route::delete('/{project}', 'destroy')->name('destroy');
    });

    Route::post('/projects/{project}/adoption-requests', [AdoptionRequestController::class, 'store'])
        ->name('projects.adoption-requests.store');

    Route::prefix('my/adoption-requests')->name('my.adoption-requests.')->controller(AdoptionRequestController::class)->group(function () {
        Route::patch('/{adoptionRequest}/approve', 'approve')->name('approve');
        Route::patch('/{adoptionRequest}/reject', 'reject')->name('reject');
        Route::patch('/{adoptionRequest}/cancel', 'cancel')->name('cancel');
    });
});

require __DIR__.'/settings.php';
