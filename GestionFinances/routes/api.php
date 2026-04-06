<?php

use App\Presentation\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/test', fn () => response()->json(['status' => 'ok']));

Route::prefix('transactions')->group(function () {
    Route::get('/', [TransactionController::class, 'index']);
    Route::post('/', [TransactionController::class, 'store'])->middleware('user.authentication');
    Route::get('/{id}', [TransactionController::class, 'show']);
    Route::patch('/{id}', [TransactionController::class, 'updateStatus'])->middleware('user.authentication');
});
