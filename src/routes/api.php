<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\ContactListController;
use App\Http\Controllers\Api\ContactListMemberController;


// Exposed
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    
    Route::apiResource('contact-lists', ContactListController::class);
    Route::post('contact-lists/{contactList}/members', [ContactListMemberController::class, 'store']);
    Route::delete('contact-lists/{contactList}/members/{user}', [ContactListMemberController::class, 'destroy']);
});
