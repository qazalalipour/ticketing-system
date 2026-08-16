<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FakeExternalServiceController;
use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::post('/tickets/{ticket}/approve', [TicketController::class, 'approve']);
    Route::post('/tickets/{ticket}/reject', [TicketController::class, 'reject']);

    Route::post(
        '/fake-external/tickets',
        [FakeExternalServiceController::class, 'send']
    );

    Route::post('/tickets/bulk-approve', [TicketController::class, 'bulkApprove']);
});
