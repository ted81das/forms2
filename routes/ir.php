<?php

use App\Http\Controllers\IController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installation Web Routes
|--------------------------------------------------------------------------
|
| Routes related to installation of the software
|
*/

Route::get('/install-start', [IController::class, 'index'])->name('install.index');
Route::get('/install/details', [IController::class, 'details'])->name('install.details');
Route::post('/install/post-details', [IController::class, 'postDetails'])->name('install.postDetails');
Route::post('/install/install-alternate', [IController::class, 'installAlternate'])->name('install.installAlternate');
Route::get('/install/success', [IController::class, 'success'])->name('install.success');

Route::get('/install/update', [IController::class, 'updateConfirmation'])->name('install.updateConfirmation');
Route::post('/install/update', [IController::class, 'update'])->name('install.update');
