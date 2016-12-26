<?php

/**
 * Description: class for Windows Phone service
 * Init command and push command to Windows Notification Service.
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

use Gcs\Repository\ConfigRepository;
use \Gcs\Repository\DeviceRepository;
use Gcs\Repository\DeviceEventRepository;
use Gcs\Notification\WindowsPhonePushNotification;

class SendCmdWindowsPhoneApp extends SendcmdAbstract {

    const UN_ENROLL              = "E4004";
    const SUCCESS                = "N1001";
    const PUSH_FAIL              = "E4002";
    const COMMAND_ERROR          = 3;
    const COMMAND_READY          = 0;
    const PUSH_HTTP_CODE_SUCCESS = 200;
    const PUSH_FAIL_CODE         = "2001";
    const LATEST_LOCATION        = "GetLatestLocation"; 

    public function sendCmd($controller, $request, $log) {
        $platform   = $request->getParameter('platform');
        $command    = $request->getParameter('command');
        $deviceData = $request->getParameter('deviceId');
        $userId     = $request->getParameter('userId');
        $profileId  = $request->getParameter('profileId');

        $configRepository = new ConfigRepository();
        $request_type     = $configRepository->getRequestTypeByPlatform($platform);

        // change deviceId from string to array.
        $deviceIds        = explode(",", $deviceData);
        $deviceEventModel = new DeviceEventRepository();
        $response         = array();
        $commandUUID      = null;

        $deviceRes   = new DeviceRepository();
        $deviceArray = $deviceRes->getDeviceList($deviceIds);

        foreach ($deviceArray as $device) {
            $regIds = array();
            if ($device->getEnrollStatus() == 0) {
                $deviceId = $device->getId();
                
                $existed = $this->_checkExistLatestLocation($command, $deviceId, $request_type);
                if($existed){
                    continue;
                }
                $commandUUID = $deviceEventModel->saveCommand($deviceId, $command, self::COMMAND_READY, $userId, $profileId, true, $request_type);
                $regId       = $device->getDeviceToken();
                if (!is_null(trim($regId))) {
                    array_push($regIds, $regId);
                }
                $pushResult = $deviceEventModel->pushNotification($regIds, $platform, null);
                if ($pushResult->httpCode != self::PUSH_HTTP_CODE_SUCCESS) {
                    $response = $this->buildResponse($response, self::PUSH_FAIL);
                    if ($commandUUID != null) {
                        $event = \DeviceEventTable::getInstance()->findOneByCommandUuid($commandUUID);
                        $deviceEventModel->updateNoteAndStatus($event, self::PUSH_FAIL_CODE, self::COMMAND_ERROR);
                    }
                }
            } else {
                $response = $this->buildResponse($response, self::UN_ENROLL);
                continue;
            }
        }

        // response message success
        if (empty($response)) {
            $response = array("error" => array("status" => 0, "msg" => ""), "data" => array(), "msg" => self::SUCCESS);
        }
        \MDMLogger::getInstance()->debug('', __LINE__ . "::::" . json_encode($response), array());
        return $response;
    }
    
    private function _checkExistLatestLocation($command, $deviceId, $request_type)
    {
        $deviceEventModel = new DeviceEventRepository();
        if($command == self::LATEST_LOCATION){
           return $deviceEventModel->checkExistedCommand($deviceId, $request_type);
        }
            
    }

}
