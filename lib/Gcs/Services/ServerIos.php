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
use Gcs\Repository\ProfileRepository;
use CFPropertyList\CFData;
use CFPropertyList\CFPropertyList;
use CFPropertyList\CFTypeDetector;

class ServerIos extends ServerAbstract
{

    const INVALID_FORMAT = "1002";
    const GET_INFO = "DeviceInformation";
    const LOCK_DEVICE = "DeviceLock";
    const UNLOCK_DEVICE = "DeviceUnlock";
    const INSTALL_PROFILE = "InstallProfile";
    const SECURITY_INFO = "SecurityInfo";
    const INSTALLED_APP = "InstalledApplicationList";
    const REMOVE_PROFILE = "RemoveProfile";
    const UN_ENROLL = "Unenroll";
    const CLEAR_PASSCODE = "ClearPasscode";
    const GET_LOG_INFORMATION = "GetLogInformation";
    const INSTALLAPPLICATION = "InstallApplication";
    const IDLE_STATUS_IOS = "Idle";
    const SUCCESS_STATUS_IOS = "Acknowledged";
    const WAITING = 1;
    const COMPLETE = 2;
    const ERROR = 3;
    const IOS = 2;
    const IOS_CONTENT_TYPE_NAME = "ios";

    public function callServer($content, $log)
    {
        $log = $this->getLogPosition(__FILE__);
        try {
            $response = array();
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
                        $response = array("0");
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
        $request_type = $this->getRequestTypeByPlatformName(self::IOS_CONTENT_TYPE_NAME);
        $eventContent = $event->getCommandByDeviceId($deviceId, $request_type);
        if ($eventContent) {
            // set status of command to processing.
            $event->updateStatus($eventContent, self::WAITING);
            $eventName = $eventContent->getEventName();
            switch ($eventName) {
                case self::INSTALLAPPLICATION:
                    $response = $this->_installEnterpriseApp($eventContent, $response);
                    break;
                case self::INSTALL_PROFILE:
                    $response = $this->_installProfileIOS($eventContent, $response);
                    break;
                case self::REMOVE_PROFILE:
                    $response = $this->_removeProfileIOS($eventContent, $response);
                    break;
                case self::LOCK_DEVICE:
                    $response = $this->_lockCommandIOS($eventContent, $response);
                    break;
                case self::GET_INFO:
                    $response = $this->_getInformationCommand($eventContent);
                    break;
                case self::SECURITY_INFO:
                    $response = $this->_getSecurityCommand($eventContent);
                    break;
                case self::UNLOCK_DEVICE:
                    $unlockToken = base64_decode($device->getUnlockToken());
                    $response = $this->_clearPasscodeIOS($eventContent, $response, $unlockToken);
                    break;
                case self::UN_ENROLL:
                    $response = $this->_removeProfileCommandIOS($eventContent, $response);
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

    /**
     * install enterprise application
     * @param  [DeviceEvent] $eventContent, [array] $response
     * @return [array] $response
     */
    private function _installEnterpriseApp($eventContent, $response)
    {
        $deviceInventory = new DeviceInventoryRepository();
        $device = $deviceInventory->getDeviceInventory($eventContent->getDeviceId());
        $filePath = \sfConfig::get("app_enroll_app_url_data");
        if ($device) {
            $response = array(
                "Command" => array(
                    "RequestType" => $eventContent->getEventName(),
                    "ManifestURL" => preg_replace("/mdm\//i", "", public_path($filePath[3], true)),
                    "Configuration" => array(
                        "UDIDCode" => $device->getUdid()
                    ),
                    "ChangeManagementState" => "Managed"
                ),
                "CommandUUID" => $eventContent->getCommandUuid()
            );
        }
        return $response;
    }

    /**
     * return a InstallProfile event in device_event table to device.
     * @param  [DeviceEvent] $eventContent, [array] $response
     * @return [array] $response
     */
    private function _installProfileIOS($eventContent, $response)
    {
        $profileData = $this->_getProfileData($eventContent, $response);
        if (count($profileData)) {
            $profileType = $profileData['configuration_type'];
            $payloadContent = array();
            switch ($profileType) {
                case 1://Wifi
                    break;
                case 2://Passcode
                    $payloadContent = $this->_getPasscodePayloadContent($eventContent, $profileData, $payloadContent);
                    break;
                default://Location
                    break;
            }
            if ($profileType != 3) {
                $response = $this->_buildProfileIOS($eventContent, $profileData, $payloadContent, $response);
            }
        }
        return $response;
    }

    private function _getProfileData($eventContent, $response)
    {
        $profileData = array();
        $commandData = $eventContent->getCommandData();
        $commandData = explode(",", $commandData);
        if (is_numeric($commandData[0]) && $commandData[0] > 0) {
            $profileId = $commandData[0];
            $profileRepository = new ProfileRepository();
            $profileData = $profileRepository->getProfileIOSByProfileId($profileId);
        }
        return $profileData;
    }

    /**
     * Return Passcode Payload Content.
     * @param  [DeviceEvent] $eventContent, [Array] $profileData
     * @return [array] $payloadContent
     */
    private function _getPasscodePayloadContent($eventContent, $profileData)
    {
        $profileRes = new ProfileRepository();
        $payloadContent = array(
            "PayloadDisplayName" => $profileData['profile_name'],
            "PayloadDescription" => $profileData['description'],
            'PayloadIdentifier' => $profileRes->getPayloadIdentifier($profileData['configuration_type']) . "_" . $profileData['profileDataId'],
            'PayloadVersion' => 1,
            'PayloadType' => 'com.apple.mobiledevice.passwordpolicy',
            'PayloadUUID' => $eventContent->getCommandUuid(),
        );

        $profileDataCompare = array();
        foreach ($profileData['profileData'] as $key => $value) {
            $passcodePolicy = $profileRes->getPasscodePolicyPayload($key);
            if ($passcodePolicy) {
                $profileDataCompare[$passcodePolicy] = $value;
            }
        }

        \MDMLogger::getInstance()->debug('', __LINE__ . ":::: Passcode Policy New: " . serialize($profileDataCompare), array());
        $payloadContent = array_merge($payloadContent, $profileDataCompare);

        return $payloadContent;
    }

    private function _returnPLIST($payload)
    {
        $plist = new CFPropertyList();
        $td = new CFTypeDetector();
        $guessedStructure = $td->toCFType($payload);
        $plist->add($guessedStructure);
        $xml = $plist->toXML();
        return $xml;
    }

    /**
     * Building command InstallProfile.
     * @param  [DeviceEvent] $eventContent, [Array] $profileData, [array] $payloadContent, [Array] $response
     * @return [array] $response
     */
    private function _buildProfileIOS($eventContent, $profileData, $payloadContent, $response)
    {
        $profileRes = new ProfileRepository();
        $payload = array(
            "PayloadContent" => array($payloadContent),
            "PayloadDescription" => $profileData['description'],
            "PayloadDisplayName" => $profileData['profile_name'],
            "PayloadIdentifier" => $profileRes->getPayloadIdentifier($profileData['configuration_type']),
            "PayloadOrganization" => "",
            "PayloadRemovalDisallowed" => false,
            "PayloadType" => "Configuration",
            "PayloadUUID" => $eventContent->getCommandUuid(),
            "PayloadVersion" => 1,
        );

        $payload = $this->_returnPLIST($payload);

        $response = array(
            "Command" => array(
                "RequestType" => $eventContent->getEventName(),
                "Payload" => new CFData(base64_encode($payload), true)
            ),
            "CommandUUID" => $eventContent->getCommandUuid()
        );
        return $response;
    }

    /**
     * build lock device command for response to ios device.
     * @param  [string] $eventName, [device_event] $eventContent, [array] $response
     * @return [array] $response
     */
    private function _lockCommandIOS($eventContent, $response)
    {
        //build command device lock
        $response = array(
            "Command" => array(
                "RequestType" => $eventContent->getEventName(),
            ),
            "CommandUUID" => $eventContent->getCommandUuid()
        );
        return $response;
    }

    /**
     * build get information command and response to device.
     * @param  [device_event] $eventContent, [string] $platform
     * @return [array] $response
     */
    private function _getInformationCommand($eventContent)
    {
        // information of device.
        $queries = "DeviceName,IMEI,Manufacturer,Model,OSVersion,";
        $queries.= "BluetoothMAC,CurrentCountry,";
        $queries.= "CurrentNetwork,DataRoamingEnabled,DeviceType,";
        $queries.= "EasIdentifier,HomeCountry,HomeNetwork,HotspotEnabled,ICCID,";
        $queries.= "IsStoreAccountActive,IsNotDisturbActive,";
        $queries.= "OperatorName,PhoneNumber,ProductName,SerialNumber,UDID,WiFiMAC,";
        $queries.= "AvailableDeviceCapacity,DeviceCapacity,StorageName,Encrypted,CurrentDataRoaming";
        $queries.= "IsDoNotDisturbInEffect,EASDeviceIdentifier,PersonalHotspotEnabled";
        $queries.= "SubscriberMCC,SubscriberMNC,CurrentMCC,CurrentMNC,IsRoaming,CurrentCarrierNetwork";
        $queries = explode(",", $queries);
        $response = array(
            "Command" => array(
                "RequestType" => $eventContent->getEventName(),
                "Queries" => $queries
            ),
            "CommandUUID" => $eventContent->getCommandUuid()
        );
        return $response;
    }

    /**
     * build get security info command and response to device.
     * @param  [device_event] $eventContent, [string] $platform
     * @return [array] $response
     */
    private function _getSecurityCommand($eventContent)
    {
        // information of device.
        $queries = "HardwareEncryptionCaps,PasscodePresent,PasscodeCompliant,EncryptionStatus";
        $queries = explode(",", $queries);
        $response = array(
            "Command" => array(
                "RequestType" => $eventContent->getEventName(),
                "Queries" => $queries
            ),
            "CommandUUID" => $eventContent->getCommandUuid()
        );
        return $response;
    }

    /**
     * build remove profile IOS for response to device.
     * @param  [device_event] $eventContent, [array] $response
     * @return [array] $response
     */
    private function _removeProfileIOS($eventContent, $response)
    {
        $profileData = $this->_getProfileData($eventContent, $response);
        if (count($profileData)) {
            $profileType = $profileData['configuration_type'];
            $profileRes = new ProfileRepository();
            $Identifier = "";
            switch ($profileType) {
                case 1://Wifi
                    break;
                case 2://Passcode
                    $Identifier = $profileRes->getPayloadIdentifier($profileType);
                    break;
                default://Location
                    break;
            }

            $response = array(
                "Command" => array(
                    "RequestType" => $eventContent->getEventName(),
                    "Identifier" => $Identifier
                ),
                "CommandUUID" => $eventContent->getCommandUuid()
            );
        }
        return $response;
    }

    /**
     * build remove profile command for response to IOS device.
     * @param  [device_event] $eventContent, [array] $response
     * @return [array] $response
     */
    private function _clearPasscodeIOS($eventContent, $response, $unlockToken)
    {

        $response = array(
            "Command" => array(
                "RequestType" => self::CLEAR_PASSCODE,
                "UnlockToken" => $unlockToken
            ),
            "CommandUUID" => $eventContent->getCommandUuid()
        );
        return $response;
    }

    /**
     * build remove profile command for response to IOS device.
     * @param  [device_event] $eventContent, [array] $response
     * @return [array] $response
     */
    private function _removeProfileCommandIOS($eventContent, $response)
    {
        $Identifier = \sfConfig::get("app_payload_identifier_data");
        $response = array(
            "Command" => array(
                "RequestType" => self::REMOVE_PROFILE,
                "Identifier" => $Identifier[0]
            ),
            "CommandUUID" => $eventContent->getCommandUuid()
        );
        return $response;
    }

}
