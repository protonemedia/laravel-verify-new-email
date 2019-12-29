<?php

use Illuminate\Support\Facades\Route;
use ProtoneMedia\LaravelVerifyNewEmail\Http\VerificationController;

Route::get('pendingEmail/verify/{token}', VerificationController::class)->middleware('signed')->name('pendingEmail.verify');
