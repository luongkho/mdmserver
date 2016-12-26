<?php

/**
 * Description: class for Android push notification service
 * Getting message and push to Google Notification service
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Notification;

class GooglePushNotification extends Notification {

    const GOOGLE_API_KEY = 'AIzaSyAwmDHEoWFw5nIpnRWc6SH6bMr7AyNW-xc';
    const GCS_URL        = 'https://android.googleapis.com/gcm/send';

    public function __construct() {
        parent::__construct();
        $this->type = 'android';
    }

    /**
     * Override method send message to Google Notification service
     * @param String $message
     * @param array $tokens
     * @param String $pushMagic
     * @return string $result
     */
    protected function sendMessage($message, array $tokens, $pushMagic) {

        $fields  = array(
            'registration_ids' => $tokens,
            'data'             => array('message' => $message),
        );
        $headers = array(
            'Authorization: key=' . self::GOOGLE_API_KEY,
            'Content-Type: application/json'
        );


        // Open connection
        $ch    = curl_init();
        $proxy = \sfConfig::get("app_command_url_data");
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, self::GCS_URL);
        curl_setopt($ch, CURLOPT_PROXY, $proxy['proxy']);
//        if(AppSetting::APPLE_DEVELOP_MODE)
//        {
//            curl_setopt($ch, CURLOPT_PROXY, self::PROXY_URL);
//        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result    = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        $header_size   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body          = substr($result, $header_size);
//		$json_response = json_decode($body);
        $error         = "";
        $json_response = json_decode($result);
        if ($json_response != null) {
            //error_log($result);
            if ($json_response->success === 0) {
                $error = "1";
            } else {
                $error = "";
            }
        } else {
            $error = "1";
        }
        // Close connection
        curl_close($ch);
        return $error;
    }

}
