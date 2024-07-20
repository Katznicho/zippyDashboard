<?php

use App\Http\Controllers\AgentController;
use Illuminate\Support\Facades\Route;


Route::prefix('agent')->group(function () {

    Route::post('register', [AgentController::class, 'register']);
    Route::post('verifyEmail', [AgentController::class, 'verifyEmail']);
    Route::post('resendOTP', [AgentController::class, 'resendOTP']);

    Route::post('login', [AgentController::class, 'login']);
    Route::post('requestPasswordReset', [AgentController::class, 'requestPasswordReset']);
    Route::post('resetPassword', [AgentController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        
        Route::post('setUpUserWalletAccount', [AgentController::class, 'setUpUserWalletAccount']);
        Route::post('updateShowWalletBalance', [AgentController::class, 'updateShowWalletBalance']);
        Route::post('changeCustomerPin', [AgentController::class, 'changeCustomerPin']);
        Route::post('saveOrUpdateUserLocation', [AgentController::class, 'saveOrUpdateUserLocation']);
        Route::post('logout', [AgentController::class, 'logout']);
        Route::post('changePassword', [AgentController::class, 'changePassword']);
        Route::post('updateAvatar', [AgentController::class, 'updateAvatar']);
        Route::post('saveDeviceInfo', [AgentController::class, 'saveDeviceInfo']);
        Route::post('hasWalletAccount', [AgentController::class, 'hasWalletAccount']);
        Route::post('updateUserAvatarUrl', [AgentController::class, 'updateUserAvatarUrl']);
        Route::post("resetPasswordFirstUser", [AgentController::class, 'resetPasswordFirstUser']);
        Route::post('updateUserLocation', [AgentController::class, 'updateUserLocation']);

        //agent
        Route::post("registerPropertyOwner", [AgentController::class, "registerPropertyOwner"]);
        Route::post("verifyPropertyOwnerPhoneNumber", [AgentController::class, "verifyPropertyOwnerPhoneNumber"]);
        Route::get("getRegisterPropertyOwnersByPage", [AgentController::class, "getRegisterPropertyOwnersByPage"]);
        Route::get("getRegisterPropertyByPage", [AgentController::class, "getRegisterPropertyByPage"]);
        Route::post("registerPropertyByAgent", [AgentController::class, "registerPropertyByAgent"]);
        Route::post("getAgentTotals", [AgentController::class, "getAgentTotals"]);
        //Route::post("")
        Route::get("getAllRegisteredPropertyOwners", [AgentController::class, "getAllRegisteredPropertyOwners"]); 
        //
        Route::get("getAgentPropertyBookings", [AgentController::class, "getAgentPropertyBookings"]);
        Route::get("getAgentTransactions", [AgentController::class, "getAgentTransactions"]);
    });



});


