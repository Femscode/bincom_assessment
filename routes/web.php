<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;


// Route::any('/', [App\Http\Controllers\UserController::class, 'homepage'])->name('homepage');
Route::get('/',  [App\Http\Controllers\PollingUnitController::class, 'PollingUnit'])->name('my_polling_unit');

Route::get('/my_polling_unit', [App\Http\Controllers\PollingUnitController::class, 'PollingUnit'])->name('my_polling_unit');
Route::get('/polling_unit_results', [App\Http\Controllers\PollingUnitController::class, 'PollingUnitResult'])->name('polling_unit_results');
Route::post('/fetch_result', [App\Http\Controllers\PollingUnitController::class, 'FetchResult'])->name('fetch_result');
Route::post('/fetch_polling_unit', [App\Http\Controllers\PollingUnitController::class, 'FetchPollingUnit'])->name('fetch_polling_unit');
Route::post('/submit_result', [App\Http\Controllers\PollingUnitController::class, 'submitResult'])->name('submit_result');
Route::any('/create_new', [App\Http\Controllers\PollingUnitController::class, 'CreateNew'])->name('create_new');
Route::any('/polling_unit', [App\Http\Controllers\UserController::class, 'polling_unit'])->name('polling_unit');

