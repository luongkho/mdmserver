<?php

/**
 * mdm actions.
 *
 * @package    mdm-server
 * @subpackage mdm
 * @author     Dung Huynh
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
use CFPropertyList\CFPropertyList;
use Gcs\Services\ServerAndroidApp;
use Gcs\Services\ServerWindowsPhone;
use Gcs\Services\ServerWindowsPhoneApp;
use Gcs\Services\ServerIos;
use Gcs\Services\ServerIosApp;

class serverAction extends BaseJSONAction {

    const INVALID_FORMAT       = "1002";
    const DEVICE_UNENROLLED    = "1003";
    const SYSTEM_ERORR_CODE    = "1001";
    const PUSH_FAIL_CODE       = "2001";
    const GET_INFO             = "DeviceInformation";
    const LOCK_DEVICE          = "DeviceLock";
    const UNLOCK_DEVICE        = "DeviceUnlock";
    const INSTALL_PROFILE      = "InstallProfile";
    const SECURITY_INFO        = "SecurityInfo";
    const INSTALLED_APP        = "InstalledApplicationList";
    const REMOVE_PROFILE       = "RemoveProfile";
    const UN_ENROLL            = "Unenroll";
    const CLEAR_PASSCODE       = "ClearPasscode";
    const GET_LOG_INFORMATION  = "GetLogInformation";
    const INSTALLAPPLICATION   = "InstallApplication";
    const GET_INFO_POLLING     = "GetInformationPolling";
    const IDLE_STATUS          = "2";
    const IDLE_STATUS_IOS      = "Idle";
    const ERROR_STATUS         = "1";
    const SUCCESS_STATUS       = "0";
    const SUCCESS_STATUS_IOS   = "Acknowledged";
    const ENROLLED             = 0;
    const READY                = 0;
    const WAITING              = 1;
    const COMPLETE             = 2;
    const ERROR                = 3;
    const LOCATION_GROUP       = "Location";
    const LOCATION_INFOR       = "LocationInformation";
    const IOS_CONTENT_TYPE     = "application/x-apple-aspen-mdm";
    const IOS_APP_CONTENT_TYPE = "application/x-apple-aspen-app";
    const WP_CONTENT_TYPE      = "application/vnd.syncml.dm+xml";
    const WP_APP_CONTENT_TYPE  = "application/json-wpapp";
    const ANDROID              = 1;
    const IOS                  = 2;
    const WINPHONE             = 3;

    /**
     * receive request and response command for each action when device wake up or request to get command.
     * @param  [object] $request
     * @return [array] $response
     */
    public function execute($request) {
        $deviceData  = $request->getContent();
        // log request data json from device.
        $log         = "/mdm/server  " . $request->getRemoteAddress() . "   ";
        $response    = array();
        $contentType = $request->getContentType();
        MDMLogger::getInstance()->debug('', $log . "::LINE::" . __LINE__ . "::Content Type::" . $contentType, array());
        MDMLogger::getInstance()->debug('', $log . "::LINE::" . __LINE__ . "::::" . rtrim($deviceData), array());
        $content     = null;
        $platform    = null;
        switch ($contentType) {
            case self::IOS_APP_CONTENT_TYPE:
                $content               = $this->_getContentIOS($deviceData);
                $serverIOSApp          = new ServerIosApp();
                $response              = $serverIOSApp->callServer($content, $log);
                $platform              = self::IOS;
                break;
            case self::IOS_CONTENT_TYPE:
                $content               = $this->_getContentIOS($deviceData);
                $serverIOS             = new ServerIos();
                $response              = $serverIOS->callServer($content, $log);
                $platform              = self::IOS;
                break;
            case self::WP_CONTENT_TYPE:
                $content               = $this->_getContentWindowsphone($deviceData);
                $serverWindowsPhone    = new ServerWindowsPhone();
                $response              = $serverWindowsPhone->callServer($content, $log);
                $platform              = self::WINPHONE;
                break;
            case self::WP_APP_CONTENT_TYPE:
                $content               = json_decode($deviceData, true);
                $serverWindowsPhoneApp = new ServerWindowsPhoneApp();
                $response              = $serverWindowsPhoneApp->callServer($content, $log);
                $platform              = self::ANDROID;
                break;
            default: // ANDROID_CONTENT_TYPE
                $content               = json_decode($deviceData, true);
                $serverAndroidApp      = new ServerAndroidApp();
                $response              = $serverAndroidApp->callServer($content, $log);
                $platform              = self::ANDROID;
                break;
        }
        MDMLogger::getInstance()->debug('', $log . "::LINE::" . __LINE__ . "::Platform::" . $platform, array());
        MDMLogger::getInstance()->debug('', $log . "::LINE::" . __LINE__ . "::Response::" . print_r($response, true), array());
        return $this->_baseReturn($platform, $response);
    }

    /**
     * Get content data for IOS, work for MDM & APP
     * @param String $deviceData
     * @return Array
     */
    private function _getContentIOS($deviceData) {
        $plist   = new CFPropertyList();
        $plist->parse($deviceData, CFPropertyList::FORMAT_XML);
        $content = $plist->toArray();
        return $content;
    }

    /**
     * get Content data for Windows phone platform, work for MDM.
     * @param String $deviceData
     * @return Array
     */
    private function _getContentWindowsphone($deviceData) {
        $xml     = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $deviceData);
        $xml     = simplexml_load_string($xml);
        $json    = json_encode($xml);
        $content = json_decode($json, true);
        return $content;
    }

    /**
     * return command based on flatform of devices
     * @param  [string] $platform
     * @return [array] $response
     */
    private function _baseReturn($platform, $response) {
        switch ($platform) {
            case self::IOS:
                return $this->returnPLIST($response);
            case self::ANDROID:
                return $this->returnJSON($response);
            default:
                return $this->returnXML($response);
        }
    }

}
