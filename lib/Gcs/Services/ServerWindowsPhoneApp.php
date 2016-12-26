<?php

/**
 * Description: class for Windows Phone service
 * General command and return to WNS service by command request
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

use Gcs\Services\ServerAbstract;
use Gcs\Repository\DeviceEventRepository;
use Gcs\Repository\DeviceInventoryRepository;
use Gcs\Repository\ConfigRepository;

class ServerWindowsPhoneApp extends ServerAbstract {

    const GET_INFO                 = "DeviceInformation";
    const INSTALL_PROFILE          = "InstallProfile";
    const SECURITY_INFO            = "SecurityInfo";
    const INSTALLED_APP            = "InstalledApplicationList";
    const REMOVE_PROFILE           = "RemoveProfile";
    const GET_LOG_INFORMATION      = "GetLogInformation";
    const GET_INFO_POLLING         = "GetInformationPolling";
    const GENERATE_PFN             = "GeneratePFN";
    const SUCCESS_STATUS           = "0";
    const ENROLLED                 = 0;
    const READY                    = 0;
    const WAITING                  = 1;
    const COMPLETE                 = 2;
    const ERROR                    = 3;
    const IDLE_STATUS              = 2;
    const LOCATION_INFOR           = "LocationInformation";
    const LATEST_LOCATION          = "GetLatestLocation"; 
    const INVALID_FORMAT           = "1002";
    const PUSH_FAIL_CODE           = "2001";
    const WP_PLATFORM              = "wp";
    const PUSH_SUCCESS             = 200;
    const WP_APP_CONTENT_TYPE_NAME = "wpapp";
    const PFN_EXPIRE               = 2592000000;    // 24 * 3600 * 1000 * 30

    /**
     * receive request and response command for each action when device wake up or request to get command.
     * @param  [object] $controller, [array] $content, [string] $log
     * @return [array] $response
     */
    public function callServer($content, $log) {
        $log      = $this->getLogPosition(__FILE__);
        $response = array();
        try {
            // receive information from device reply on time scheduler  
            if (isset($content['MessageType'])) {
                $this->_receiveInformation($content);
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
            }
            // if status is error.
            else {
                $error = $content['Error'];
                $event->updateNoteAndStatus($deviceEvent, $error['ErrorCode'], self::ERROR);
            }
            $deviceEvent->save();
            $deviceInventoryModel->setUpdateAtDeviceInventory($device->getId());
        }
    }

    /**
     * return a oldest event in device_event table to device when it wakes up.
     * @param  [DeviceInventory] $device, [array] $response, [string] $platform
     * @return [array] $response
     */
    private function _sendCommand($device, $response) {
        $deviceId     = $device->getId();
        $event        = new DeviceEventRepository();
        $request_type = $this->getRequestTypeByPlatformName(self::WP_APP_CONTENT_TYPE_NAME);
        $eventContent = $event->getCommandByDeviceId($deviceId, $request_type);
        if ($eventContent) {
            // set status of command to processing.
            $event->updateStatus($eventContent, self::WAITING);
            $eventName = $eventContent->getEventName();
            \MDMLogger::getInstance()->debug('', __LINE__ . "::::" . $deviceId . "::::" . $eventName, array());
            switch ($eventName) {
                case self::INSTALL_PROFILE:
                    $response = $this->installProfileCommand($eventContent, $response);
                    break;
                case self::REMOVE_PROFILE:
                    $response = $this->removeProfileCommand($eventContent, $response);
                    break;
                case self::LATEST_LOCATION:
                    $event->updateStatus($eventContent, self::COMPLETE);
                    break;
                default:
                    // Get Log Information
                    $response = $this->commonCommand($eventContent, $response);
                    break;
            }
        }
        return $response;
    }

    /**
     * recevice information reply on time scheduler 
     * @param  [array] $content
     * @return [boolean] $response
     */
    public function _receiveInformation($content) {
        if (isset($content['MessageType']) && isset($content['UDID'])) {
            $messageType     = $content['MessageType'];
            $udid            = $content['UDID'];
            $deviceInventory = new DeviceInventoryRepository();
            $device          = $deviceInventory->getDeviceInventorybyUDID($udid);
            if ($device) {
                if ($device->getEnrollStatus() == self::ENROLLED) {
                    switch ($messageType) {
                        case self::LOCATION_INFOR:
                            $this->receiveLocation($content);
                            break;
                        case self::GET_INFO:
                            $this->deviceInformation($device, $content, null);
                            break;
                        case self::SECURITY_INFO:
                            $this->deviceSecurity($device, $content, null);
                            break;
                        case self::INSTALLED_APP:
                            $this->deviceInstalledApp($device, $content, null);
                            break;
                        case self::GET_INFO_POLLING:
                            $this->_deviceInforPolling($device);
                            break;
                        default:
                            break;
                    }
                }
            }
        }
    }

    /**
     * get information of device reply on time scheduler for iOS platform
     * @param  [DeviceInventory] $device
     * @return [array] $response
     */
    private function _deviceInforPolling($device) {
        $deviceId         = $device->getId();
        $regId            = $device->getRegistrationId();
        // Save device information command to DeviceEvent table.
        $deviceEventModel = new DeviceEventRepository();
        $configRepository = new ConfigRepository();
        $platform         = self::WP_PLATFORM;
        $commands         = array();
        $request_type     = $configRepository->getRequestTypeByPlatform($platform);
        if (!($deviceEventModel->getCommandByEventNameAndStatus(self::GET_INFO, self::READY, $deviceId))) {
            $getInfoUdid = $deviceEventModel->saveCommand($deviceId, self::GET_INFO, self::READY, null, null, false, $request_type);
            array_push($commands, $getInfoUdid);
        }
        // Get channelURI for MDM if more than 30 days
        $latestPFN = $deviceEventModel->getLatestPFNCommand($deviceId);
        if (!$latestPFN || ($latestPFN && (time() - strtotime($latestPFN->updated_at) >= self::PFN_EXPIRE))){
            $generatePFN = $deviceEventModel->saveCommand($deviceId, self::GENERATE_PFN, self::READY, null, null, false, $request_type);
            array_push($commands, $generatePFN);
        }
            
        // get registrationId and push notification to devices.
        $regIds = array($regId);
        $result = null;
        if (!empty($commands)) {
            $result = $deviceEventModel->pushNotification($regIds, $platform, null);
        }
        \MDMLogger::getInstance()->debug('', print_r($result, true), array());
        // save error code when push notification is not successful.
        if ($result->httpCode != self::PUSH_SUCCESS) {
            foreach ($commands as $command) {
                $eventDevice = $deviceEventModel->getCommandByCommandUuid($command);
                $deviceEventModel->updateNoteAndStatus($eventDevice, self::PUSH_FAIL_CODE, self::ERROR);
            }
            return false;
        }
        return true;
    }

}
