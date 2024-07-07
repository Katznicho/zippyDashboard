<?php

use App\Http\Controllers\AppUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('app-user')->group(function () {

    Route::post('loginOrRegisterByPhone', [AppUserController::class, 'loginOrRegisterByPhone']);
    Route::post('loginOrRegisterByEmail', [AppUserController::class,'loginOrRegisterByEmail']);
    Route::post('verifyOtpPhoneNumber', [AppUserController::class, 'verifyOtpPhoneNumber']);
    Route::post('resendPhoneNumberOtp', [AppUserController::class, 'resendPhoneNumberOtp']);

    Route::post('verifyEmailOtp', [AppUserController::class, 'verifyEmailOtp']);
    Route::post('resendEmailOtpVerification', [AppUserController::class, 'resendEmailOtpVerification']);
    Route::post('updateUserDetails', [AppUserController::class, 'updateUserDetails']);
    Route::post("loginOrRegisterByGoogle", [AppUserController::class, 'loginOrRegisterByGoogle']);

    Route::middleware('auth:app_user')->group(function () {
         
        //app user
        Route::get("fetchLoggedInUserDetails", [AppUserController::class, 'fetchLoggedInUserDetails']);
        Route::post("updateUserDetails", [AppUserController::class, 'updateUserDetails']);
        Route::post("logout", [AppUserController::class, 'logout']);

        //app user


        //more routes
        Route::get('getUserPoints', [AppUserController::class, 'getUserPoints']);

        Route::get('fetchUserPointsUsages', [AppUserController::class, 'fetchUserPointsUsages']);
    
        Route::get('loadMoreUserPointsUsages', [AppUserController::class, 'loadMoreUserPointsUsages']);
    
        Route::post('updateUserPoints', [AppUserController::class, 'updateUserPoints']);
    
        Route::post('createPropertyAlert', [AppUserController::class, 'createPropertyAlert']);
        Route::post("deActivateAlert", [AppUserController::class, "deActivateAlert"]);
        Route::post("ActivateAlert", [AppUserController::class, "ActivateAlert"]);
        Route::get("getUserAlerts", [AppUserController::class, "getUserAlerts"]);
        Route::get("getUserNotifications", [AppUserController::class, "getUserNotifications"]);
        Route::get("getUserLikes", [AppUserController::class, "getUserLikes"]);
    
        Route::post("createUserBooking", [AppUserController::class, "createUserBooking"]);
        Route::get("getUserBookings", [AppUserController::class, "getUserBookings"]);
        //more routes
        Route::get('getUserPayments', [PaymentController::class, 'getUserPayments']);
       
        Route::post("likeProperty", [AppUserController::class, "likeProperty"]);
        Route::post("dislikeProperty", [AppUserController::class, "dislikeProperty"]);

        Route::post("commentOnProperty", [AppUserController::class, "commentOnProperty"]);
        Route::post("commentOnAgentProperty", [AppUserController::class, "commentOnAgentProperty"]);
    
        Route::get("getAppUserSavedPropertiesByPaginated", [AppUserController::class , "getAppUserSavedPropertiesByPaginated"]);

        Route::post("checkIfPropertyLikedByUser", [AppUserController::class, "checkIfPropertyLiked"]);

        Route::post("loadPoints", [AppUserController::class, "loadPoints"]);

        // Route::post('setUpUserWalletAccount', [AuthController::class, 'setUpUserWalletAccount']);
        // Route::post('updateShowWalletBalance', [AuthController::class, 'updateShowWalletBalance']);
        // Route::post('changeCustomerPin', [AuthController::class, 'changeCustomerPin']);
        // Route::post('saveOrUpdateUserLocation', [AuthController::class, 'saveOrUpdateUserLocation']);
        // Route::post('logout', [AuthController::class, 'logout']);
        // Route::post('changePassword', [AuthController::class, 'changePassword']);
        // Route::post('updateAvatar', [AuthController::class, 'updateAvatar']);
        // Route::post('saveDeviceInfo', [AuthController::class, 'saveDeviceInfo']);
        // Route::post('hasWalletAccount', [AuthController::class, 'hasWalletAccount']);
        // Route::post('updateUserAvatarUrl', [AuthController::class, 'updateUserAvatarUrl']);
        // Route::post("resetPasswordFirstUser", [AuthController::class, 'resetPasswordFirstUser']);
        // Route::post('updateUserLocation', [AuthController::class, 'updateUserLocation']);
    });
});