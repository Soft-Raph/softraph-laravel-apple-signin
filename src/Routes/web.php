<?php

use Illuminate\Support\Facades\Route;
use Softraph\LaravelAppleSignin\Controllers\AppleSignInController;

Route::get('auth/apple', [AppleSignInController::class, 'redirectToApple']);
Route::post('auth/apple/callback', [AppleSignInController::class, 'handleAppleCallback']);
