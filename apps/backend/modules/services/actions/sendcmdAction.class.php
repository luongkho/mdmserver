<?php

/**
 * services actions.
 *
 * @package    mdm-server
 * @subpackage services
 * @author     Dung Huynh
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
use Gcs\Repository\DeviceEventRepository;
use Gcs\Repository\ConfigRepository;
use Gcs\Repository\TemplateRepository;
use Gcs\Services\SendCmdAndroidApp;
use Gcs\Services\SendCmdIos;
use Gcs\Services\SendCmdIosApp;
use Gcs\Services\SendCmdWindowsPhone;
use Gcs\Services\SendCmdWindowsPhoneApp;

class sendcmdAction extends BaseJSONAction {

    const SUCCESS                   = "N1001";
    const IOS_PLATFORM              = "ios";
    const IOS_APP_PLATFORM          = "iosapp";
    const ANDROID_PLATFORM          = "android";
    const WINDOWSPHONE_PLATFORM     = "wp";
    const WINDOWSPHONE_APP_PLATFORM = "wpapp";

    /**
     * get command from GUI, save it to device event command and push notification to wake up device.
     * @param  [object] $request
     * @return [array] $response          
     */
    public function execute($request) {
        $platform = $request->getParameter('platform');
        $response = array();
        $log      = \MDMLogger::getInstance();

        switch ($platform) {
            case self::ANDROID_PLATFORM:
                $sendCmdAndroid = new SendCmdAndroidApp();
                $response       = $sendCmdAndroid->sendCmd($this, $request, $log);
                break;
            case self::IOS_PLATFORM:
                $sendCmdIos     = new SendCmdIos();
                $response       = $sendCmdIos->sendCmd($this, $request, $log);
                break;
            case self::IOS_APP_PLATFORM:
                $sendCmdIosApp  = new SendCmdIosApp();
                $response       = $sendCmdIosApp->sendCmd($this, $request, $log);
                break;
            case self::WINDOWSPHONE_PLATFORM:
                $sendCmdWP      = new SendCmdWindowsPhone();
                $response       = $sendCmdWP->sendCmd($this, $request, $log);
                break;
            case self::WINDOWSPHONE_APP_PLATFORM:
                $sendCmdWP      = new SendCmdWindowsPhoneApp();
                $response       = $sendCmdWP->sendCmd($this, $request, $log);
                break;
            default :
                break;
        }
        if (empty($response)) {
            $response = array("error" => array("status" => 0, "msg" => ""), "data" => array(), "msg" => self::SUCCESS);
        }
        MDMLogger::getInstance()->debug('', __LINE__ . "::::" . json_encode($response), array());
        return $this->returnJSON($response);
    }

}
