<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    

    public function sendNotification(Request $request)
    {
         try {
            $token = $request->input('token') ?? "dnaXYBhaQQeJ3_iJDxJE52:APA91bF2CIy3yht3phoslR-JDWrTlh8r0Gj2H3peHWxaCu9lLi6fMtLtKazgGzOFYBMpHGSNGuN816yKflgadEeGoWL4gVlw4voO-RTGnaZvGBymuyRFgn8OQv6RS2D6szGjMVPQVJe6";
        $title = $request->input('title') ?? "Property Match 😎";
        $body = $request->input('body') ?? "We have found a match for your property. Check it out now! 😎";
        $data = $request->input('data') ?? [
            'Followers' => '100',
            'Followers' => '100',
            'Followers' => '100',
        ];

        $imageUrl = $request->input('image_url') ?? "https://images.freeimages.com/images/large-previews/d03/victorian-houses-of-san-franci-1542979.jpg?fmt=webp&w=500";

        $sent = $this->firebaseService->sendNotification($token, $title, $body, $imageUrl, $data);

        if ($sent) {
            return response()->json(['success' => true, 'message' => 'Notification sent successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to send notification.'], 500);
        }
         } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['success'=>false , 'message'=>$th->getMessage()]);
         }

    }
}
