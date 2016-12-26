<?php

/**
 * Description: class for Android service
 * General command and return to Google service by command request
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

use Gcs\Services\ServerAbstract;
use Gcs\Repository\DeviceEventRepository;
use Gcs\Repository\DeviceInventoryRepository;

class ServerAndroidApp extends ServerAbstract {

    const INVALID_FORMAT                = "1002";
    const GET_INFO                      = "DeviceInformation";
    const LOCK_DEVICE                   = "DeviceLock";
    const INSTALL_PROFILE               = "InstallProfile";
    const SECURITY_INFO                 = "SecurityInfo";
    const INSTALLED_APP                 = "InstalledApplicationList";
    const REMOVE_PROFILE                = "RemoveProfile";
    const GET_LOG_INFORMATION           = "GetLogInformation";
    const IDLE_STATUS                   = "2";
    const SUCCESS_STATUS                = "0";
    const WAITING                       = 1;
    const COMPLETE                      = 2;
    const ERROR                         = 3;
    const ANDROID_APP_CONTENT_TYPE_NAME = "android";

    public function callServer($content, $log) {
        $log = $this->getLogPosition(__FILE__);
        try {
            $response = array();
            // receive information from device reply on time scheduler  
            if (isset($content['MessageType'])) {
                $this->receiveInformation($content);
            } else {
                // check the message response is invalid format
                if (!isset($content['UDID']) || !isset($content['Status'])) {
                    $response = $this->errorCommand($response, self::INVALID_FORMAT);
                    \MDMLogger::getInstance()->error('', $log . "Invalid message format", array());
                    \MDMLogger::getInstance()->debug('', $log . json_encode($response), array());
                    return $response;
                }
                $udid               = $content['UDID'];
                $status             = $content['Status'];
                // get device based on udid and check it is existed in database.
                $deviceInventoryRes = new DeviceInventoryRepository();
                $device             = $deviceInventoryRes->getDeviceInventorybyUDID($udid);
                $isValid            = $this->validateDevice($device);

                // special case for get device information
                $isSpecialEvent = $this->validateSpecEvent($content);
                // special case for unenroll
                $isUnenroll     = $this->skipUnenroll($device);
                if ($isValid || $isUnenroll || $isSpecialEvent) {
                    // check requests after device wake up. 
                    if ($status == self::IDLE_STATUS) {
                        $response = $this->_sendCommand($device, $response);
                        \MDMLogger::getInstance()->debug('', __LINE__ . "::::" . $log . json_encode($response), array());
                        return $response;
                    } else {
                        // check request after device return the result when executing.
                        if (isset($content['CommandUUID']) && isset($content['UDID']) && isset($content['Status'])) {
                            //get comment content based on command id.
                            $commandUuid = $content['CommandUUID'];
                            $this->_handleResponse($commandUuid, $status, $content, $device);
                        } else {
                            // Invalid message format
                            \MDMLogger::getInstance()->error('', $log . "Invalid message format", array());
                            \MDMLogger::getInstance()->debug('', $log . json_encode($response), array());
                            return $response;
                        }
                    }
                } else {
                    // Device is unenrrolled
                    \MDMLogger::getInstance()->info('', $log . "Device is unenrrolled", array());
                    \MDMLogger::getInstance()->debug('', $log . json_encode($response), array());
                    return $response;
                }
            }
        } catch (Exception $e) {
            \MDMLogger::getInstance()->error('', $log . $e, array());
        }
        \MDMLogger::getInstance()->debug('', $log . json_encode($response), array());
        return $response;
    }

    /**
     * build get information command and response to device.
     * @param  [device_event] $eventContent, [string] $platform
     * @return [array] $response
     */
    private function _getInformationCommand($eventContent) {
        // information of device.
        $queries  = "DeviceName,IMEI,Manufacturer,Model,OSVersion,";
        $queries.= "BluetoothMAC,CurrentCountry,";
        $queries.= "CurrentNetwork,DataRoamingEnabled,DeviceType,";
        $queries.= "EasIdentifier,HomeCountry,HomeNetwork,HotspotEnabled,ICCID,";
        $queries.= "IsStoreAccountActive,IsNotDisturbActive,";
        $queries.= "OperatorName,PhoneNumber,ProductName,SerialNumber,UDID,WiFiMAC,";
        $queries.= "AvailableDeviceCapacity,DeviceCapacity,StorageName,Encrypted,CurrentDataRoaming";
        $queries.= "IsDoNotDisturbInEffect,EASDeviceIdentifier,PersonalHotspotEnabled";
        $queries.= "SubscriberMCC,SubscriberMNC,CurrentMCC,CurrentMNC,IsRoaming,CurrentCarrierNetwork";
        $response = array(
            "Command"     => array(
                "RequestType" => $eventContent->getEventName(),
                "Queries"     => $queries
            ),
            "CommandUUID" => $eventContent->getCommandUuid()
        );
        return $response;
    }

    /**
     * build lock device command for response to device.
     * @param  [string] $eventName, [device_event] $eventContent, [array] $response
     * @return [array] $response
     */
    private function _lockCommand($eventContent, $response) {
        //generate new passcode
        $newPasscode = rand(10000000, 99999999);
        // build command device lock
        $response    = array(
            "Command"     => array(
                "RequestType" => $eventContent->getEventName(),
                "NewPasscode" => $newPasscode
            ),
            "CommandUUID" => $eventContent->getCommandUuid()
        );
        // encript passcode and save to command data.
        $enPasscode  = base64_encode($newPasscode);
        $eventContent->setCommandData($enPasscode);
        $eventContent->save();
        return $response;
    }

    /**
     * build get security info command and response to device.
     * @param  [device_event] $eventContent, [string] $platform
     * @return [array] $response
     */
    private function _getSecurityCommand($eventContent) {
        // information of device.
        $queries  = "HardwareEncryptionCaps,PasscodePresent,PasscodeCompliant,EncryptionStatus";
        $response = array(
            "Command"     => array(
                "RequestType" => $eventContent->getEventName(),
                "Queries"     => $queries
            ),
            "CommandUUID" => $eventContent->getCommandUuid()
        );
        return $response;
    }

    /**
     * return a oldest event in device_event table to device when it wakes up.
     * @param  [DeviceInventory] $device, [array] $response, [string] $platform
     * @return [array] $response
     */
    private function _sendCommand($device, $response) {
        $deviceId     = $device->getId();
        $event        = new DeviceEventRepository();
        $request_type = $this->getRequestTypeByPlatformName(self::ANDROID_APP_CONTENT_TYPE_NAME);
        $eventContent = $event->getCommandByDeviceId($deviceId, $request_type);
        if ($eventContent) {
            // set status of command to processing.
            $event->updateStatus($eventContent, self::WAITING);
            $eventName = $eventContent->getEventName();
            switch ($eventName) {
                case self::INSTALL_PROFILE:
                    $response = $this->installProfileCommand($eventContent, $response);
                    break;
                case self::LOCK_DEVICE:
                    $response = $this->_lockCommand($eventContent, $response);
                    break;
                case self::GET_INFO:
                    $response = $this->_getInformationCommand($eventContent);
                    break;
                case self::SECURITY_INFO:
                    $response = $this->_getSecurityCommand($eventContent);
                    break;
                case self::REMOVE_PROFILE:
                    $response = $this->removeProfileCommand($eventContent, $response);
                    break;
                default:
                    $response = $this->commonCommand($eventContent, $response);
                    break;
            }
        }
        return $response;
    }

    /**
     * update information when the device return the result of executing command.
     * @param  [string]$commandUuid, [int]$status, [array]$content, [DeviceInventory] $device
     * @return 
     */
    private function _handleResponse($commandUuid, $status, $content, $device) {
        $deviceInventoryModel = new DeviceInventoryRepository();
        $event                = new DeviceEventRepository();
        $deviceEvent          = \DeviceEventTable::getInstance()->findOneByCommandUuid($commandUuid);
        $deviceEvent->setModel($device->getModel());
        if ($deviceEvent) {
            // if status is sucess.
            if ($status == self::SUCCESS_STATUS) {
                $deviceEvent->setStatus(self::COMPLETE);  // set status is finished.
                $eventName = $deviceEvent->getEventName();
                switch ($eventName) {
                    case self::LOCK_DEVICE:
                        $eventData = $deviceEvent->getCommandData();
                        $deviceInventoryModel->updatePasscode($device, $eventData);
                        break;
                    case self::GET_INFO:
                        $this->deviceInformation($device, $content, $deviceEvent);
                        break;
                    case self::SECURITY_INFO:
                        $this->deviceSecurity($device, $content, $deviceEvent);
                        break;
                    case self::INSTALLED_APP:
                        $this->deviceInstalledApp($device, $content, $deviceEvent);
                        break;
                    case self::REMOVE_PROFILE:
                        $this->removeProfile($device, $content, $deviceEvent);
                        break;
                    case self::INSTALL_PROFILE:
                        $this->installProfile($device, $content, $deviceEvent);
                        break;
                    case self::GET_LOG_INFORMATION:
                        $this->getLogInformation($device, $content, $deviceEvent);
                        break;
                    default:
                        break;
                }
            } else {// if status is error.
                $error = $content['Error'];
                $event->updateNoteAndStatus($deviceEvent, $error['ErrorCode'], self::ERROR);
            }
            $deviceEvent->save();
            $deviceInventoryModel->setUpdateAtDeviceInventory($device->getId());
        }
    }

}
