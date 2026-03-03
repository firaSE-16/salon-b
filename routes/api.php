<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\TenantMembersController;

// Health
Route::get('/health', fn() => response()->json(['ok'=>true]));

// Auth routes (no tenant resolution needed)
Route::post('/auth/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

// Modules (drop-in)
$modules = [
    __DIR__.'/api.module1.php',
    __DIR__.'/api.module2.php',
    __DIR__.'/api.module3.php',
    __DIR__.'/api.module4.php',
    __DIR__.'/api.module5.php',
    __DIR__.'/api.module6.php',
    __DIR__.'/api.module7.php',
    __DIR__.'/api.module8.php',
    __DIR__.'/api.module9.php',
    __DIR__.'/api.module11.php',
];

Route::middleware(['resolveTenant'])->group(function () use ($modules) {
    foreach ($modules as $f) {
        if (file_exists($f)) require $f;
    }
});


// Identity + context
// /me works without tenant (for superadmins or initial setup)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [MeController::class, 'me']);
});

// /me/salons needs tenant context to fetch salons from tenant database
Route::middleware(['resolveTenant', 'auth:sanctum'])->group(function () {
    Route::get('/me/salons', [MeController::class, 'salons']);
});

// Tenant membership management (central identity)
Route::middleware(['resolveTenant','auth:sanctum','requireTenantMembership'])->group(function () {
    Route::get('/tenant/members', [TenantMembersController::class, 'index']);
    Route::put('/tenant/members', [TenantMembersController::class, 'upsert']); // upsert by email
    Route::delete('/tenant/members/{userId}', [TenantMembersController::class, 'destroy']);
});


// Superadmin onboarding bootstrap (creates tenant + first owner + first salon)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/onboarding/bootstrap', [OnboardingController::class, 'bootstrap']);
});
