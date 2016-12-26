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
use Gcs\Services\CheckinWindowsPhone;
use Gcs\Services\CheckinWindowsPhoneApp;
use Gcs\Services\CheckinIos;
use Gcs\Services\CheckinIosApp;
use Gcs\Services\CheckinAndroid;

class checkinAction extends BaseJSONAction
{

    const IOS_CONTENT_TYPE = "application/x-apple-aspen-mdm-checkin";
    const IOSAPP_CONTENT_TYPE = "application/x-apple-aspen-app-checkin";
    const WP_CONTENT_TYPE = "application/soap+xml";
    const WP_APP_CONTENT_TYPE = "application/json-wpapp";

    /**
     * receive request and enroll device.
     * @param  [object] $request
     * @return [array] $response
     */
    public function execute($request)
    {
        $deviceData = $request->getContent();
        $contentType = $request->getContentType();
        // log json data from device
        $log = "/mdm/checkin  " . $request->getRemoteAddress() . "   ";
        MDMLogger::getInstance()->debug('', __LINE__ . $log . rtrim($deviceData), array());
        switch ($contentType) {
            case self::IOSAPP_CONTENT_TYPE:
                $plist = new CFPropertyList();
                $plist->parse($deviceData, CFPropertyList::FORMAT_XML);
                $content = $plist->toArray();
                $checkinIosApp = new CheckinIosApp();
                $result = $checkinIosApp->enroll($this, $content, $log);
                $response = $this->returnPLIST($result);
                break;
            case self::IOS_CONTENT_TYPE:
                $plist = new CFPropertyList();
                $plist->parse($deviceData, CFPropertyList::FORMAT_XML);
                $content = $plist->toArray();
                $checkinIos = new CheckinIos();
                $result = $checkinIos->enroll($this, $content, $log);
                $response = $this->returnPLIST($result);
                break;
            case self::WP_CONTENT_TYPE:
                //TO-DO: enroll for window phone device.
                $content = $deviceData;
                $checkinWindowsPhone = new CheckinWindowsPhone();
                $response = $checkinWindowsPhone->enroll($this, $content, $log);
                break;
            case self::WP_APP_CONTENT_TYPE:
                //TO-DO: enroll for window phone device.
                $content = json_decode($deviceData, true);
                $checkinWindowsPhoneApp = new CheckinWindowsPhoneApp();
                $result = $checkinWindowsPhoneApp->enroll($this, $content, $log);
                $response = $this->returnJSON($result);
                break;
            default: // ANDROID_CONTENT_TYPE
                $content = json_decode($deviceData, true);
                $checkinAndroid = new CheckinAndroid();
                $result = $checkinAndroid->enroll($this, $content, $log);
                $response = $this->returnJSON($result);
                break;
        }
        return $response;
    }

}
