<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Currency;
use App\Models\PaymentPeriod;
use App\Models\Property;
use App\Models\PropertyStatus;
use App\Models\Service;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    //
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

    public function  getAllCategories(Request $request)
    {
        try {
            //code...
            $categories =  Category::OrderBy('id', 'desc')->get();
            return response()->json(
                [
                    'response' => "success",
                    "data" => $categories
                ],
                201
            );
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function getAllPropertyStatuses(Request $request)
    {
        try {
            $propertyStatus =  PropertyStatus::all();
            return response()->json(
                [
                    'response' => "success",
                    "data" => $propertyStatus
                ],
                201
            );
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function getAllCurrencies(Request $request)
    {
        try {
            $currencies =  Currency::all();
            return response()->json(
                [
                    'response' => "success",
                    "data" => $currencies
                ],
                201
            );
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function getAllPaymentPeriods(Request $request)
    {
        try {
            $paymentPeriods =  PaymentPeriod::all();
            return response()->json(
                [
                    'response' => "success",
                    "data" => $paymentPeriods
                ],
                201
            );
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['response' => 'failure', 'message' => $th->getMessage()]);
        }
    }

    public function getAllPropertiesByPagination(Request $request)
    {
        try {
            //code...
            $limit = $request->input('limit', 100);
            $page = $request->input('page', 1);
            $sortOrder = $request->input('sort_order', 'desc');


            $property = Property::orderBy('id', $sortOrder)
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

    public function getPropertyCommentsByIdAndPaginated(Request $request){
        try {
             $request->validate([
                 'property_id' => 'required'
             ]);
              $limit = $request->input('limit', 100);
              $page = $request->input('page', 1);
              $sortOrder = $request->input('sort_order', 'desc');

              $comments = Comment::where('property_id', $request->property_id)
              ->orderBy('id', $sortOrder)
              ->with([
                'appUser'
              ])
              ->paginate($limit, ['*'], 'page', $page);

              $response = [
                  "data" => $comments->items(),
                  "pagination" => [
                      "total" => $comments->total(),
                      "current_page" => $comments->currentPage(),
                      "last_page" => $comments->lastPage(),
                      "per_page" => $comments->perPage(),
                  ]
                  ];

            return response()->json(['response' => "success", 'data' => $response], 200);

            //  $comments = Comment::where('property_id', $request->property_id)->get();

             //return response()->json(['success' => true, 'data' => $comments, 'message' => 'Property comments successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
            
        }

    }
}
