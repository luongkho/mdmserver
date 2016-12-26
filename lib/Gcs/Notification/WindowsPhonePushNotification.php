<?php

/**
 * Description: class router general notification by Windows Platform
 * Getting access token by Windows Notification Service (WNS)
 * Setup message using push to WNS
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Notification;

class WPNResponse {

    public $message  = '';
    public $error    = false;
    public $httpCode = '';

    function __construct($message, $httpCode, $error = false) {
        $this->message  = $message;
        $this->httpCode = $httpCode;
        $this->error    = $error;
    }

}

use Gcs\Notification\WPNResponse;

class WindowsPhonePushNotification extends Notification {

    private $access_token = '';
    private $sid          = '';
    private $secret       = '';

    const Toast            = 'wns/toast';
    const Badge            = 'wns/badge';
    const Tile             = 'wns/tile';
    const Raw              = 'wns/raw';
    const CONTENT_TYPE_RAW = 'application/octet-stream';
    const CONTENT_TYPE_XML = 'text/xml';

    /**
     * Init function
     */
    function __construct() {
        parent::__construct();
        $this->type = 'wp';


        $wpAuthor     = \sfConfig::get("app_windows_phone_author_data");
        $this->sid    = urlencode($wpAuthor['packaged']);
        $this->secret = urlencode($wpAuthor['secret']);
    }

    /**
     * Get access token using push notification
     * Return error message if exist
     * @return String | boolean
     */
    private function get_access_token() {
        if ($this->access_token != '') {
            return;
        }
        $str    = "grant_type=client_credentials&client_id=" . $this->sid . "&client_secret=" . $this->secret . "&scope=notify.windows.com";
        $url    = "https://login.live.com/accesstoken.srf";
        $ch     = curl_init($url);
        $proxy  = \sfConfig::get("app_command_url_data");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXY, $proxy['proxy']);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output);
        if (isset($output->error)) {
            return $output->error_description;
        }
        if ($output->access_token) {
            $this->access_token = $output->access_token;
        }
        return false;
    }

    /**
     * Build raw data if using message type is "Raw"
     * @return string
     */
    private function build_raw_data() {
//        return base64_encode(openssl_random_pseudo_bytes(30));
        return "Notification";
    }

    /**
     * Build xml data if using message type are Toast, Tile, Badge
     * @param String $type
     * @return String
     */
    private function build_xml_data($type) {
        $wnsTypeArray = explode("/", $type);
        $wnsType      = end($wnsTypeArray) . ".xml";
        $libDir       = \sfConfig::get("sf_lib_dir");
        return file_get_contents($libDir . "/Gcs/Services/xml/" . $wnsType);
    }

    /**
     * Build content message by message type
     * @param String $type
     * @return Array
     */
    private function build_content_type_and_data($type) {
        $xml_data = '';
        if ($type == self::Raw) {
            $content_type = self::CONTENT_TYPE_RAW;
            $xml_data     = $this->build_raw_data();
        } else {
            $content_type = self::CONTENT_TYPE_XML;
            $xml_data     = $this->build_xml_data($type);
        }
        return array(
            'content_type' => $content_type,
            'data'         => $xml_data
        );
    }

    /**
     * Setting header to push notification
     * @param Integer $contentLength
     * @param String $contentType
     * @param String $wnsType
     * @return Array
     */
    private function setupHeaders($contentLength, $contentType, $wnsType) {
        $headers = array(
            "Content-Length: " . $contentLength,
            "X-WNS-Type: " . $wnsType,
            "Authorization: Bearer $this->access_token",
            "Content-Type: " . $contentType
        );
        return $headers;
    }

    /**
     * 
     * @param String $xml_data Message
     * @param Array $uri ChannelURI
     * @param String $type wns type
     * @return Object WPNResponse response status push notification
     */
    public function sendMessage($xml_data, array $uriArr, $type) {
        if ($this->access_token == '') {
            if ($this->get_access_token()) {
                return New WPNResponse($e->getMessage(), 400, true);
            }
        }
        if (is_null($type)) {
            $type = self::Raw;
        }
        $contentTypeData = $this->build_content_type_and_data($type);
        $xml_data        = $contentTypeData['data'];

        $contentType   = $contentTypeData['content_type'];
        $contentLength = strlen($xml_data);
        $headers       = $this->setupHeaders($contentLength, $contentType, $type);
        $uri           = @$uriArr[0];
        $ch            = curl_init($uri);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output        = curl_exec($ch);
        $response      = curl_getinfo($ch);
        curl_close($ch);
        $code          = $response['http_code'];
        if ($code == 200) {
            return New WPNResponse('Successfully sent message', $code);
        } else if ($code == 401) {
            $this->access_token = '';
            return $this->sendMessage($xml_data, array($uri), $type);
        } else if ($code == 410 || $code == 404) {
            return New WPNResponse('Expired or invalid URI', $code, true);
        } else {
            return New WPNResponse('Unknown error while sending message', $code, true);
        }
    }

}
