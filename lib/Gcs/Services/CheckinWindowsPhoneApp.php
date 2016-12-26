<?php

/**
 * Description: class for Windows Phone container service
 *  Process enroll for Windows Phone container platform
 *  Verify enroll status then update or ignore data
 *  Return status enroll to container.
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

use Gcs\Services\CheckinAbstract;
use Gcs\Repository\DeviceInventoryRepository;
use Gcs\Repository\EnrollWPRepository;
use Gcs\Repository\UserRepository;
use Gcs\Repository\DeviceEventRepository;

class CheckinWindowsPhoneApp extends CheckinAbstract
{

    // error code
    const UN_ENROLL_CODE = "1010";
    const PUSH_FAIL_CODE = "2001";
    const INVALID_USER_CODE = "0010";
    const SYSTEM_ERORR_CODE = "1001";
    const INVALID_FORMAT_CODE = "1002";
    const REGID_UDID_NULL = "1012";
    const ENROLL_FAIL = "1011";
    const ENROLL_OTHER_USER = "1013";
    const COMMAND_ERROR = 3;
    const COMMAND_READY = 0;
    // enroll status
    const ENROLLED = 0;
    const UNENROLL = 1;
    const ALLOW_REENROLL = 2;
    const AUTHENTICATE = "Authenticate";
    const RECEIVE_HARDWAREID = "ReceiveHardwareId";
    const EXPIRED = 0;
    const WAITING_MDM = 1;
    const ENROLLED_WP = 3;
    const WP_PUSH = "wp";

    /**
     * Loading pakaged openssl then general Self Signed from CSR
     * @return String
     */
    public function enroll($controller, $content, $log)
    {
        $response = array();
        if (isset($content['MessageType'])) {
            switch ($content['MessageType']) {
                case self::AUTHENTICATE:
                    $response = $this->_authenticate($content, $log);
                    break;
            }
        }
        // log json data response to device
        \MDMLogger::getInstance()->debug('', $log . json_encode($response), array());
        return $response;
    }

    /**
     * receive HardwareId from app container.
     * @param  [array] $content
     * @return [array] $response
     */
    private function _authenticate($content, $log)
    {
        $response = array();
        if (isset($content['Username']) && isset($content['HardwareId']) && isset($content['ChannelURI'])) {
            try {
                $username = $content['Username'];
                $hardwareId = $content['HardwareId'];
                $channelUri = $content['ChannelURI'];
                // validate request from device.
                $isValid = $this->_validateRequest($hardwareId, $channelUri, $log);
                if ($isValid != null) {
                    return $isValid;
                }
                $userRepository = new UserRepository();
                $userInfo = $userRepository->getUserInfoByUsername($username);
                $response = $this->updateEnrollWPApp($hardwareId, $userInfo, $channelUri, $log);
            } catch (Exception $e) {
                // log json data response to device
                $response = $this->buildCommand(self::SYSTEM_ERORR_CODE);
                \MDMLogger::getInstance()->error('', $log . $e, array());
            }
        } else {
            // log json data response to device
            $response = $this->buildCommand(self::INVALID_FORMAT_CODE);
            \MDMLogger::getInstance()->error('', __LINE__ . $log . "Invalid message format", array());
        }
        // log json data response to device
        \MDMLogger::getInstance()->debug('', $log . json_encode($response), array());
        return $response;
    }

    /**
     * validate channelUri and hardware Id 
     * @param  [string] $channelUri, [string]$hardwareId, [string] $log
     * @return [array] $response
     */
    private function _validateRequest($hardwareId, $channelUri, $log)
    {
        if ($hardwareId == "" || $channelUri == "") {
            $response = $this->buildCommand(self::REGID_UDID_NULL);
            \MDMLogger::getInstance()->error('', $log . "Parameter HardwareID or RegistrationID is invalid", array());
            \MDMLogger::getInstance()->debug('', $log . json_encode($response), array());
            return $response;
        }
        return null;
    }

    /**
     * validate channelUri and hardware Id 
     * @param  [string] $hardwareId, [UserInfo]$userInfo, [string] $channelUri,[string] $udid, [string] $log
     * @return [array] $response
     */
    private function _checkEnrollStatus($hardwareId, $userInfo, $channelUri, $udid, $log)
    {
        $response = array();
        $deviceInventory = new DeviceInventoryRepository();
        $device = $deviceInventory->getDeviceInventorybyHardwareId($hardwareId);
        $flag = false;
        if (!$device) {
            $device = new \DeviceInventory();
            $device->setEnrollStatus(2);
            $deviceInventory->updateHardwareId($device, $userInfo->getId(), $udid);
            $flag = true;
        } else {
            $isValidUser = $this->_checkUserEnrolled($userInfo, $device, $log);
            if ($isValidUser != null) {
                return $isValidUser;
            }
            //check enroll_status of device.
            $enrollStatus = $device->getEnrollStatus();
            switch ($enrollStatus) {
                case self::ENROLLED: // device is enrolled
                    $flag = true;
                    break;
                case self::UNENROLL: // device is unenrolled
                default: // enroll_status = 2 (allow re-enroll)
                    $response = $this->buildCommand(self::UN_ENROLL_CODE);
                    \MDMLogger::getInstance()->info('', $log . "Device is unenrolled", array());
                    break;
            }
        }
        if ($flag) {
            if ($device->getDeviceToken() != $channelUri) {
                $deviceInventory->updateDeviceToken($device, $channelUri);
            }
            $response = array('Status' => '0', 'UDID' => $device->getUdid());
        }
        return $response;
    }

    /**
     * update info to Enroll_wp table
     * @param  [string] $hardwareId, [array] $userInfo,[string] $channelUri
     * @return boolean
     */
    public function updateEnrollWPApp($hardwareId, $userInfo, $channelUri, $log)
    {
        $response = array();
        $enrollRepository = new EnrollWPRepository();
        $userId = $userInfo->getId();
        // get username and password from request of device.
        $enrollWP = $enrollRepository->getDeviceByUserIDApp($userId, $hardwareId);
        if (!$enrollWP) {
            $enrollWP = new \EnrollWp();
        }

        $enrollRepository->updateInfoEnrollWP($enrollWP, $userId, null, $hardwareId, self::WAITING_MDM);
        $enrollRepository->updateChannelUriApp($hardwareId, $channelUri);
        if ($enrollWP->getStatus() == self::ENROLLED_WP) {
            $response = $this->_checkEnrollStatus($hardwareId, $userInfo, $channelUri, $enrollWP->getUdid(), $log);
            $event = new DeviceEventRepository();
            if ($response['Status'] == 0) {
                $event->pushNotification(array($enrollWP->getChanneluriMdm()), self:: WP_PUSH, null);
            }
        } else {
            $response = $this->buildCommand(self::UN_ENROLL_CODE);
            \MDMLogger::getInstance()->info('', $log . "Device is unenrolled", array());
        }
        return $response;
    }

    /**
     * validate Udid and registration Id 
     * @param  [string] $udid, [string]regID, [string] $log
     * @return [array] $response
     */
    private function _checkUserEnrolled($userInfo, $device, $log)
    {
        $enrollStatus = $device->getEnrollStatus();
        if ($enrollStatus == self::ENROLLED) {
            $userId = $userInfo->getId();
            if ($userId != $device->getUserId()) {
                $response = array(
                    'Status' => '1',
                    'Error' => array(
                        'ErrorCode' => self::ENROLL_OTHER_USER
                    ),
                    'UDID' => $device->getUdid()
                );
                \MDMLogger::getInstance()->error('', $log . "Device was enrolled by other User", array());
                return $response;
            }
        }
        return null;
    }

}
