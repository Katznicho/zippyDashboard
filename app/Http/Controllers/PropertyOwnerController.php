<?php

namespace App\Http\Controllers;

use App\Mail\RequestPasswordReset;
use App\Models\Amenity;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Service;
use App\Traits\MessageTrait;
use App\Traits\PropertyOwnerTrait;
use App\Traits\UserTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PropertyOwnerController extends Controller
{
    use UserTrait, PropertyOwnerTrait, MessageTrait;

    //Auth area
    // Login a user
    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'password' => 'required|string',
        ]);

        // Find the user
        $user = PropertyOwner::where('phone_number', $request->phone_number)->first();

        // Check if the user exists and the password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'response' => 'failure',
                'message' => 'Invalid credentials',
            ], 401);
        }

        //check if the user verified their email


        // Create an auth token for the user
        $authToken = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'response' => 'success',
            'message' => 'Successfully logged in!',
            'user' => $user,
            'authToken' => $authToken,
        ], 200);
    }


    public function updateFirstUser(Request $request)
    {
        try {
            $user_id =  $this->getCurrentLoggedPropertyOwnerBySanctum()->id;
            $user = PropertyOwner::find($user_id)->update([
                'is_new_user' => false
            ]);

            return response()->json(['response' => 'success', 'message' => 'User updated successfully.']);
            //
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        // Delete the user's current auth token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'response' => 'success',
            'message' => 'Successfully logged out!',
        ], 200);
    }

    //
    // Change a user's password
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        // Find the user
        $user = PropertyOwner::where('email', $request->user()->email)->first();

        // Check if the user exists and the password is correct
        if (!$user || !Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'response' => 'failure',
                'message' => 'Invalid credentials',
                'errors' => [
                    'old_password' => ['Invalid credentials'],
                ],
            ], 401);
        }

        // Check if the new password is the same as the old password
        if ($request->old_password == $request->new_password) {
            return response()->json([
                'response' => 'failure',
                'message' => 'New password cannot be the same as the old password',
                'errors' => [
                    'new_password' => ['New password cannot be the same as the old password'],
                ],
            ], 401);
        }

        // Change the user's password
        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json([
            'response' => 'success',
            'message' => 'Successfully changed password!',
            'trainer' => $user->trainer,
        ], 200);
    }

    // Request a password reset
    public function requestPasswordReset(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|exists:property_owners,phone_number',
        ]);

        // Generate a random OTP code
        $otpCode = random_int(100000, 999999);

        // Get the user
        $user = PropertyOwner::where('phone_number', $request->phone_number)->first();

        // Update the user's OTP code and OTP send time
        $user->update([
            'otp' => Hash::make($otpCode),
            'otp_send_time' => now(),
        ]);

        try {
            // Send the OTP code to the user's email
            Mail::to($user->email)->send(new RequestPasswordReset($user, $otpCode));
        } catch (\Throwable $th) {
            throw $th;
        }

        $message = "To reset verify your password, please use the following one-time
        verification code:  . $otpCode . ";
        $message .= "This code will expire in 5 minutes.";
        $message .= "Thanks,";
        $message .= "Zippy Team";


        $this->sendMessage($user->phone_number, $message);

        return response()->json([
            'response' => 'success',
            'message' => 'OTP sent successfully',
        ], 201);
    }

    //
    // Reset a user's password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|exists:property_owners,phone_number',
            'otp' => 'required|size:6',
            'new_password' => 'required|string|min:6',
            'confirm_new_password' => 'required|string|same:new_password',
        ]);

        // Find the user
        $user = PropertyOwner::where('phone_number', $request->phone_number)->first();

        // Check if the OTP code is correct
        if (!Hash::check($request->otp, $user->otp)) {
            return response()->json([
                'response' => 'failure',
                'message' => 'Incorrect OTP. Check your phone  number for OTP sent to you',
                'errors' => [
                    'otp' => ['Incorrect OTP. Check your phone number for OTP sent to you'],
                ],
            ], 401);
        }

        // Check if the OTP code is expired
        if (now()->diffInMinutes($user->otp_send_time) > 5) {
            return response()->json([
                'response' => 'failure',
                'message' => 'OTP code expired',
                'errors' => [
                    'otp' => ['OTP code expired'],
                ],
            ], 401);
        }

        // Update the user's password
        $user->update([
            'otp' => null,
            'otp_send_time' => null,
            'password' => Hash::make($request->new_password),
        ]);

        // Delete all the user's auth tokens
        $user->tokens()->delete();

        // Create an auth token for the user
        $authToken = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'response' => 'success',
            'message' => 'Successfully reset password!',
            'authToken' => $authToken,
            'user' => $user,
        ], 200);
    }


    public function resetPasswordFirstUser(Request $request)
    {
        try {
            $request->validate([
                'new_password' => 'required|string|min:6',
                'confirm_new_password' => 'required|string|same:new_password',
            ]);

            $user_id  = $this->getCurrentLoggedPropertyOwnerBySanctum()->id;
            $user = PropertyOwner::find($user_id);
            //update user password
            $user->password = Hash::make($request->new_password);
            $user->is_new_user = 0;
            $user->save();
            return response()->json(['response' => 'success', 'message' => 'Password updated successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    //
    // Update user avatar
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required',
        ]);

        // Find the user
        $user = PropertyOwner::where('id', $request->user()->id)->first();

        // Check if the user exists
        if (!$user) {
            return response()->json([
                'response' => 'failure',
                'message' => 'User does not exist',
            ], 401);
        }

        // Update the user's avatar
        $user->avatar = $request->avatar;
        $user->save();

        return response()->json([
            'response' => 'success',
            'message' => 'Successfully updated avatar!',
            'user' => $user,
        ], 200);
    }
    //Auth area

    public function  getAllServices(Request $request)
    {
        try {
            $services =  Service::all();
            return response()->json(
                [
                    'response' => "success",
                    "data" => $services
                ],
                201
            );
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function getAllAmenities(Request $request)
    {
        try {
            $amenities =  Amenity::all();
            return response()->json(
                [
                    'response' => "success",
                    "data" => $amenities
                ],
                201
            );
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function getRegisterPropertyByPage(Request $request)
    {
        try {
            //code...
            $limit = $request->input('limit', 100);
            $page = $request->input('page', 1);
            $sortOrder = $request->input('sort_order', 'desc');

            // $user_id =  $this->getCurrentLoggedPropertyOwnerBySanctum()->id;
            $user_id = auth('property_owner')->user()->id;

            $property = Property::orderBy('id', $sortOrder)->where('owner_id', $user_id)
                ->with([
                    'agent',
                    'owner',
                    'services',
                    'amenities',
                    'category',
                    'amenityProperties',
                    'propertyServices',
                    'paymentPeriod',
                    'status',
                    'currency'

                ])
                ->paginate($limit, ['*'], 'page', $page);

            $response = [
                "data" => $property->items(),
                "pagination" => [
                    "total" => $property->total(),
                    "current_page" => $property->currentPage(),
                    "last_page" => $property->lastPage(),
                    "per_page" => $property->perPage(),
                ]

            ];

            return response()->json(['response' => "success", 'data' => $response], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function getOwnerTotals(Request $request)
    {
        try {
            //code...
            // $user_id =  $this->getCurrentLoggedUserBySanctum()->id;
            // $total_referrals =  User::where('referrer_id', $user_id)->where("property_owner_verified", true)->count();
            // $user_id =  $this->getCurrentLoggedPropertyOwnerBySanctum()->id;
            // $user_id =  $this->getCurrentLoggedPropertyOwnerBySanctum()->id;
            $user_id = auth('property_owner')->user()->id;
            $toal_properties = Property::where('owner_id', $user_id)->count();
            return response()->json(['response' => 'success', 'message' => 'Totals fetched successfully.', 'data' => ['total_properties' => $toal_properties]]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
