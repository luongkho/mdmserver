<?php

/**
 * Description: class for IOS service
 * Init command and push command to Apple service
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */


namespace Gcs\Services;

/**
 * Description of SendCmdIos
 *
 * @author cuongnd.xt
 */
use Gcs\Services\SendcmdAbstract;
use Gcs\Repository\DeviceEventRepository;
use Gcs\Repository\ConfigRepository;
use Gcs\Repository\DeviceRepository;

class SendCmdIos extends SendcmdAbstract {

    const UN_ENROLL              = "E4004";
    const PUSH_FAIL              = "E4002";
    const PUSH_FAIL_CODE         = "2001";
    const COMMAND_ERROR          = 3;
    const COMMAND_READY          = 0;
    const IOS_PLATFORM           = "ios";
    const IOS_APP_PLATFORM       = "iosapp";

    public function sendCmd($controller, $request, $log) {
        // get parameters from GUI
        $command    = $request->getParameter('command');
        $deviceData = $request->getParameter('deviceId');
        $userId     = $request->getParameter('userId');
        $profileId  = $request->getParameter('profileId');
        $platform   = self::IOS_PLATFORM;

        $configRepository = new ConfigRepository();
        $request_type     = $configRepository->getRequestTypeByPlatform($platform);

        // change deviceId from string to array.
        $deviceIds        = explode(",", $deviceData);
        $deviceEventModel = new DeviceEventRepository();
        // save command to database with status 0
        $regIds           = array();
        $response         = array();
        $commandUUID      = null;
        $commandUUIDApp   = null;
        // query to get devices based on device_ids 
        $deviceRep        = new DeviceRepository();
        $deviceArray      = $deviceRep->getDeviceList($deviceIds);
        foreach ($deviceArray as $device) {
            if ($device->getEnrollStatus() == 0) {
                $deviceId    = $device->getId();
                $pushMagic   = $device->getPushMagic();
                $commandUUID = $deviceEventModel->saveCommand($deviceId, $command, self::COMMAND_READY, $userId, $profileId, true, $request_type);

                if ($command == "Unenroll") {
                    $deviceEventModel->unEnroll($deviceId);
                    $request_type_app = $configRepository->getRequestTypeByPlatform(self::IOS_APP_PLATFORM);
                    $commandUUIDApp   = $deviceEventModel->saveCommand($deviceId, "UnenrollApp", self::COMMAND_READY, $userId, $profileId, false, $request_type_app);
                }
                $regId = $device->getRegistrationId();

                if ($regId != "") {
                    array_push($regIds, $regId);
                }

                \MDMLogger::getInstance()->debug('', "Flag True::" . __LINE__ . "::::" . $command . "::::" . print_r($regIds, true) . "::::" . $platform . "::::" . $pushMagic, array());
                $pushResult = $deviceEventModel->pushNotification($regIds, $platform, $pushMagic);

                if ($command == "Unenroll") {
                    $pushResult = $deviceEventModel->pushNotification(array($device->getDeviceToken()), "iosapp", $pushMagic);
                }
                if ($pushResult != "") {
                    $response = $this->buildResponse($response, self::PUSH_FAIL);
                    if ($commandUUID != null) {
                        $event = \DeviceEventTable::getInstance()->findOneByCommandUuid($commandUUID);
                        $deviceEventModel->updateNoteAndStatus($event, self::PUSH_FAIL_CODE, self::COMMAND_ERROR);
                    }
                }
                if ($command == 'Unenroll') {
                    foreach ($deviceArray as $device) {
                        $deviceId = $device->getId();
                        $deviceEventModel->clearRegId($deviceId);
                    }
                }
            } else {
                // device is unenrolled.
                $response = $this->buildResponse($response, self::UN_ENROLL);
            }
            $regIds = array();
        }
        return $response;
    }

}
