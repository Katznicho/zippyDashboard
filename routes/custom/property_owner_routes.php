<?php

use App\Http\Controllers\PropertyOwnerController;
use Illuminate\Support\Facades\Route;


Route::prefix('property_owner')->group(function () {
    Route::post('register', [PropertyOwnerController::class, 'register']);
    Route::post('verifyEmail', [PropertyOwnerController::class, 'verifyEmail']);
    Route::post('resendOTP', [PropertyOwnerController::class, 'resendOTP']);

    Route::post('login', [PropertyOwnerController::class, 'login']);
    Route::post('requestPasswordReset', [PropertyOwnerController::class, 'requestPasswordReset']);
    Route::post('resetPassword', [PropertyOwnerController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get("getRegisterPropertyOfOwnerByPage", [PropertyOwnerController::class, "getRegisterPropertyByPage"]);
        Route::post("getOwnerTotals", [PropertyOwnerController::class, "getOwnerTotals"]);

        //auth
        Route::post('setUpUserWalletAccount', [PropertyOwnerController::class, 'setUpUserWalletAccount']);
        Route::post('updateShowWalletBalance', [PropertyOwnerController::class, 'updateShowWalletBalance']);
        Route::post('changeCustomerPin', [PropertyOwnerController::class, 'changeCustomerPin']);
        Route::post('saveOrUpdateUserLocation', [PropertyOwnerController::class, 'saveOrUpdateUserLocation']);
        Route::post('logout', [PropertyOwnerController::class, 'logout']);
        Route::post('changePassword', [PropertyOwnerController::class, 'changePassword']);
        Route::post('updateAvatar', [PropertyOwnerController::class, 'updateAvatar']);
        Route::post('saveDeviceInfo', [PropertyOwnerController::class, 'saveDeviceInfo']);
        Route::post('hasWalletAccount', [PropertyOwnerController::class, 'hasWalletAccount']);
        Route::post('updateUserAvatarUrl', [PropertyOwnerController::class, 'updateUserAvatarUrl']);
        Route::post("resetPasswordFirstUser", [PropertyOwnerController::class, 'resetPasswordFirstUser']);
        Route::post('updateUserLocation', [PropertyOwnerController::class, 'updateUserLocation']);
        //auth

        Route::get("getPropertyOwnerPropertyBookings", [PropertyOwnerController::class, "getPropertyOwnerPropertyBookings"]);
    });
    
});

