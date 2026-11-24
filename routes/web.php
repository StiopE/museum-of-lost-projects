<?php

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
});

require __DIR__.'/settings.php';
