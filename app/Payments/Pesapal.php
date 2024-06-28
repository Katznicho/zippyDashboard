<?php

namespace App\Payments;

use App\Models\Payment;
use Illuminate\Support\Facades\Config;

class Pesapal
{
    protected static $pesapalBaseUrl = 'https://pay.pesapal.com/v3';

    protected static $body;

    protected static $headers;

    protected static $response;

    protected static $options;

    protected static $manager;

    protected static $consumerKey;

    protected static $consumerSecret;

    public static function loadConfig()
    {
        self::$consumerKey = Config::get('services.pesapal.consumer_key');
        self::$consumerSecret = Config::get('services.pesapal.consumer_secret');
    }

    public static function pesapalBaseUrl()
    {
        try {
            //code...
            return self::$pesapalBaseUrl;
        } catch (\Throwable $th) {
            //throw $th;

            return $th->getMessage();
        }
    }

    public static function pesapalAuth()
    {

        try {
            //code...
            self::loadConfig();
            $url = self::$pesapalBaseUrl.'/api/Auth/RequestToken';
            $headers = ['Content-Type' => 'application/json', 'accept' => 'application/json'];
            $body = json_encode([
                'consumer_key' => self::$consumerKey,
                'consumer_secret' => self::$consumerSecret,
            ]);

            $data = Curl::PostToken($url, $headers, $body);
            $data = json_decode(json_encode($data));

            return $data;
        } catch (\Throwable $th) {
            //throw $th;
            return $th->getMessage();
        }
    }

    public static function pesapalRegisterIPN(string $ipnUrl)
    {
        //return $url;
        try {

            //code...
            $token = self::pesapalAuth();

            if (! $token->success) {
                throw new \Exception('Failed to obtain Token');
            }

            $url = self::$pesapalBaseUrl.'/api/URLSetup/RegisterIPN';
            $headers = ['Content-Type' => 'application/json', 'accept' => 'application/json', 'Authorization' => 'Bearer '.$token->message->token];

            $body = json_encode([
                'url' => $ipnUrl,
                'ipn_notification_type' => 'GET',
            ]);

            $data = Curl::Post($url, $headers, $body);
            $data = json_decode(json_encode($data));

            return $data;
        } catch (\Throwable $th) {
            //throw $th;

            return $th->getMessage();
        }
        //18213
    }

    public static function listIPNS()
    {
        try {
            //code...
            $token = self::pesapalAuth();

            if (! $token->success) {
                throw new \Exception('Failed to obtain Token');
            }

            $url = self::$pesapalBaseUrl.'/api/URLSetup/GetIpnList';
            $headers = ['Content-Type' => 'application/json', 'accept' => 'application/json', 'Authorization' => 'Bearer '.$token->message->token];

            $data = Curl::Get($url, $headers);
            $data = json_decode(json_encode($data));

            return $data;
        } catch (\Throwable $th) {
            //throw $th;
            return $th->getMessage();
        }
    }

    public static function orderProcess($reference, $amount, $phone, $description, $callback, $first_name, $email, $second_name, $cancel_url, $type, $mode = 'App', $product_id = null)
    {
        try {
            //code...
            $token = self::pesapalAuth();
            $payload = json_encode([
                'id' => $reference,
                'currency' => 'UGX',
                'amount' => $amount,
                'description' => $description,
                'redirect_mode' => 'PARENT_WINDOW',
                'callback_url' => $callback,
                'call_back_url' => $cancel_url,
                'notification_id' => '87564655-8bf0-4a7e-9b6e-dd628ff94291',
                'billing_address' => [
                    'phone_number' => $phone,
                    'first_name' => $first_name,
                    'last_name' => $second_name,
                    'email' => $email,

                ],
            ]);

            if (! $token->success) {
                throw new \Exception('Failed to obtain Token');
            }
            $url = self::$pesapalBaseUrl.'/api/Transactions/SubmitOrderRequest';
            $headers = ['Content-Type' => 'application/json', 'accept' => 'application/json', 'Authorization' => 'Bearer '.$token->message->token];
            $data = Curl::Post($url, $headers, $payload);

            $data = json_decode(json_encode($data));

            return $data;
        } catch (\Throwable $th) {

            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }

    public static function transactionStatus(string $oderTrackingId, string $oderMerchantReference)
    {

        try {
            //code...
            $transId = $oderTrackingId;
            // $merchant = $oderMerchantReference;
            if (! isset($transId) || empty($transId)) {

                throw new \Exception('Missing Transaction ID');
            }

            $token = self::pesapalAuth();
            if (! $token->success) {
                return response()->json(['success' => false, 'message' => 'Failed to obtain Token', 'response' => $token]);
            }

            $url = self::$pesapalBaseUrl."/api/Transactions/GetTransactionStatus?orderTrackingId={$transId}";
            $headers = ['Content-Type' => 'application/json', 'accept' => 'application/json', 'Authorization' => 'Bearer '.$token->message->token];
            $data = Curl::Get($url, $headers);

            $data = json_decode(json_encode($data));

            return $data;
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }
    }
}
