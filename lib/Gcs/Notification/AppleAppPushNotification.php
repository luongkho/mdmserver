<?php

/**
 * Description: class for IOS container push notification service
 * Getting message and push to Apple Notification service
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Notification;

class AppleAppPushNotification extends Notification {

    public function __construct() {
        parent::__construct();
        $this->type = 'iosapp';
    }

    /**
     * Override method send message to Apple Notification service
     * @param String $message
     * @param array $deviceTokens
     * @param String $pushMagic
     * @return string $result
     */
    protected function sendMessage($message, array $deviceTokens, $pushMagic) {
        $badge          = 0;
        $sound          = 'default';
//    $payload = array();
//    $payload['aps'] = array();
        $payload['aps'] = array(
            'alert'             => 'Notification',
            'sound'             => 'default',
            'content-available' => 1
        );
//    $payload['aps'] = $message;
        $payload        = json_encode($payload);
        $apns_url       = null;
        $apns_cert      = null;
        $apns_port      = 2195;
        if (\sfConfig::get('sf_environment') == 'dev') {
            $apns_url    = 'gateway.push.apple.com';
            $certificate = \sfConfig::get("app_push_notification_iosapp");
            $apns_cert   = dirname(dirname(__FILE__)) . $certificate['dir'];
        } else {
            $apns_url    = 'gateway.push.apple.com';
            $certificate = \sfConfig::get("app_push_notification_iosapp");
            $apns_cert   = dirname(dirname(__FILE__)) . $certificate['dir'];
        }
        // Put your private key's passphrase here:
        $passphrase     = 'gcsvn123';
        $stream_context = stream_context_create();
        stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert);
        stream_context_set_option($stream_context, 'ssl', 'passphrase', $passphrase);
        $apns           = stream_socket_client('ssl://' . $apns_url . ':' . $apns_port, $error, $error_string, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $stream_context);
        $result         = null;
        $push           = null;
        if (!$apns) {
            //Can not open socket to apple server
            $result = '1';
        } else {
            foreach ($deviceTokens as $deviceToken) {
                $token        = unpack('H*', base64_decode($deviceToken, true));
                //$apns_message = chr(0) . chr(0) . chr(32) . pack('H*', trim($deviceToken)) . chr(0) . chr(strlen($payload)) . $payload;
                $apns_message = chr(0) . chr(0) . chr(32) . pack('H*', trim($token[1])) . chr(0) . chr(strlen($payload)) . $payload;
                $result       = fwrite($apns, $apns_message, strlen($apns_message));
            }
        }
        if (!$push) {
            $result = '';
        } else {
            $result = '1';
        }
        @socket_close($apns);
        @fclose($apns);
        return $result;
    }

}
