<?php

namespace App\Http\Controllers;

use App\Models\UserDevice;
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
            $userPushTokens = UserDevice::all();
            foreach ($userPushTokens as $userPushToken) {
                $token = $userPushToken->push_token;

                $title = $request->input('title') ?? "Good Night! 🌙✨";
                $body = $request->input('body') ?? "It's the weekend! Time to relax, unwind, and have sweet dreams! 😴🌟";
                $data = $request->input('data') ?? [
                    'Reminder' => 'Enjoy your night!',
                ];

                $imageUrl = $request->input('image_url') ?? "https://images.freeimages.com/images/large-previews/d03/victorian-houses-of-san-franci-1542979.jpg?fmt=webp&w=500";
                
                $this->firebaseService->sendNotification($token, $title, $body, $imageUrl, $data);
            }

            return response()->json(['success' => true, 'message' => 'Notification sent successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }
}
?>
