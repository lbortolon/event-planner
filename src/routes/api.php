<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\ContactListController;
use App\Http\Controllers\Api\ContactListMemberController;

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\InvitationController;

/**
 * Exposed
 */

// User
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/**
 * Protected
 */
Route::middleware('auth:sanctum')->group(function () {
    
    // User
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // Lists
    Route::apiResource('contact-lists', ContactListController::class);
    Route::post('contact-lists/{contactList}/members', [ContactListMemberController::class, 'store']);
    Route::delete('contact-lists/{contactList}/members/{user}', [ContactListMemberController::class, 'destroy']);
    
    // Activities
    Route::apiResource('activities', ActivityController::class);
    Route::post('activities/{activity}/invitations', [InvitationController::class, 'store']);
    Route::patch('activities/{activity}/invitations/{invitation}', [InvitationController::class, 'update']);
    Route::delete('activities/{activity}/invitations/{invitation}', [InvitationController::class, 'destroy']);
});
