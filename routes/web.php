<?php

use Illuminate\Support\Facades\Route;

Route::view('/tickets', 'tickets.index')
    ->name('tickets.index');
Route::view('/admin/tickets', 'admin.index')
    ->name('admin.tickets.index');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');

    Route::view('/register', 'auth.login')->name('register');
});
