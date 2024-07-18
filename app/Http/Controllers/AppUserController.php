<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\RequestPasswordReset;
use App\Mail\UserVerification;
use App\Mail\WalletActivated;
use App\Models\AgentComment;
use App\Models\AppUser;
use App\Models\Booking;
use App\Models\Comment;
use App\Models\Likes;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PointUsage;
use App\Models\Property;
use App\Models\PropertyNotification;
use App\Models\User;
use App\Models\UserAccount;
use App\Models\UserDevice;
use App\Models\UserPoint;
use App\Models\ZippyAlert;
use App\Payments\Pesapal;
use App\Traits\AppUserTrait;
use App\Traits\MessageTrait;
use App\Traits\SendPushNotification;
use App\Traits\UserTrait;
use App\Traits\ZippyAlertTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppUserController extends Controller
{
    use MessageTrait, ZippyAlertTrait, AppUserTrait;

    private function generateOtp()
    {
        return random_int(100000, 999999);
    }

    public function loginOrRegisterByPhone(Request $request)
    {
        try {
            $request->validate([
                'phone_number' => 'required|string',
            ]);

            $user = AppUser::where('phone_number', $request->phone_number)->first();

            if ($user) {
                $otpCode = $this->generateOtp();
                $user->otp = Hash::make($otpCode);
                $user->otp_send_time = now();
                $user->save();
                try {
                    $message = "Your OTP code is: " . $otpCode . " . This code will expire in 5 minutes from Zippy Real Esates";
                    $this->sendMessage($request->phone_number, $message);
                } catch (\Throwable $th) {
                    Log::error('Failed to send OTP: ' . $th->getMessage());
                }
                return response()->json(['response' => 'success', 'message' => 'OTP code sent to your phone.', 'is_new_user' => false]);
            } else {
                $otpCode = $this->generateOtp();
                $user = new AppUser();
                $user->provider ="phoneNumber";
                $user->phone_number = $request->phone_number;
                $user->otp = Hash::make($otpCode);
                $user->otp_send_time = now();
                $user->save();

                $message = "Your OTP code is: " . $otpCode . " . This code will expire in 5 minutes from Zippy Real Esates";
                $this->sendMessage($request->phone_number, $message);
                return response()->json(['response' => 'success', 'message' => 'OTP code sent to your phone.', 'is_new_user' => true]);
            }
        } catch (Throwable $th) {
            Log::error('Login or register by phone failed: ' . $th->getMessage());
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function verifyOtpPhoneNumber(Request $request)
    {
        try {
            $request->validate([
                'phone_number' => 'required|string',
                'otp' => 'required|string',
            ]);

            $user = AppUser::where('phone_number', $request->phone_number)->first();

            if ($user) {
                if (Hash::check($request->otp, $user->otp)) {
                    $user->otp = null;
                    $user->otp_send_time = null;
                    $user->save();
                            // Create an auth token for the user
        $authToken = $user->createToken('authToken')->plainTextToken;
                    return response()->json([
                        'response' => 'success',
                         'message' => 'OTP verified successfully.',
                         'data'=>[
                            'user' => $user,
                            'authToken'=> $authToken
                            ]
                ]);
                } else {
                    return response()->json(['response' => 'failure', 'message' => 'Invalid OTP.']);
                }
            } else {
                return response()->json(['response' => 'failure', 'message' => 'Invalid OTP.']);
            }
        } catch (Throwable $th) {
            Log::error('Verify OTP by phone number failed: ' . $th->getMessage());
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function verifyEmailOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string',
                'otp' => 'required|string',
            ]);

            $user = AppUser::where('email', $request->email)->first();

            if ($user) {
                if (Hash::check($request->otp, $user->otp)) {
                    $user->otp = null;
                    $user->otp_send_time = null;
                    $user->save();

        $authToken = $user->createToken('authToken')->plainTextToken;
        return response()->json([
            'response' => 'success',
             'message' => 'OTP verified successfully.',
             'data'=>[
                'user' => $user,
                'authToken'=> $authToken
                ]
                ]);
                } else {
                    return response()->json(['response' => 'failure', 'message' => 'Invalid OTP.']);
                }
            } else {
                return response()->json(['response' => 'failure', 'message' => 'Invalid OTP.']);
            }
        } catch (Throwable $th) {
            Log::error('Verify email OTP failed: ' . $th->getMessage());
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function resendPhoneNumberOtp(Request $request)
    {
        try {
            $request->validate([
                'phone_number' => 'required|string',
            ]);

            $user = AppUser::where('phone_number', $request->phone_number)->first();

            if ($user) {
                $otpCode = $this->generateOtp();
                $user->otp = Hash::make($otpCode);
                $user->otp_send_time = now();
                $user->save();
                $this->sendMessage($request->phone_number, $otpCode);
                return response()->json(['response' => 'success', 'message' => 'OTP code sent to your phone.']);
            } else {
                return response()->json(['response' => 'failure', 'message' => 'Invalid OTP.']);
            }
        } catch (Throwable $th) {
            Log::error('Resend phone number OTP failed: ' . $th->getMessage());
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function resendEmailOtpVerification(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string',
            ]);

            $user = AppUser::where('email', $request->email)->first();

            if ($user) {
                $otpCode = $this->generateOtp();
                $user->otp = Hash::make($otpCode);
                $user->otp_send_time = now();
                $user->save();
                try {
                    Mail::to($user->email)->send(new UserVerification($user, $otpCode));
                } catch (Throwable $th) {
                    Log::error('Failed to send OTP email: ' . $th->getMessage());
                    return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
                }
                return response()->json(['response' => 'success', 'message' => 'OTP code sent to your email.']);
            } else {
                return response()->json(['response' => 'failure', 'message' => 'Invalid OTP.']);
            }
        } catch (Throwable $th) {
            Log::error('Resend email OTP verification failed: ' . $th->getMessage());
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        try {
            $appUserId = $this->getCurrentLoggedAppUserBySanctum()->id;
            $user =  AppUser::find($appUserId);
            //delete token
            $user->tokens()->delete();
            // $request->user()->tokens()->delete();
            return response()->json(['response' => 'success', 'message' => 'Logged out successfully.']);
        } catch (Throwable $th) {
            Log::error('Logout failed: ' . $th->getMessage());
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function loginOrRegisterByEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
            ]);

            $user = AppUser::where('email', $request->email)->first();

            if ($user) {
                $otpCode = $this->generateOtp();
                $user->otp = Hash::make($otpCode);
                $user->otp_send_time = now();
                $user->save();
                try {
                    Mail::to($user->email)->send(new UserVerification($user, $otpCode));
                } catch (Throwable $th) {
                    Log::error('Failed to send OTP email: ' . $th->getMessage());
                    return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
                }
                return response()->json(['response' => 'success', 'message' => 'OTP code sent to your email.']);
            } else {
                $otpCode = $this->generateOtp();
                $user = new AppUser();
                $user->provider ="email";
                $user->email = $request->email;
                $user->otp = Hash::make($otpCode);
                $user->otp_send_time = now();
                $user->save();
                try {
                    Mail::to($user->email)->send(new UserVerification($user, $otpCode));
                } catch (Throwable $th) {
                    Log::error('Failed to send OTP email: ' . $th->getMessage());
                    return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
                }
                return response()->json(['response' => 'success', 'message' => 'OTP code sent to your email.']);
            }
        } catch (Throwable $th) {
            Log::error('Login or register by email failed: ' . $th->getMessage());
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function updateUserDetails(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
            ]);
            $user =  $this->getCurrentLoggedAppUserBySanctum();

            $name =  $request->first_name . " " . $request->last_name;
             $res = AppUser::where('id', $user->id)->update([
                 'name' => $name,
                 'email' => $request->email,
                 'phone_number' => $request->phone_number,
                 'dob' => $request->dob,
             ]);

            if (!$res) {
                return response()->json(['response' => 'failure', 'message' => 'Failed to update user details.']);
            }

            return response()->json(['response' => 'success', 'message' => 'User details updated successfully.']);
        } catch (Throwable $th) {
            // Log::error('Update user details failed: ' . $th->getMessage());
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    // public function loginOrRegisterByGoogle(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'first_name' => 'required|string',
    //             'last_name' => 'required|string',
    //             'email' => 'required|string',
    //             'picture'=>'required|string',
    //         ]);

    //         $user = AppUser::where('email', $request->email)->first();
    //         $user->email = $request->email;
    //         $user->save();
    //         return response()->json(['response' => 'success', 'message' => 'User logged in or registered by Google successfully.']);
    //     } catch (Throwable $th) {
    //         Log::error('Login or register by Google failed: ' . $th->getMessage());
    //         return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
    //     }
    // }

    //login or register by google
    public function loginOrRegisterByGoogle(Request $request)
{
    try {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email',
            'picture' => 'required|string',
        ]);

        $user = AppUser::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->first_name." ".$request->last_name,
                'provider' => 'google',
                'picture' => $request->picture,
            ]
        );

        // Update user details if user already exists
        if (!$user->wasRecentlyCreated) {
            $user->update([
                // 'first_name' => $request->first_name,
                // 'last_name' => $request->last_name,
                'name' => $request->first_name." ".$request->last_name,
                'provider' => 'google',
                'picture' => $request->picture,
            ]);
        }

        // Generate authentication token
        $authToken = $user->createToken('authToken')->plainTextToken;

        // Return response with user details and authentication token
        return response()->json([
            'response' => 'success',
            'message' => 'User logged in or registered by Google successfully.',
            'data' => [
                'user' => $user,
                'authToken' => $authToken
            ]
        ]);
    } catch (Throwable $th) {
        Log::error('Login or register by Google failed: ' . $th->getMessage());
        return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
    }
}

    //login or register by google

    public function fetchLoggedInUserDetails(Request $request)
    {
        try {
            $user =  $this->getCurrentLoggedAppUserBySanctum();
            $user_id = $user->id;
            $app_user =  AppUser::where('id', $user_id)->first();
            // $user = User::where('email', $request->email)->first();
            return response()->json(['response' => 'success', 'data' => $user]);
        } catch (Throwable $th) {
            Log::error('Fetch logged in user details failed: ' . $th->getMessage());
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    //here
    // public function getUserPoints(Request $request)
    // {
    //     try {

    //         // $user_id =  $this->getCurrentLoggedUserBySanctum()->id;
    //         $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
    //         $user = AppUser::with([
    //             'pointUsages',
    //             'userPoints'
    //         ])->find($user_id);
    //         return response()->json(['data' => $user, 'response' => 'success', 'message' => 'User Points fetched successfully.']);
    //     } catch (\Throwable $th) {
    //         return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
    //     }
    // }

    public function getUserPoints(Request $request)
{
    try {
        $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
        $user = AppUser::with([
            'pointUsages',
            'userPoints'
        ])->find($user_id);

        if ($user) {
            // Ensure the custom attributes are loaded
            $user->load('pointUsages', 'userPoints');

            return response()->json([
                'data' => [
                    'user' => $user,
                    'total_points' => $user->total_points,
                    'used_points' => $user->used_points,
                    'current_points' => $user->current_points,
                ],
                'response' => 'success',
                'message' => 'User Points fetched successfully.'
            ]);
        } else {
            return response()->json([
                'response' => 'failure',
                'message' => 'User not found.'
            ]);
        }
    } catch (\Throwable $th) {
        return response()->json([
            'response' => 'failure',
            'message' => $th->getMessage()
        ]);
    }
}


    public function fetchUserPointsUsages(Request $request)
    {
        try {
            $user_id =  $this->getCurrentLoggedUserBySanctum()->id;
            // $user = User::find($user_id)->pointUsages;
            $points = PointUsage::where('app_user_id', $user_id)->get();
            return response()->json(['response' => 'success', 'data' => $points, 'message' => 'User Usage Points fetched successfully.']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function loadMoreUserPointsUsages(Request $request)
    {
        try {
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
            $points = PointUsage::where('user_id', $user_id)->skip($request->skip)->take($request->take)->get();
            return response()->json(['response' => 'success', 'data' => $points, 'message' => 'User Usage Points fetched successfully.']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function createPropertyAlert(Request $request)
    {
        try {

            // $user_id =  $this->getCurrentLoggedUserBySanctum()->id;
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;

            //first count the number of alerts a user has
            $total_alerts = ZippyAlert::where('app_user_id', $user_id)->count();
            if ($total_alerts > 4) {
                return response()->json(['response' => 'failure', 'alert_max' => true, 'message' => "You have reached the maximum number of alerts. You can only have up to 4 alerts."]);
            }
            $user = AppUser::find($user_id);

            $request->validate([
                //'services' => 'required|array',
                //'amenities' => 'required|array',
                // 'contact_options' => 'required|array',
                //'number_of_bedrooms' => 'required',
                //'number_of_bathrooms' => 'required',
                //'cost' => 'required',
                'category_id' => 'required',
                //'longitude' => 'required',
                //'latitude' => 'required',
                //'address' => 'required',
            ]);
            $userAlert =  ZippyAlert::create([
                'app_user_id' => $user_id,
                'category_id' => $request->category_id,
                'services' => json_encode($request->services),
                'amenities' => json_encode($request->amenities),
                'cost' => $request->cost,
                // 'contact_options' => json_encode($request->contact_options),
                'number_of_bedrooms' => $request->number_of_bedrooms,
                'number_of_bathrooms' => $request->number_of_bathrooms,
                'is_active' => true,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'address' => $request->address
            ]);

            $this->zippySearchAlgorithm($request, $user, $userAlert);

            

            return response()->json(['response' => 'success', 'message' => 'Property Alert created successfully.']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function deActivateAlert(Request $request)
    {
        try {
            // $user_id =  $this->getCurrentLoggedUserBySanctum()->id;
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
            $user = User::find($user_id);
            $userAlert =  ZippyAlert::where('app_user_id', $user_id)->where('id', $request->alert_id)->update(['is_active' => false]);

            $message = "Hello " . $user->name . ",\n\n"  . "Your Zippy Alert has been deactivated.\n\n" . "Regards,\n" . "Zippy Team";
            if ($user->phone_number) {
                $this->sendMessage($user->phone_number, $message);
            }
            return response()->json(['response' => 'success', 'message' => 'Property Alert deactivated successfully.']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function ActivateAlert(Request $request)
    {
        try {
            // $user_id =  $this->getCurrentLoggedUserBySanctum()->id;
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
            $user = User::find($user_id);
            $userAlert =  ZippyAlert::where('app_user_id', $user_id)->where('id', $request->alert_id)->update(['is_active' => true]);
            $message = "Hello " . $user->name . ",\n\n"  . "Your Zippy Alert has been deactivated.\n\n" . "Regards,\n" . "Zippy Team";
            if ($user->phone_number) {
                $this->sendMessage($user->phone_number, $message);
            }
            return response()->json(['response' => 'success', 'message' => 'Property Alert activated successfully.']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function getUserAlerts(Request $request)
    {
        try {
            // $user_id =  $this->getCurrentLoggedUserBySanctum()->id;
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
            $userAlerts = ZippyAlert::where('app_user_id', $user_id)
                ->with(['category', 'appUser', 'user'])
                ->get();
            return response()->json(['alerts' => $userAlerts, 'response' => 'success', 'message' => 'User Alerts fetched successfully.']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    // public function getUserNotifications(Request $request)
    // {
    //     try {
    //         $limit = $request->input('limit', 100);
    //         $page = $request->input('page', 1);
    //         $sortOrder = $request->input('sort_order', 'desc');
    //         // $user_id = $this->getCurrentLoggedUserBySanctum()->id;
    //         $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;

    //         // Add a status filter if 'status' is provided in the request
    //         //$status = $request->input('status');
    //         $paymentQuery = Notification::where('app_user_id', $user_id);

    //         // if (!empty($status)) {
    //         //     $paymentQuery->where('status', $status);
    //         // }

    //         $res = $paymentQuery->orderBy('id', $sortOrder)->with([
    //             'user', 'property', 'appUser'
    //         ])->paginate($limit, ['*'], 'page', $page);

    //         $response = [
    //             'data' => $res->items(),
    //             'pagination' => [
    //                 'current_page' => $res->currentPage(),
    //                 'per_page' => $limit,
    //                 'total' => $res->total(),
    //             ],
    //         ];

    //         return response()->json(['success' => true, 'data' => $response]);
    //     } catch (\Throwable $th) {
    //         return response()->json(['success' => false, 'message' => $th->getMessage()]);
    //     }
    // }


    public function getUserNotifications(Request $request)
{
    try {
        $limit = $request->input('limit', 100);
        $page = $request->input('page', 1);
        $sortOrder = $request->input('sort_order', 'desc');
        $user_id = $this->getCurrentLoggedAppUserBySanctum()->id;

        $notificationQuery = Notification::where('app_user_id', $user_id);

        $res = $notificationQuery->orderBy('id', $sortOrder)
            ->with([
                'user',
                'property.agent',
                'property.owner',
                'property.services',
                'property.amenities',
                'property.category',
                'property.amenityProperties',
                'property.propertyServices',
                'property.paymentPeriod',
                'property.status',
                'property.currency',
                'appUser'
            ])->paginate($limit, ['*'], 'page', $page);

        $response = [
            'data' => $res->items(),
            'pagination' => [
                'current_page' => $res->currentPage(),
                'per_page' => $limit,
                'total' => $res->total(),
            ],
        ];

        return response()->json(['success' => true, 'data' => $response]);
    } catch (\Throwable $th) {
        return response()->json(['success' => false, 'message' => $th->getMessage()]);
    }
}


public function getUserLikes(Request $request)
{
    try {
        $limit = $request->input('limit', 100);
        $page = $request->input('page', 1);
        $sortOrder = $request->input('sort_order', 'desc');
        $user_id = $this->getCurrentLoggedAppUserBySanctum()->id;

        $res = Likes::where('app_user_id', $user_id)
            ->where('is_like', true)
            ->orderBy('id', $sortOrder)
            ->with([
                'user',
                'property.agent',
                'property.owner',
                'property.services',
                'property.amenities',
                'property.category',
                'property.amenityProperties',
                'property.propertyServices',
                'property.paymentPeriod',
                'property.status',
                'property.currency',
                'appUser'
            ])->paginate($limit, ['*'], 'page', $page);

        $response = [
            'data' => $res->items(),
            'pagination' => [
                'current_page' => $res->currentPage(),
                'per_page' => $limit,
                'total' => $res->total(),
            ],
        ];

        return response()->json(['success' => true, 'data' => $response]);   
    } catch (\Throwable $th) {
        return response()->json(['success' => false, 'message' => $th->getMessage()]);
    }
}



    public function createUserBooking(Request $request)
    {
        try {
            $request->validate([
                'property_id' => 'required',
                'total_price' => 'required',
            ]);

             $property_details= Property::find($request->property_id);
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
            $user =  AppUser::find($user_id);
            $reference = Str::uuid();
            $res =  Payment::create([
            'reference' => $reference,
            'amount' => $request->total_price,
            'type' => "Booking",
            'app_user_id' => $user_id,
            'payment_mode' => "App",
            'reference' => $reference,
            'status' => config('status.payment_status.pending'),
            'phone_number' => $user->phone_number,
            'description' => "Payment for a property booking",    
            ]);

            if(!$res){
                return response()->json(['success' => false, 'message' => 'Something went wrong.']);
            }

            $res = Booking::create([
                'app_user_id' => $user_id,
                'property_id' => $request->property_id,
                'total_price' => $request->total_price,
                'payment_id' => $res->id,
                'status' => 'pending',
                'agent_id' => $property_details->agent_id,
                'owner_id' => $property_details->owner_id,
            ]);

            if(!$res){
                return response()->json(['success' => false, 'message' => 'Something went wrong.']);
            }
            else{

                // $amount = $request->input('total_price');
                $amount =  500;
                $phone = $user->phone_number;
                $callback = "https://dashboard.zippyug.com/finishPayment";
                //$reference = Str::uuid();
                $reference = $reference;
                $description = "Payment for a property booking";
                $names = explode(" ", $user->name);
                $first_name = $names[0];
                $second_name = $names[1];
                $email = $user->email;
                $cancel_url = "https://dashboard.zippyug.com/cancelPayment" . '?payment_reference=' . $reference;
                $payment_type = "Booking";
                
                // return $payment_type;
                // return $amount;
                $data = Pesapal::orderProcess($reference, $amount, $phone, $description, $callback, $first_name, $email, $second_name, $cancel_url, $payment_type, 'App');
                if ($data->success) {
                    return response()->json(['success' => true, 'message' => 'Booking created successfully.', 'data'=> $data->message->redirect_url ]);
                } else {
                    //return redirect()->back()->with('error', 'Payment Failed please try again');
                    return response()->json(['success' => false, 'message' => "Payment Failed please try again"]);
                }
                
    
                // return response()->json(['success' => true, 'message' => 'Order processed successfully', 'response' => $data]);

                // return response()->json(['success' => true, 'message' => 'Booking created successfully.']);
            }

            return response()->json(['success' => true, 'data' => $res, 'message' => 'Booking created successfully.']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function loadPoints(Request $request){
        try {
            $request->validate([
                'amount' => 'required',
                'points' => 'required',
                'phone_number' => 'required',
            ]);
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
            $user =  AppUser::find($user_id);
            $reference = Str::uuid();
            $res =  Payment::create([
            'reference' => $reference,
            'amount' => $request->amount,
            'type' => "Points",
            'app_user_id' => $user_id,
            'payment_mode' => "App",
            'reference' => $reference,
            'status' => config('status.payment_status.pending'),
            'phone_number' => $request->phone_number,
            'description' => "Payment for a property points",    
            ]);
            if(!$res){
                return response()->json(['success' => false, 'message' => 'Something went wrong.']);
            }
            UserPoint::create([
                'app_user_id' => $user_id,
                'points' => $request->points,
                'payment_id' => $res->id,
                'status' => 'pending',
                'description' => $request->reason ?? "Payment for a property points",
                'reference' => $reference
            ]);

            //create a payment
               $amount =  500;
                $phone = $user->phone_number;
                $callback = "https://dashboard.zippyug.com/finishPayment";
                //$reference = Str::uuid();
                $reference = $reference;
                $description = $request->reason ?? "Payment for a property points";
                $names = explode(" ", $user->name);
                $first_name = $names[0];
                $second_name = $names[1];
                $email = $user->email;
                $cancel_url = "https://dashboard.zippyug.com/cancelPayment" . '?payment_reference=' . $reference;
                $payment_type = "Points";

                $data = Pesapal::orderProcess($reference, $amount, $phone, $description, $callback, $first_name, $email, $second_name, $cancel_url, $payment_type, 'App');
                if ($data->success) {
                    return response()->json(['success' => true, 'message' => 'Points created successfully.', 'data'=> $data->message->redirect_url ]);
                } else {
                    //return redirect()->back()->with('error', 'Payment Failed please try again');
                    return response()->json(['success' => false, 'message' => "Payment Failed please try again"]);
                }

        }
        catch(\Throwable $th){
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function getUserBookings(Request $request)
    {
        try {
            $limit = $request->input('limit', 100);
            $page = $request->input('page', 1);
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;

            $bookingQuery = Booking::where('app_user_id', $user_id)->with(['property', 'user']);

            $bookings = $bookingQuery->paginate($limit, ['*'], 'page', $page);

            $response = [
                'data' => $bookings->items(),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'per_page' => $limit,
                    'total' => $bookings->total(),
                ],
            ];

            return response()->json(['success' => true, 'data' => $response]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
    //here

    public function getUserPayments(Request $request)
    {
        try {
            //code...
            $limit = $request->input('limit', 100);
            $page = $request->input('page', 1);
            $sortOrder = $request->input('sort_order', 'desc');
            // $user_id = $this->getCurrentLoggedUserBySanctum()->id;
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
            

            // $user =  AppUser::find($user_id);
            // return $user;

            // Add a status filter if 'status' is provided in the request
            $status = $request->input('status');
            $paymentQuery = Payment::where('app_user_id', $user_id);

            if (!empty($status)) {
                $paymentQuery->where('status', $status);
            }

            $res = $paymentQuery->orderBy('id', $sortOrder)->with([
                'appUser',
                'property',
                'user',
                'booking'
            ])->paginate($limit, ['*'], 'page', $page);

            $response = [
                'data' => $res->items(),
                'pagination' => [
                    'current_page' => $res->currentPage(),
                    'per_page' => $limit,
                    'total' => $res->total(),
                ],
            ];

            return response()->json(['success' => true, 'data' => $response]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function likeProperty(Request $request){
        try {   
            $request->validate([
                'property_id' => 'required',
            ]);
            
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
    
            // Check if a record already exists for the given user and property
            $like = Likes::where('app_user_id', $user_id)
                          ->where('property_id', $request->property_id)
                          ->first();
    
            if ($like) {
                // Update the existing record
                $like->is_like = 1;
                $like->is_dislike = 0;
                $like->save();
            } else {
                // Create a new record
                $like = Likes::create([
                    'app_user_id' => $user_id,
                    'property_id' => $request->property_id,
                    'is_like' => 1,
                    'is_dislike' => 0,
                ]);
            }
    
            return response()->json(['success' => true, 'data' => $like, 'message' => 'Property liked successfully.']);
        }
        catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
    
    public function dislikeProperty(Request $request){
        try {   
            $request->validate([
                'property_id' => 'required',
            ]);
    
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
    
            // Check if a record already exists for the given user and property
            $like = Likes::where('app_user_id', $user_id)
                          ->where('property_id', $request->property_id)
                          ->first();
    
            if ($like) {
                // Update the existing record
                $like->is_like = 0;
                $like->is_dislike = 1;
                $like->save();
            } else {
                // Create a new record
                $like = Likes::create([
                    'app_user_id' => $user_id,
                    'property_id' => $request->property_id,
                    'is_like' => 0,
                    'is_dislike' => 1,
                ]);
            }
    
            return response()->json(['success' => true, 'data' => $like, 'message' => 'Property disliked successfully.']);
        }
        catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function checkIfPropertyLiked(Request $request){
        try {
            $request->validate([
                'property_id' => 'required',
            ]);

            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;

            $res = Likes::where('app_user_id', $user_id)
            ->where('property_id', $request->property_id)
            ->where('is_like', 1)
            ->where('is_dislike', 0)
            ->first();
            if(!$res){
                return response()->json(['success' => false, 'message' => 'Property not liked.']);
            }

            return response()->json(['success' => true, 'message' => 'Property liked successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);

            
        }
    }
    



    public function commentOnProperty(Request $request){
        try {   
            $request->validate([
                'property_id' => 'required',
                'message' => 'required',
                 'rating'=>'required'
            ]);
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
            $res = Comment::create([
                'app_user_id' => $user_id,
                'property_id' => $request->property_id,
                'rating' => $request->rating,
                'body' => $request->message,
            ]);
            return response()->json(['success' => true, 'data' => $res, 'message' => 'Property comment successfully.']);
    }

    catch (\Throwable $th) {
        return response()->json(['success' => false, 'message' => $th->getMessage()]);
    }
    }


    public function commentOnAgentProperty(Request $request){
        try {   
            $request->validate([
                'agent_id' => 'required',
                'message' => 'required',
                'rating'=>'required'
            ]);
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;
            $res = AgentComment::create([
                'app_user_id' => $user_id,
                'agent_id' => $request->agent_id,
                'rating' => $request->rating,
                'body' => $request->message,
            ]);
            return response()->json(['success' => true, 'data' => $res, 'message' => 'Agent comment successfully.']);
        }

        catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }

    }

    public function getAppUserSavedPropertiesByPaginated(Request $request){

        try {
            $user_id =  $this->getCurrentLoggedAppUserBySanctum()->id;

            $limit = $request->input('limit', 100);
            $page = $request->input('page', 1);
            $sortOrder = $request->input('sort_order', 'desc');

            $res = Likes::where('app_user_id', $user_id)->orderBy('id', $sortOrder)->with([
                'property'
            ])->paginate($limit, ['*'], 'page', $page);

            $response = [
                "data" => $res->items(),
                "pagination" => [
                    "total" => $res->total(),
                    "current_page" => $res->currentPage(),
                    "last_page" => $res->lastPage(),
                    "per_page" => $res->perPage(),
                ]
                ];

            return response()->json(['success' => true, 'data' => $response, 'message' => 'Property saved successfully.']); 
        }

        catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }

    }

    public function editProfile(Request $request)
    {
        try {
             $request->validate([
                 'first_name'=>'required',
                 'last_name'=>'required',
                 'phone_number'=>'required',
                 'dob'=>'required',
             ]);
            $user = $this->getCurrentLoggedAppUserBySanctum();
            $user->name = $request->name;
            //$user->save();
            return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function updateUserLocation(Request $request)
    {
        try {
            $request->validate([
                'lat' => 'required|string|max:255',
                'long' => 'required|string|max:255',
            ]);
            // $user = $this->getCurrentLoggedUserBySanctum();
            $user = $this->getCurrentLoggedAppUserBySanctum();
            AppUser::find($user->id)->update(
                ['lat' => $request->lat, 'long' => $request->long]
            );

            return response()->json(['response' => 'success', 'message' => 'Location updated successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    //save device info
    public function saveDeviceInfo(Request $request)
    {
        $request->validate([
            'push_token' => 'required|string|max:255',
            'device_id' => 'required|string|max:255',
            'device_model' => 'required|string|max:255',
            'device_manufacturer' => 'required|string|max:255',
            'app_version' => 'required|string|max:255',
            'device_os' => 'required|string|max:255',
            'device_os_version' => 'required|string|max:255',
            'device_user_agent' => 'required|string|max:255',
            'device_type' => 'required|string|max:255',
        ]);



        $user = $this->getCurrentLoggedAppUserBySanctum();

        // Check this info is already saved or not
        $userDevice = UserDevice::where('app_user_id', $user->id)
            ->where('device_id', $request->device_id)
            ->first();

        if ($userDevice) {
            $userDevice->update([
                'push_token' => $request->push_token,
                'device_model' => $request->device_model,
                'device_manufacturer' => $request->device_manufacturer,
                'app_version' => $request->app_version,
                'device_os' => $request->device_os,
                'device_os_version' => $request->device_os_version,
                'device_user_agent' => $request->device_user_agent,
                'device_type' => $request->device_type,
            ]);

            return response()->json(['response' => 'success', 'message' => 'Device token updated successfully.']);
        }

        // Save device token
        UserDevice::create([
            'app_user_id' => $user->id,
            'push_token' => $request->push_token,
            'device_id' => $request->device_id,
            'device_model' => $request->device_model,
            'device_manufacturer' => $request->device_manufacturer,
            'app_version' => $request->app_version,
            'device_os' => $request->device_os,
            'device_os_version' => $request->device_os_version,
            'device_user_agent' => $request->device_user_agent,
            'device_type' => $request->device_type,
        ]);

        return response()->json(['response' => 'success', 'message' => 'Device token saved successfully.']);
    }
    //save device info
}
