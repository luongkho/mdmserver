<?php
/**
 * Description: class for Android service
 *  Process enroll for Android platform
 *  And, Update or Ignore data when device enroll
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

use Gcs\Services\CheckinAbstract;
use Gcs\Repository\DeviceInventoryRepository;
use Gcs\Repository\UserRepository;

class CheckinAndroid extends CheckinAbstract
{

    // error code
    const UN_ENROLL_CODE = "1010";
    const SYSTEM_ERORR_CODE = "1001";
    const INVALID_FORMAT_CODE = "1002";
    const REGID_UDID_NULL = "1012";
    // enroll status
    const ENROLLED = 0;
    const UNENROLL = 1;
    const ALLOW_REENROLL = 2;
    const PLATFORM_ANDROID = "android";
    const GETINFO = "DeviceInformation";
    const SECURITY = "SecurityInfo";
    const INSTALLED_APP = "InstalledApplicationList";

    /**
     * enroll Android platform.
     * @param  [array] $content, [UserInfo] $userInfo,[string] $log
     * @return [array] $response 
     */
    public function enroll($controller, $content, $log)
    {
        $response = array();
        if (isset($content['MessageType']) && isset($content['Username']) && isset($content['UDID']) && isset($content['RegistrationID'])) {
            try {
                $username = $content['Username'];
                $udid = $content['UDID'];
                $regId = $content['RegistrationID'];
                $userRepository = new UserRepository();
                $userInfo = $userRepository->getUserInfoByUsername($username);

                // validate request from device.
                $isValid = $this->_validateRequest($udid, $regId, $log);
                if ($isValid != null) {
                    return $isValid;
                }
                $response = $this->_saveOrUpdateDevice($content, $userInfo, $log);
            } catch (Exception $e) {
                // log json data response to device
                $response = $this->buildCommand(self::SYSTEM_ERORR_CODE);
                \MDMLogger::getInstance()->error('', $log . $e, array());
            }
        } else {
            // log json data response to device
            $response = $this->buildCommand(self::INVALID_FORMAT_CODE);
            \MDMLogger::getInstance()->error('', $log . "Invalid message format", array());
        }
        // log json data response to device
        \MDMLogger::getInstance()->debug('', $log . json_encode($response), array());
        return $response;
    }

    /**
     * validate Udid and registration Id 
     * @param  [string] $udid, [string]regID, [string] $log
     * @return [array] $response
     */
    private function _validateRequest($udid, $regId, $log)
    {
        if ($udid == "" || $regId == "") {
            $response = $this->buildCommand(self::REGID_UDID_NULL);
            \MDMLogger::getInstance()->error('', $log . "Parameter UDID or RegistrationID is invalid", array());
            \MDMLogger::getInstance()->debug('', $log . json_encode($response), array());
            return $response;
        }
        return null;
    }

    /**
     * save or update device to device inventory table.
     * @param  [array] $content, [UserInfo] $userInfo,[string] $log
     * @return [array] $response 
     */
    private function _saveOrUpdateDevice($content, $userInfo, $log)
    {
        $response = array();
        $userId = $userInfo->getId();
        $udid = $content['UDID'];
        $regId = $content['RegistrationID'];
        $deviceInventoryModel = new DeviceInventoryRepository();
        $device = $deviceInventoryModel->getDeviceInventorybyUDID($udid);
        $flag = false;
        if (!$device) {
            $flag = true;
            $device = new \DeviceInventory();
        } else {
            $isValidUser = $this->checkUserEnrolled($userInfo, $device, $log);
            if ($isValidUser != null) {
                return $isValidUser;
            }
            //check enroll_status of device.
            $enrollStatus = $device->getEnrollStatus();
            switch ($enrollStatus) {
                case self::ENROLLED: // device is enrolled
                    if ($device->getRegistrationId() != $regId) {
                        $deviceInventoryModel->updateRegId($device, $regId);
                    }
                    $response = array('Status' => '0');
                    break;
                case self::UNENROLL: // device is unenrolled
                    $response = $this->buildCommand(self::UN_ENROLL_CODE);
                    \MDMLogger::getInstance()->info('', $log . "Device is unenrolled", array());
                    break;
                default: // enroll_status = 2 (allow re-enroll)
                    $flag = true;
                    break;
            }
        }
        //enroll/re-enroll device
        if ($flag == true) {
            // update information to device inventory table.
            $deviceInventoryModel->updateDeviceInventory($device, $regId, $userId, $udid, $content, 1);
            // Save information command to DeviceEvent table.
            $this->saveAndPushCommand($device, $userId, null, self::GETINFO, self::PLATFORM_ANDROID, $log);
            $this->saveAndPushCommand($device, $userId, null, self::SECURITY, self::PLATFORM_ANDROID, $log);
            $this->saveAndPushCommand($device, $userId, null, self::INSTALLED_APP, self::PLATFORM_ANDROID, $log);
            // response status success.
            $response = array('Status' => '0');
        }
        return $response;
    }

}
