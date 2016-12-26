<?php

/**
 * Description: class for IOS service
 * General command and return to Apple service by command request
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

use Gcs\Services\ServerAbstract;
use Gcs\Repository\DeviceEventRepository;
use Gcs\Repository\DeviceInventoryRepository;

class ServerIosApp extends ServerAbstract
{

    const INVALID_FORMAT = "1002";
    const GET_LOG_INFORMATION = "GetLogInformation";
    const IDLE_STATUS_IOS = "Idle";
    const SUCCESS_STATUS_IOS = "Acknowledged";
    const WAITING = 1;
    const COMPLETE = 2;
    const ERROR = 3;
    const IOS = 2;
    const IOS_APP_CONTENT_TYPE_NAME = "iosapp";
    const INSTALL_PROFILE = "InstallProfile";
    const REMOVE_PROFILE = "RemoveProfile";

    public function callServer($content, $log)
    {
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
                $udid = $content['UDID'];
                $status = $content['Status'];
                // get device based on udid and check it is existed in database.
                $deviceInventoryRes = new DeviceInventoryRepository();
                $device = $deviceInventoryRes->getDeviceInventorybyUDID($udid);
                $isValid = $this->validateDevice($device);

                // special case for get device information
                $isSpecialEvent = $this->validateSpecEvent($content);
                // special case for unenroll
                $isUnenroll = $this->skipUnenroll($device);
                if ($isValid || $isUnenroll || $isSpecialEvent) {
                    // check requests after device wake up. 
                    if ($status === self::IDLE_STATUS_IOS) {
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
                        }
                    }
                } else {
                    // Device is unenrrolled
                    \MDMLogger::getInstance()->info('', $log . "Device is unenrrolled", array());
                    \MDMLogger::getInstance()->debug('', $log . json_encode($response), array());
                }
            }
        } catch (Exception $e) {
            \MDMLogger::getInstance()->error('', $log . $e, array());
        }
        \MDMLogger::getInstance()->debug('', $log . json_encode($response), array());
        return $response;
    }

    /**
     * return a oldest event in device_event table to device when it wakes up.
     * @param  [DeviceInventory] $device, [array] $response, [string] $platform
     * @return [array] $response
     */
    private function _sendCommand($device, $response)
    {
        $deviceId = $device->getId();
        $event = new DeviceEventRepository();
        $request_type = $this->getRequestTypeByPlatformName(self::IOS_APP_CONTENT_TYPE_NAME);
        $eventContent = $event->getCommandByDeviceId($deviceId, $request_type);
        if ($eventContent) {
            // set status of command to processing.
            $event->updateStatus($eventContent, self::WAITING);
            $eventName = $eventContent->getEventName();
            switch ($eventName) {
                case self::INSTALL_PROFILE:
                    $response = $this->installProfileCommand($eventContent, $response);
                    break;
                case self::REMOVE_PROFILE:
                    $response = $this->removeProfileCommand($eventContent, $response);
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
     * update information when the device return the result of executing command.
     * @param  [string]$commandUuid, [int]$status, [array]$content, [DeviceInventory] $device
     * @return 
     */
    private function _handleResponse($commandUuid, $status, $content, $device)
    {
        $deviceInventoryModel = new DeviceInventoryRepository();
        $event = new DeviceEventRepository();
        $deviceEvent = \DeviceEventTable::getInstance()->findOneByCommandUuid($commandUuid);
        $deviceEvent->setModel($device->getModel());
        if ($deviceEvent) {
            // if status is sucess.
            if ($status == self::SUCCESS_STATUS_IOS) {
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
            } else {// if status is error.
                $error = $content['ErrorChain'][0];
                $event->updateNoteAndStatus($deviceEvent, $error['ErrorCode'], self::ERROR);
            }
            $deviceEvent->save();
            $deviceInventoryModel->setUpdateAtDeviceInventory($device->getId());
        }
    }

}
