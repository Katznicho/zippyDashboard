<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\Property;
use App\Models\PropertyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use App\Mail\Payment as MailNotification;
use App\Models\UserDevice;
use App\Services\FirebaseService;

trait ZippyAlertTrait
{
    use MessageTrait;

    protected $costPercentage = 0.3;
    protected $locationPercentage = 0.3;
    protected $servicesPercentage = 0.1;
    protected $amenitiesPercentage = 0.1;
    protected $roomsPercentage = 0.15;
    protected $bathroomsPercentage = 0.05;
    protected $threshold = 0.7;

    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function zippySearchAlgorithm(Request $request, $user, $userAlert)
    {
        try {
            $category_id = $request->get('category_id', 0);
            $cost = $request->get('cost', 0);
            $latitude = $request->get('latitude', 0);
            $longitude = $request->get('longitude', 0);
            $services = $request->get('services', []);
            $amenities = $request->get('amenities', []);
            $rooms = $request->get('rooms', 0);
            $bathrooms = $request->get('bathrooms', 0);

            $properties = Property::where('category_id', $category_id)->get();

            foreach ($properties as $property) {
                $score = 0.0;

                $costPercentage = $this->calculateCost(intval($property->price), intval($cost));
                $score += $costPercentage;

                $distancePercentage = $this->calculateDistance($property->lat, $property->long, $latitude, $longitude);
                $score += $distancePercentage;

                $roomPercentage = $this->calculateRoomPercentage($property->rooms, $rooms);
                $score += $roomPercentage;

                $bathroomsPercentage = $this->calculateBathroomPercentage($property->bathrooms, $bathrooms);
                $score += $bathroomsPercentage;

                $servicesPercentage = $this->calculateServicesPercentage($property->getServicesIdsAttribute(), $services);
                $score += $servicesPercentage;

                $amenitiesPercentage = $this->calculateAmenitiesPercentage($property->getAmenitiesIdsAttribute(), $amenities);
                $score += $amenitiesPercentage;

                if ($score >= $this->threshold) {
                    $matchScore = floatval($score) * 100.0;
                    $message = "Hello " . $user->name . ",\n\n" . "$property->name matches with your Zippy Alert with score $matchScore.\n\n" . "Regards,\n" . "Zippy Team";

                    $userPushToken = UserDevice::where('app_user_id', $user->id)->first();
                    $token = $userPushToken->push_token ?? null;
                    if ($token) {
                        $title = "Property Match 😎";
                        $body = "$property->name matches with your Zippy Alert with score $matchScore. Check it out now! 😎";
                        $imageUrl = $property->cover_image;
                        $data = [
                            'Followers' => '100',
                            'Property' => $property->name,
                            'Score' => $matchScore,
                        ];

                        $this->firebaseService->sendNotification($token, $title, $body, $imageUrl, $data);
                    }

                    $notification = Notification::create([
                        'app_user_id' => $user->id,
                        'property_id' => $property->id,
                        'type' => 'Property Notification',
                        'title' => "Property Zippy Alert",
                        'message' => "$property->name matches with your Zippy Alert with score $matchScore",
                    ]);

                    PropertyNotification::create([
                        'property_id' => $property->id,
                        'app_user_id' => $user->id,
                        'score' => $score,
                        'match_percentage' => $score,
                        'notification_id' => $notification->id,
                        'is_enabled' => true,
                        'cost_percentage' => $costPercentage,
                        'location_percentage' => $distancePercentage,
                        'services_percentage' => $servicesPercentage,
                        'amenities_percentage' => $amenitiesPercentage,
                        'rooms_percentage' => $roomPercentage,
                        'bathrooms_percentage' => $bathroomsPercentage,
                        'zippy_alert_id' => $userAlert->id,
                    ]);

                    $userAlert->is_active = false;
                    $userAlert->save();

                    if ($user->phone_number) {
                        $this->sendMessage($user->phone_number, $message);
                    }
                    try {
                        Mail::to($user->email)->send(new MailNotification($user, $message, 'Property Match'));
                    } catch (Throwable $th) {
                        Log::error($th);
                    }

                    return true;
                }
            }

            return false;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    // Existing methods (calculateDistance, calculateCost, etc.) remain unchanged
}
