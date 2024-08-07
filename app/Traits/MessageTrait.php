<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

trait MessageTrait
{
    //  $apiKey = config::get('services.africastalking.api_key');
    //send message
    public function sendMessage(string $phoneNumber, string $message)
    {

        $phoneNumber = $this->formatMobileInternational($phoneNumber);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sms.thinkxsoftware.com/smsdashboard/views/api/send_message.php?sender_id=Zippy&phone_number=' . urlencode($phoneNumber) . '&message=' . urlencode($message) . '&api_key=c40946651cbacf254da69cc3c73ab99ae6daa4dc67e4a331fd553ce0222432ad',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // echo $response;


        Log::info($response);


         return $response;
    }

    public function formatMobileInternational($mobile)
    {
        $length = strlen($mobile);
        $m = '256';
        //format 1: +256752665888
        if ($length == 13)
            return $mobile;
        elseif ($length == 12) //format 2: 256752665888
            return "+" . $mobile;
        elseif ($length == 10) //format 3: 0752665888
            return $m .= substr($mobile, 1);
        elseif ($length == 9) //format 4: 752665888
            return $m .= $mobile;

        return $mobile;
    }
}
