<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
    // 08062277891 adekunle funmilola,08063878055 akinwunmi sambona, yemisi 08069680613,salako sahed 08057036533
});
Route::get('myfirstapi', function () {
    return response(User::all(), 202);
});
Route::post('createalert/{title}', [App\Http\Controllers\AdminController::class, 'createalert_api'])->name('createalert_api');
Route::put('updatealert/{id}', [App\Http\Controllers\AdminController::class, 'updatealert'])->name('updatealert');
Route::any('paystack/webhook', [App\Http\Controllers\PaymentController::class, 'paystack'])->name('handlewebhook');

// Route::any('paystack/webhook',[App\Http\Controllers\WebhooksController::class, 'handleWebhook'])->name('handlewebhook');

Route::post('monnify/transaction_complete', [App\Http\Controllers\PaymentController::class, 'monnifyTransactionComplete2']);
