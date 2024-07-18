<?php

namespace App\Http\Controllers;

// use App\Mail\Payment;

use App\Models\Payment;
use App\Models\User;
use App\Payments\Pesapal;
use App\Traits\MessageTrait;
use App\Traits\UserTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;
use App\Mail\Payment as MailPayment;
use App\Models\Agent;
use App\Models\AppUser;
use App\Models\Booking;
use App\Models\UserAccount;
use App\Models\UserPoint;
use App\Models\Donation;
use App\Models\Notification;
use App\Models\PropertyOwner;
use App\Models\UserDevice;
use App\Services\FirebaseService;

class PaymentController extends Controller
{
    use MessageTrait, UserTrait, MessageTrait;
    
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function sendMessageTest(Request $request)
    {
        try {

            $phone = '256759983853';
            $message = "This is a test message from zippy";
            $this->sendMessage($phone, $message);
            return "Message sent successfully";
        } catch (\Throwable $th) {
            //throw $th;
            return $th->getMessage();
        }
    }

    private function finishPaymentAndSendEmailByView(Payment $transaction, $customer)
    {
        if ($transaction->type == config('status.payment_type.Donation')) {
            //createOrUpdate Donation
            // Donation::updateOrCreate(
            //     ['payment_id' => $transaction->id],
            //     [
            //         'name' => 'Reuse Donation',
            //         'description' => $transaction->description,
            //         'user_id' => $customer->id,
            //         'is_annyomous' => $transaction->is_annyomous,
            //         'status' => config('status.payment_status.completed'),
            //         'payment_id' => $transaction->id,
            //         'amount' => $transaction->amount,
            //         'product_id' => $transaction->product_id,
            //     ]
            // );

            // UserPoint::where("reference", $transaction->reference)->update([

            // ]);
            try {
                Mail::to($customer->email)->send(new MailPayment($customer, 'Your Donation Has Been Successfully Completed', 'Donation Completed'));
            } catch (Throwable $th) {
                // throw $th;
                Log::error($th);
            }

            return view('payments.finish');
        } elseif ($transaction->type == config('status.payment_type.Wallet')) {

            //update the current user account balance get the account balance from the user account and add the amount
            $account = UserAccount::where('user_id', $customer->id)->first();
            UserAccount::where('user_id', $customer->id)->update([
                'account_balance' => $account->account_balance + $transaction->amount,
            ]);

            try {
                Mail::to($customer->email)->send(new MailPayment($customer, 'Your Wallet Balance Has Been Successfully Updated', 'Wallet TopUp Completed'));
            } catch (Throwable $th) {
                // throw $th;
                Log::error($th);
            }

            return view('payments.finish');
        } elseif ($transaction->type == config('status.payment_type.Product')) {
            try {
                Mail::to($customer->email)->send(new MailPayment($customer, 'THe  product payment has been successfully completed', 'Product Payment Completed'));
            } catch (Throwable $th) {
                // throw $th;
                Log::error($th);
            }

            return view('payments.finish');
        } else {
            return view('payments.finish');
        }
    }

    //
    private function finishPaymentAndSendEmailByJSON(Payment $transaction, $customer)
    {
        if ($transaction->type == "Points") {
            UserPoint::where("reference", $transaction->reference)->update([
                'status' => config('status.payment_status.completed'),
                //'payment_id' => $transaction->id,
            ]);
        }
        else{
            //booking payment
            Booking::where("reference", $transaction->reference)->update([
                'status' => config('status.payment_status.completed'),
                'is_approved'=>1
            ]);

            $booking =  Booking::where("reference", $transaction->reference)->first();
            $agent_id = $booking->agent_id;
            $owner_id = $booking->user_id;
            if($agent_id){
                //send message to agent
                $agent = Agent::where('id', $agent_id)->first();
                $message = "$customer->name has successfully completed the booking. Please check the app for more details. Thanks Zippy Team";
                if($agent->phone_number){
                    $this->sendMessage($agent->phone_number, $message);
                }
            }

            if($owner_id){
                //send message to owner
                $owner = PropertyOwner::where('id', $owner_id)->first();
                $message = "$customer->name has successfully completed the booking. Please check the app for more details. Thanks Zippy Team";
                if($owner->phone_number){
                    $this->sendMessage($owner->phone_number, $message);
                }
            }
        }

        $message = "Your Payment Has Been Successfully Completed Please check the app for more details. Thanks Zippy Team";
        if($customer->phone_number){
            $this->sendMessage($customer->phone_number, $message);
        }

        //get agent
         

        if($customer->email){

        try {
            Mail::to($customer->email)->send(new MailPayment($customer, $message, 'Payment Completed'));
        } catch (Throwable $th) {
            // throw $th;
            Log::error($th);
        }

        }
        Notification::create([
            'app_user_id' => $customer->id,
            'type'=>$transaction->type,
            'title'=>"Payment for $transaction->type completed",
            'message'=>"Payment for $transaction->type completed",
        ]);
        // Send push notification
        $userPushToken = UserDevice::where('app_user_id', $customer->id)->first();
        if ($userPushToken) {
            $token = $userPushToken->push_token;
            if ($token) {
                $title = "🎉 Payment Completed 🎉";
                $body = "Payment for $transaction->type has been successfully completed. ✅";
                $data = [
                    'transaction_type' => $transaction->type,
                    'transaction_id' => $transaction->id,
                ];

                $this->firebaseService->sendNotification($token, $title, $body, null, $data);
            }
        }
        // return view('payments.finish');
        return response()->json([
            'status' => 200,
            'message' => 'Transaction completed',
        ]);
    }

    public function finishPayment(Request $request)
    {
        try {
            //code...
            return view('payments.finish');
        } catch (\Throwable $th) {
            //throw $th;
            return view('payments.cancel');
        }
    }


    public function registerIPN(Request $request)
    {
        try {
            //add validation for url is registered
            $request->validate([
                'url' => 'required|string',
            ]);

            return Pesapal::pesapalRegisterIPN($request->url);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th->getMessage());

            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function listIPNS(Request $request)
    {
        try {
            $data = Pesapal::listIPNS();

            return response()->json(['success' => true, 'message' => 'Success', 'response' => $data]);
        } catch (\Throwable $th) {

            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function cancelPayment(Request $request)
    {
        try {
            $payment_reference = $request->input('payment_reference');
            Payment::where('reference', $payment_reference)->update([
                'status' => config('status.payment_status.canceled'),
            ]);

            return view('payments.cancel');
        } catch (\Throwable $th) {
            Log::error($th->getMessage());

            return view('payments.cancel');
        }
    }

    public function completePayment(Request $request)
    {
        try {
            Log::info('===========The call back was called===================================');
            Log::info('Received Response Page');
            Log::info('============The call back was called==================================');
            // Get the parameters from the URL
            $orderTrackingId = $request->input('OrderTrackingId');
            $orderMerchantReference = $request->input('OrderMerchantReference');
            //return $orderMerchantReference;
            $orderNotificationType = $request->input('OrderNotificationType');
            Payment::where('reference', $orderMerchantReference)->update([
                'order_tracking_id' => $orderTrackingId,
                'orderNotificationType' => $orderNotificationType,

            ]);

            $transaction = Payment::where('reference', $orderMerchantReference)->first();
            if (!$transaction) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Transaction not found',
                ]);
            }
            $customer = AppUser::find($transaction->app_user_id);
            $data = Pesapal::transactionStatus($orderTrackingId, $orderTrackingId);
            $payment_method = $data->message->payment_method;

            Log::info('=========================================call back executed=============================================================================================================');
            Log::info("Received Response Page - Order Tracking ID: $orderTrackingId, Merchant Reference: $orderMerchantReference, Notification Type: $orderNotificationType");
            Log::info('==========================================call back executed============================================================================================================');

            if ($data->message->payment_status_description == config('status.payment_status.completed')) {

                //check if the transaction is already completed
                if ($transaction->status == config('status.payment_status.completed')) {
                    return response()->json([
                        'status' => 200,
                        'message' => 'Transaction completed',
                    ]);

                    //return $this->finishPaymentAndSendEmailByJson($transaction, $customer);
                } else {

                    $transaction->update([
                        'status' => config('status.payment_status.completed'),
                        'payment_method' => $payment_method,
                    ]);

                    return $this->finishPaymentAndSendEmailByJson($transaction, $customer);
                }
            }
            else{
               //fail the transactin
               $transaction->update([
                   'status' => config('status.payment_status.failed'),
                   'payment_method' => $payment_method,
               ]);
                return response()->json(['success' => false, 'message' => 'Transaction failed', 'status' => 500]);
            }
        } catch (\Throwable $th) {

            Log::info('===========callback url==================================');
            Log::error($th->getMessage());
            Log::info('============call back url=================================');

            return response()->json(['success' => false, 'message' => $th->getMessage(), 'status' => 500]);
        }
    }

    public function processOrder(Request $request, $amount, $phone, $re)
    {


        try {
            //$amount, $phone, $callback
            $request->validate([
                'amount' => 'required|numeric',
                'phone_number' => 'required|string',
                'callback' => 'required|string',
                'payment_type' => 'required|string', //points
                'cancel_url' => 'required|string',
                'description' => 'required|string',
                'first_name' => 'required|string',
                'second_name' => 'required|string',
                'customer_email' => 'required|string',
                'reference' => 'required|string',

            ]);
            //https://dashboard.zippyug.com/finishPayment
            $amount = $request->input('amount');
            $phone = $request->input('phone_number');
            $callback = "https://dashboard.zippyug.com/finishPayment";
            //$reference = Str::uuid();
            $reference = $request->input('reference');
            $description = $request->input('description') ?? 'Buying points';
            $first_name = $request->input('customer_name');
            $second_name = $request->input('second_name');
            $email = $request->input('customer_email');
            // $customer_id = $getCustomer->id;
            $cancel_url = $request->input('cancel_url');
            //add the payment reference to cancel url
            $cancel_url = "https://dashboard.zippyug.com/cancelPayment" . '?payment_reference=' . $reference;
            $payment_type = $request->input('payment_type');
            $product_id = $request->input('product_id');
            // return $payment_type;
            // return $amount;
            $data = Pesapal::orderProcess($reference, $amount, $phone, $description, $callback, $first_name, $email, $second_name, $cancel_url, $payment_type, 'App', $product_id);


            return response()->json(['success' => true, 'message' => 'Order processed successfully', 'response' => $data]);
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function checkTransactionStatus(Request $request)
    {

        try {
            //code...
            $request->validate([
                'orderTrackingId' => 'required|string',
                'merchantReference' => 'required|string',
            ]);
            $orderTrackingId = $request->input('orderTrackingId');
            $merchantReference = $request->input('merchantReference');
            $data = Pesapal::transactionStatus($orderTrackingId, $merchantReference);

            return response()->json(['success' => true, 'message' => 'Success', 'response' => $data->message->payment_status_description]);
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public function testSendingMessages(Request $request)
    {
        try {
            //code...
            $message = 'Testing sending messages';
            $phoneNumber = '+256759983853';
            $res = $this->sendMessage($phoneNumber, $message);

            return response()->json(['success' => true, 'message' => 'Success', 'response' => $res]);

            return 'success';
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }



    public function getUserPayments(Request $request)
    {
        try {
            //code...
            $limit = $request->input('limit', 100);
            $page = $request->input('page', 1);
            $sortOrder = $request->input('sort_order', 'desc');
            $user_id = $this->getCurrentLoggedUserBySanctum()->id;

            // Add a status filter if 'status' is provided in the request
            $status = $request->input('status');
            $paymentQuery = Payment::where('user_id', $user_id);

            if (!empty($status)) {
                $paymentQuery->where('status', $status);
            }

            $res = $paymentQuery->orderBy('id', $sortOrder)->with([
                'station',
                'currency',
                'customerCard',
                'order',
                'user',
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
}
