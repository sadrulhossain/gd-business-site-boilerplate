<?php

use App\Http\Controllers\Web\LoginController;
use Illuminate\Support\Facades\Route;

// Login for the OAuth authorization step (session guard "web"). The admin SPA never sees the password.
Route::get('login', [LoginController::class, 'show'])->name('login');
Route::post('login', [LoginController::class, 'store'])->middleware('throttle:login');
Route::get('logout', [LoginController::class, 'destroy'])->name('logout');
