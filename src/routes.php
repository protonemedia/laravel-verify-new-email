<?php

use Illuminate\Support\Facades\Route;
use ProtoneMedia\LaravelVerifyNewEmail\Http\VerifyNewEmailController;

Route::get('pendingEmail/verify/{token}', [VerifyNewEmailController::class, 'verify'])
    ->middleware('signed')
    ->name('pendingEmail.verify');
