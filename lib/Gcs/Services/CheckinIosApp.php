<?php

/**
 * Description: class for IOS container service
 *  Process enroll for IOS container platform
 *  Save information send by container
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

use Gcs\Services\CheckinAbstract;
use Gcs\Repository\UserRepository;
use Gcs\Repository\DeviceInventoryRepository;

class CheckinIosApp extends CheckinAbstract
{

    // error code
    const UN_ENROLL_CODE = "1010";
    const INVALID_FORMAT_CODE = "1002";
    const REGID_UDID_NULL = "1012";
    // enroll status
    const ENROLLED = 0;

    /**
     * enroll for ios app container
     * @param  [array] $content, [string] $log
     * @return [boolean] 
     */
    public function enroll($controller, $content, $log)
    {
        $response = array();
        $udid = $content['UDID'];
        if (isset($content['Username'])) {
            $username = $content['Username'];
            $userRepository = new UserRepository();
            $userInfo = $userRepository->getUserInfoByUsername($username);
            $deviceInventoryModel = new DeviceInventoryRepository();
            $device = $deviceInventoryModel->getDeviceInventorybyUDID($udid);
            if ($device) {
                $response = $this->_saveIosAppInfo($device, $content, $userInfo, $log);
            } else {
                $response = $this->buildCommand(self::REGID_UDID_NULL);
                \MDMLogger::getInstance()->error('', $log . "Parameter UDID or RegistrationID is invalid", array());
            }
        } else {
            $response = $this->buildCommand(self::INVALID_FORMAT_CODE);
        }
        return $response;
    }

    /**
     * enroll for ios app container
     * @param  [DeviceInventory] $device,[array] $content, [UserInfo] $userInfo,[string] $log
     * @return [array] $response 
     */
    private function _saveIosAppInfo($device, $content, $userInfo, $log)
    {
        $response = array();
        $deviceInventoryModel = new DeviceInventoryRepository();
        // check device is re-enroll or login again.
        if ($device->getDeviceToken() != null && $device->getUserId() != null) {
            $isValidUser = $this->checkUserEnrolled($userInfo, $device, $log);
            if ($isValidUser != null) {
                return $isValidUser;
            }
        }
        $enrollStatus = $device->getEnrollStatus();
        if ($enrollStatus == self::ENROLLED) {
            $deviceInventoryModel->saveAppDeviceToken($device, $content);
            $deviceInventoryModel->saveAppUserInfo($device, $content);
        } else {
            $response = $this->buildCommand(self::UN_ENROLL_CODE);
        }
        return $response;
    }

}
