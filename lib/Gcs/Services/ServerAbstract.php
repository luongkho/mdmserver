<?php

/**
 * Description: Abstract class for general command service
 *  Base class for 3 platform: Android, IOS and Windows phone
 *  Abstract function general command for 3 platform
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

use Gcs\Repository\DeviceEventRepository;
use Gcs\Repository\DeviceInventoryRepository;
use Gcs\Repository\DeviceLocationRepository;
use Gcs\Repository\DeviceApplicationRepository;
use Gcs\Repository\ProfileRepository;
use Gcs\Repository\DeviceLogRepository;
use Gcs\Repository\ConfigRepository;

abstract class ServerAbstract {

    const ENROLLED         = 0;
    const READY            = 0;
    const ERROR            = 3;
    const ERROR_STATUS     = "1";
    const UN_ENROLL        = "Unenroll";
    const UN_ENROLL_APP    = "UnenrollApp";
    const INFORMATION      = "Information";
    const LOCATION_INFOR   = "LocationInformation";
    const GET_INFO         = "DeviceInformation";
    const SECURITY_INFO    = "SecurityInfo";
    const INSTALLED_APP    = "InstalledApplicationList";
    const GET_INFO_POLLING = "GetInformationPolling";
    const LOCATION_GROUP   = "Location";

    abstract protected function callServer($content, $log);

    /**
     * Log position
     * @param type $log
     * @return type
     */
    public function getLogPosition($log) {
        $logPathArray = explode("\\", $log);
        return "File::".end($logPathArray)."::";
    }

    /**
     * build remove profile command for response to device.
     * @param  [device_event] $eventContent, [array] $response
     * @return [array] $response
     */
    public function removeProfileCommand($eventContent, $response) {
        $response = array(
            "Command"     => array(
                "RequestType" => $eventContent->getEventName(),
                "Identifier"  => ""
            ),
            "CommandUUID" => $eventContent->getCommandUuid()
        );
        return $response;
    }

    /**
     * build install profile command for response to device.
     * @param  [device_event] $eventContent, [array] $response
     * @return [array] $response
     */
    public function installProfileCommand($eventContent, $response) {
        // get profile group base on name
        $profileGroup = \ProfileAttributeGroupTable::getInstance()->findOneByName(self::LOCATION_GROUP);
        if ($profileGroup) {
            // get profile group id
            $profileGroupId = $profileGroup->getId();
            $commandData    = $eventContent->getCommandData();
            if ($commandData) {
                $data      = explode(",", $commandData);
                $profileId = $data[0];
                if ($profileId) {
                    // get profile information based on profile Id and profile group Id.
                    $profileInformation = \ProfileInformationTable::getInstance()
                            ->findOneByProfileIdAndProfileAttributeGroupId($profileId, $profileGroupId);
                    if ($profileInformation) {
                        // get information of profile and convert to array.
                        $value        = $profileInformation->getValue();
                        $profileValue = unserialize($value);
                        if (isset($profileValue['distance']) && isset($profileValue['interval'])) {
                            $response = array(
                                "Command"     => array(
                                    "RequestType" => $eventContent->getEventName(),
                                    "Distance"    => $profileValue['distance'],
                                    "Interval"    => $profileValue['interval']
                                ),
                                "CommandUUID" => $eventContent->getCommandUuid()
                            );
                        }
                    }
                }
            }
        }
        return $response;
    }

    /**
     * Store log content in database
     * @param  [array] $content
     * @return [boolean] $response
     */
    public function getLogInformation($device, $content, $deviceEvent) {
        $logRepository = new DeviceLogRepository();
        $log           = $logRepository->saveLog($device, $content);
    }

    /**
     * save profile which is installed in device to database
     * @param [device_inventory] $device, [json] $content, [device_event] $deviceEvent 
     * @return NONE
     */
    public function installProfile($device, $content, $deviceEvent) {
        $profileRepository = new ProfileRepository();
        $data              = explode(",", $deviceEvent->getCommandData());
        $profileRepository->updateDeviceProfile($data, $device);
    }

    /**
     * remove profile which is installed in device
     * @param [device_inventory] $device, [json] $content, [device_event] $deviceEvent 
     * @return NONE
     */
    public function removeProfile($device, $content, $deviceEvent) {
        // Remove device profile
        $deviceProfile = \DeviceProfileTable::getInstance()
                ->findOneByDeviceIdAndProfileId($device->getId(), $deviceEvent->getCommandData());
        if ($deviceProfile) {
            $deviceProfile->delete();
        }

        // Remove device location if profile is Location
        $deviceLocationRep = new DeviceLocationRepository();
        $deletelocation    = $deviceLocationRep->deleteDeviceLocation(
                $deviceEvent->getCommandData(), $device->getId());
    }

    /**
     * get installed app list from device and set to inventory information table
     * @param [device_inventory] $device, [json] $content, [device_event] $deviceEvent 
     * @return NONE
     */
    public function deviceInstalledApp($device, $content, $deviceEvent) {
        if (isset($content['InstalledApplicationList'])) {
            $queryData         = $content['InstalledApplicationList'];
            // update information from request of device to database
            $deviceApplication = new DeviceApplicationRepository();
            $deviceApplication->updateInstalledApp($device, $queryData);
        }
    }

    /**
     * get security info from device and set to inventory information table
     * @param [device_inventory] $device, [json] $content, [device_event] $deviceEvent 
     * @return NONE
     */
    public function deviceSecurity($device, $content, $deviceEvent) {
        if (isset($content['SecurityInfo'])) {
            $queryData       = $content['SecurityInfo'];
            // update information from request of device to database
            $deviceInventory = new DeviceInventoryRepository();
            $deviceInventory->updateSecurityInfo($device, $queryData);
        }
    }

    /**
     * get information from device and set to inventory information table
     * @param [device_inventory] $device, [json] $content, [device_event] $deviceEvent 
     * @return NONE
     */
    public function deviceInformation($device, $content, $deviceEvent) {
        if (isset($content['QueryResponses'])) {
            $queryData       = $content['QueryResponses'];
            // update information from request of device to database
            $deviceInventory = new DeviceInventoryRepository();
            $deviceInventory->updateDeviceInfo($device, $queryData, $deviceEvent);
            // save Inventory Information 
            $deviceInventory->updateInventoryInfo($device, $queryData);
        }
    }

    /**
     * build common command for response to device.(EraseDevice, Unenroll,...]
     * @param  [device_event] $eventContent, [array] $response
     * @return [array] $response
     */
    public function commonCommand($eventContent, $response) {
        $response = array(
            "Command"     => array(
                "RequestType" => $eventContent->getEventName(),
            ),
            "CommandUUID" => $eventContent->getCommandUuid()
        );
        return $response;
    }

    /**
     * Get request_type by platform name.
     * @param String $platform_name
     * @return Int $platform_type
     */
    public function getRequestTypeByPlatformName($platform_name) {
        $configRepository = new ConfigRepository();
        $request_type     = $configRepository->getRequestTypeByPlatform($platform_name);
        return $request_type;
    }

    /**
     * special case: send command unenroll for device when device is un-enrolled. 
     * @param  [DeviceInventory] $device
     * @return [boolean]
     */
    public function skipUnenroll($device) {
        if ($device) {
            $command = \DeviceEventTable::getInstance()->findOneByDeviceIdAndStatus($device->getId(), 0);
            if ($command) {
                $commandName = $command->getEventName();
                $commands    = array(
                    self::UN_ENROLL,
                    self::UN_ENROLL_APP
                );
                if (in_array($commandName, $commands)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * validate special event 
     * @param  [array] $content
     * @return [boolean] $response
     */
    public function validateSpecEvent($content) {
        if (isset($content['CommandUUID'])) {
            $deviceEvent = \DeviceEventTable::getInstance()->findOneByCommandUuid($content['CommandUUID']);
            if ($deviceEvent) {
                $eventName = $deviceEvent->getEventName();
                $eventType = $deviceEvent->getEventType();
                if ($eventType == self::INFORMATION || $eventName == self::UN_ENROLL) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * validate device
     * @param  [device_event] $device
     * @return [boolean]
     */
    public function validateDevice($device) {
        if ($device) {
            $enrollStatus = $device->getEnrollStatus();
            if ($enrollStatus == self::ENROLLED) {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * build command error for response to device.
     * @param  [array] $response, [string] $error
     * @return [array] $response
     */
    public function errorCommand($response, $error) {
        $response = array(
            'Status' => self::ERROR_STATUS,
            'Error'  => array(
                'ErrorCode' => $error
            )
        );
        return $response;
    }

    /**
     * recevice information reply on time scheduler 
     * @param  [array] $content
     * @return [boolean] $response
     */
    public function receiveInformation($content) {
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
                    $deviceInventory->setUpdateAtDeviceInventory($device->getId());
                }
            }
        }
    }

    /**
     * receive location tracking.
     * @param  [array] $content
     * @return [array] $response
     */
    public function receiveLocation($content) {
        $deviceLocation = new DeviceLocationRepository();
        $deviceUDID     = $content['UDID'];
        $lat            = $content['Latitude'];
        $lon            = $content['Longitude'];
        $deviceLocation->saveLocation($deviceUDID, $lat, $lon);
    }

    /**
     * get information of device reply on time scheduler for iOS platform
     * @param  [DeviceInventory] $device
     * @return [array] $response
     */
    private function _deviceInforPolling($device) {
        $deviceId         = $device->getId();
        $regId            = $device->getRegistrationId();
        $pushMagic        = $device->getPushMagic();
        // Save device information command to DeviceEvent table.
        $deviceEventModel = new DeviceEventRepository();
        $configRepository = new ConfigRepository();
        $platform         = "ios";
        $commands         = array();
        $request_type     = $configRepository->getRequestTypeByPlatform($platform);
        if (!($deviceEventModel->getCommandByEventNameAndStatus(self::GET_INFO, self::READY, $deviceId))) {
            $getInfoUdid = $deviceEventModel->saveCommand($deviceId, self::GET_INFO, self::READY, null, null, false, $request_type);
            array_push($commands, $getInfoUdid);
        }
        if (!($deviceEventModel->getCommandByEventNameAndStatus(self::SECURITY_INFO, self::READY, $deviceId))) {
            $getSecurityUdid = $deviceEventModel->saveCommand($deviceId, self::SECURITY_INFO, self::READY, null, null, false, $request_type);
            array_push($commands, $getSecurityUdid);
        }
        if (!($deviceEventModel->getCommandByEventNameAndStatus(self::INSTALLED_APP, self::READY, $deviceId))) {
            $installedAppUdid = $deviceEventModel->saveCommand($deviceId, self::INSTALLED_APP, self::READY, null, null, false, $request_type);
            array_push($commands, $installedAppUdid);
        }
//        array_push($commands, $getInfoUdid, $getSecurityUdid, $installedAppUdid);
        // get registrationId and push notification to devices.
        $regIds = array();
        array_push($regIds, $regId);
        $result = "";
        if (!empty($commands)) {
            $result = $deviceEventModel->pushNotification($regIds, $platform, $pushMagic);
        }
        // save error code when push notification is not successful.
        if ($result != "") {
            foreach ($commands as $command) {
                $eventDevice = $deviceEventModel->getCommandByCommandUuid($command);
                $deviceEventModel->updateNoteAndStatus($eventDevice, self::PUSH_FAIL_CODE, self::ERROR);
            }
            return false;
        }
        return true;
    }

}
