<?php
/**
 * Description: Abstract class for checkin service
 *  Base class for 3 platform: Android, IOS and Windows phone
 *  Abstract function enroll for 3 platform
 *  Getting command error for 3 platform
 *  Save command for 3 platform
 *  Verify enroll status for 3 platform
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

use Gcs\Repository\DeviceEventRepository;
use Gcs\Repository\ConfigRepository;

abstract class CheckinAbstract
{

    // error code
    const PUSH_FAIL_CODE = "2001";
    const ENROLL_OTHER_USER = "1013";
    // command code
    const COMMAND_ERROR = 3;
    const COMMAND_READY = 0;
    // enroll status
    const ENROLLED = 0;

    /**
     * receive request and enroll device.
     * @param  [object] $request
     * @return [array] $response
     */
    abstract protected function enroll($controller, $content, $log);

    /**
     * build command error for response to device.
     * @param  [string] $error
     * @return [array] $response
     */
    public function buildCommand($error)
    {
        $response = array(
            'Status' => '1',
            'Error' => array(
                'ErrorCode' => $error
            )
        );
        return $response;
    }

    /**
     * save get device_info, security_info, installed_ap command and push
     * notification to wake up device.
     * @param  [DeviceInventory] $device, [int]$userId, [string] $pushMagic, [string] $event,
     * [string] $platform, [string]$log
     * @return [boolean] 
     */
    public function saveAndPushCommand($device, $userId, $pushMagic, $event, $platform, $log, $flag = true)
    {
        $deviceId = $device->getId();
        $regId = $device->getRegistrationId();
        // Save device information command to DeviceEvent table.
        $deviceEventModel = new DeviceEventRepository();
        $configRepository = new ConfigRepository();
        $request_type = $configRepository->getRequestTypeByPlatform($platform);

        $commandUuid = $deviceEventModel->saveCommand($deviceId, $event, self::COMMAND_READY, $userId, null, $flag, $request_type);
        // get registrationId and push notification to devices.
        $regIds = array();
        array_push($regIds, $regId);
        $result = $deviceEventModel->pushNotification($regIds, $platform, $pushMagic);
        // save error code when push notification is not successful.
        if ($result != "") {
            $eventDevice = $deviceEventModel->getCommandByCommandUuid($commandUuid);
            $deviceEventModel->updateNoteAndStatus($eventDevice, self::PUSH_FAIL_CODE, self::COMMAND_ERROR);
            \MDMLogger::getInstance()->error('', $log . "Failed to push notification", array());
            return false;
        }
        return true;
    }

    /**
     * validate Udid and registration Id 
     * @param  [string] $udid, [string]regID, [string] $log
     * @return [array] $response
     */
    public function checkUserEnrolled($userInfo, $device, $log)
    {
        $enrollStatus = $device->getEnrollStatus();
        if ($enrollStatus == self::ENROLLED) {
            $userId = $userInfo->getId();
            if ($userId != $device->getUserId()) {
                $response = $this->buildCommand(self::ENROLL_OTHER_USER);
                \MDMLogger::getInstance()->error('', $log . "Device was enrolled by other User", array());
                return $response;
            }
        }
        return null;
    }

}
