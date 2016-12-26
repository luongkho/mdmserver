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
use Gcs\Repository\EnrollWPRepository;

class ServerWindowsPhone extends ServerAbstract
{

    const WP_PUSH = "wp";
    const GET_INFO = "DeviceInformation";
    const LOCK_DEVICE = "DeviceLock";
    const UNLOCK_DEVICE = "DeviceUnlock";
    const INSTALL_PROFILE = "InstallProfile";
    const SECURITY_INFO = "SecurityInfo";
    const INSTALLED_APP = "InstalledApplicationList";
    const INSTALLED_APPLICATION = "InstallApplication";
    const REMOVE_PROFILE = "RemoveProfile";
    const UN_ENROLL = "Unenroll";
    const CLEAR_PASSCODE = "ClearPasscode";
    const GET_LOG_INFORMATION = "GetLogInformation";
    const GET_INFO_POLLING = "GetInformationPolling";
    const GENERATE_PFN = "GeneratePFN";
    const SUCCESS_STATUS = "200";
    const READY = 0;
    const WAITING = 1;
    const COMPLETE = 2;
    const ERROR = 3;
    const IDLE_STATUS = 1201;
    const WINDOWSPHONE = 3;
    const ALLOW_REENROLL_CODE = 2;
    const ENROLL_CODE = 0;
    const UNENROLL_CODE = 1;
    const INVALID_FORMAT_CODE = "1002";
    const UNENROLL_BYUSER = 1226;
    const WP_CONTENT_TYPE_NAME = "wp";
    const WAITING_APP = 2;
    const WATING_MDM = 1;
    const ENROLLED_WP = 3;

    /**
     * receive request and response command for each action when device wake up or request to get command.
     * @param  [object] $controller, [array] $content, [string] $log
     * @return [array] $response
     */
    public function callServer($content, $log)
    {
        $log = $this->getLogPosition(__FILE__);
        $response = null;
        // update information of device when device send sync command.
        if (isset($content['SyncHdr']['Source']['LocURI'])) {
            $udid = $content['SyncHdr']['Source']['LocURI'];
            $device = $this->updateEnrollWP($udid, $content);
        }
        if (is_null($device)) {
            $commandUuid = rand(1, 1000);
            $udid = $content['SyncHdr']['Source']['LocURI'];
            $checkRequest = $this->_getPFN($content, $udid);
            if (!$checkRequest) {
                $response = $this->generatePFN($content, $commandUuid, self::WINDOWSPHONE);
            }
        } else {
            // device send sync for get command from server
            if (isset($content['SyncBody']['Alert']['Data'])) {
                $status = $content['SyncBody']['Alert']['Data'];
                if ($status == self::IDLE_STATUS) {
                    $response = $this->_sendCommand($device, $content, self::WINDOWSPHONE);
                }
            }
            // device send result of command to server
            if (isset($content['SyncBody']['Status'])) {
                $statuses = $content['SyncBody']['Status'];
                $status = $this->_getCommandStatus($statuses);
                $commandUuid = $this->_getCommandUuid($content);
                $response = $this->_handleResponse($commandUuid, $status, $content, $device);
            }
            // device send command notify user un-enrolled mdmserver
            $this->_unenrollByUser($content);
            // if device is unenrolled and don't have any command response for mdm
            if ($device->getEnrollStatus() == self::UNENROLL_CODE 
                    && is_null(trim($response))) {
                $response = $this->_deviceIsUnenrolled($device, $content, $response);
            }
        }
        if (!$response) {
            $response = $this->_returnSuccess($content);
        }
        return $response;
    }

    /**
     * receive request and enroll device.
     * @param  [string] $udid, [array] $content
     * @return [string] fileContent
     */
    public function updateEnrollWP($udid, $content)
    {
        $enrollRepository = new EnrollWPRepository();
        // get username and password from request of device.
        $userID = $this->_getUserIdInRequest($content);
        $enrollWP = $enrollRepository->getDeviceByUserIDMDM($userID, $udid);
        if (!$enrollWP) {
            $enrollWP = new \EnrollWp();
        }

        $enrollRepository->updateInfoEnrollWP($enrollWP, $userID, $udid, null, self::WAITING_APP);
        if ($enrollWP->getStatus() == self::ENROLLED_WP) {
            return $this->updateDeviceInformation($enrollWP, $content, $userID);
        }
        return null;
    }

    /**
     * receive request and enroll device.
     * @param  [string] $udid, [array] $content
     * @return [string] fileContent
     */
    public function updateDeviceInformation($enrollWP, $content, $userID)
    {
        $udid = $enrollWP->getUdid();
        $deviceInventory = new DeviceInventoryRepository();
        $device = $deviceInventory->getDeviceInventorybyUDID($udid);
        $new = false;
        if (!$device) {
            $device = new \DeviceInventory();
            $device->setUdid($udid);
            $device->save();
            $new = true;
        }
        $enrollStatus = $device->getEnrollStatus();
        $deviceData = $this->convertInfoFormRequest($content, 'Replace');
        if ($deviceData && $enrollStatus != self::UNENROLL_CODE) {
            $channelUri = $device->getRegistrationId();
            $deviceInventory->updateDeviceInventory($device, $channelUri, $userID, $udid, $deviceData, 3);
            $deviceInventory->updateHardwareId($device, $userID, $udid);
            $deviceInventory->updateDeviceToken($device, $enrollWP->getChanneluriApp());
        }
        if ($new || $enrollStatus == self::ALLOW_REENROLL_CODE) {
            $deviceEvent = new DeviceEventRepository();
            $deviceEvent->saveCommand($device->getId(), self::GENERATE_PFN, self::READY, null, null, true, 30);
            $deviceEvent->saveCommand($device->getId(), self::GET_INFO, self::READY, null, null, true, 30);
        }
        return $device;
    }

    /**
     * convert information of device.
     * @param  [array] $content, [string]$tagName
     * @return [array] deviceInfo
     */
    public function convertInfoFormRequest($content, $tagName)
    {
        $deviceInfo = array();
        if (isset($content['SyncBody'][$tagName])) {
            $items = $content['SyncBody'][$tagName]['Item'];
            foreach ($items as $item) {
                $elementName = $item['Source']['LocURI'];
                switch ($elementName) {
                    case './DevInfo/DevId':
                        $deviceInfo['UDID'] = $item['Data'];
                        break;
                    case './DevInfo/Man':
                        $deviceInfo['Manufacturer'] = $item['Data'];
                        break;
                    case './DevInfo/Mod':
                        $deviceInfo['Model'] = $item['Data'];
                        break;
                    case './DevDetail/Ext/Microsoft/DeviceName':
                        $deviceInfo['DeviceName'] = $item['Data'];
                        break;
                    case './Vendor/MSFT/DeviceInstanceService/Identity/Identity1/IMEI':
                        if (!empty($item['Data'])) {
                            $deviceInfo['IMEI'] = $item['Data'];
                        }
                        break;
                    case './Vendor/MSFT/DeviceInstanceService/Identity/Identity2/IMEI':
                        if (!empty($item['Data'])) {
                            if (!empty($deviceInfo['IMEI'])) {
                                $deviceInfo['IMEI'] .= " - " . $item['Data'];
                            } else {
                                $deviceInfo['IMEI'] = $item['Data'];
                            }
                        }
                        break;
                    case './DevDetail/SwV':
                        $deviceInfo['OSVersion'] = $item['Data'];
                        break;
                    case './Vendor/MSFT/DeviceInstanceService/Roaming':
                    case './Vendor/MSFT/DeviceInstanceService/Identity/Identity1/Roaming':
                    case './Vendor/MSFT/DeviceInstanceService/Identity/Identity2/Roaming':
                        $deviceInfo['DataRoamingEnabled'] = $item['Data'];
                        $deviceInfo['IsRoaming'] = $item['Data'];
                        break;
                    case './DevDetail/DevTyp':
                        $deviceInfo['DeviceType'] = $item['Data'];
                        break;
                    case './Vendor/MSFT/DeviceInstanceService/Identity/Identity1/PhoneNumber':
                        if (!empty($item['Data'])) {
                            $deviceInfo['PhoneNumber'] = $item['Data'];
                        }
                        break;
                    case './Vendor/MSFT/DeviceInstanceService/Identity/Identity2/PhoneNumber':
                        if (!empty($item['Data'])) {
                            if (!empty($deviceInfo['PhoneNumber'])) {
                                $deviceInfo['PhoneNumber'] .= " - " . $item['Data'];
                            } else {
                                $deviceInfo['PhoneNumber'] = $item['Data'];
                            }
                        }
                        break;
                    case './DevDetail/Ext/WLANMACAddress':
                        $deviceInfo['WiFiMAC'] = $item['Data'];
                        break;
                    case './Vendor/MSFT/DeviceInstanceService/IMSI':
                    case './Vendor/MSFT/DeviceInstanceService/Identity/Identity1/IMSI':
                    case './Vendor/MSFT/DeviceInstanceService/Identity/Identity2/IMSI':
                        if (!empty($item['Data'])) {
                            $str = substr($item['Data'], 0, 3) . "-" . substr($item['Data'], 3);
                            $deviceInfo['CurrentCountry'] = $str;
                            $deviceInfo['CurrentNetwork'] = $str;
                            $deviceInfo['HomeCountry'] = $str;
                            $deviceInfo['HomeNetwork'] = $str;
                        }
                        break;
                    case './DevDetail/Ext/Microsoft/CommercializationOperator':
                        if (!empty($item['Data'])) {
                            $deviceInfo['OperatorName'] = $item['Data'];
                        }
                        break;
                    case './vendor/MSFT/DMClient/Provider/MDMSERVER/Push/ChannelURI':
                        $deviceInfo['ChannelURI'] = $item['Data'];
                        break;
                    default:
                        break;
                }
            }
        }
        return $deviceInfo;
    }

    /**
     * receive request and enroll device.
     * @param  [DeviceInventory] $device, [array] $content, [int] $platform, [array] $serverInfo
     * @return [string] fileContent
     */
    private function _sendCommand($device, $content, $platform)
    {
        $deviceId = $device->getId();
        $event = new DeviceEventRepository();
        $request_type = $this->getRequestTypeByPlatformName(self::WP_CONTENT_TYPE_NAME);
        $eventContent = $event->getCommandByDeviceId($deviceId, $request_type);
        $response = null;
        if ($eventContent) {
            // set status of command to processing.
            $event->updateStatus($eventContent, self::WAITING);
            $eventName = $eventContent->getEventName();
            \MDMLogger::getInstance()->debug('', __LINE__ . "::::" . $eventName, array());
            switch ($eventName) {
                case self::GET_INFO:
                    $response = $this->_getInformationCommand($content, $eventContent, $platform);
                    break;
                case self::LOCK_DEVICE:
                    $response = $this->_lockDevice($content, $eventContent, $platform);
                    break;
                case self::GENERATE_PFN:
                    // get commandUuid of this command.
                    $commandUuid = $eventContent->getCommandUuid();
                    $response = $this->generatePFN($content, $commandUuid, $platform);
                    break;
                case self::UN_ENROLL:
                    $commandUuid = $eventContent->getCommandUuid();
                    $response = $this->_unEnrollCommand($content, $commandUuid, $platform);
                    break;
//                case self::INSTALLED_APPLICATION:
//                    $commandUuid = $eventContent->getCommandUuid();
//                    $response = $this->_installAppCommand($content, $commandUuid, $platform);
//                    break;
                default:
                    break;
            }
        }
        return $response;
    }

    /**
     * Command lock device
     * @param  [array] $content, [string] $eventContent, [int] $platform
     * @return [array] response
     */
    private function _lockDevice($content, $eventContent, $platform)
    {
        $header = $this->_replaceHeader($content);
        $libDir = \sfConfig::get("sf_lib_dir");
        $bodyContent = file_get_contents($libDir . "/Gcs/Services/xml/LockDevice.xml");
        $bodyContent = preg_replace("/command_uuid/i", $eventContent->getCommandUuid(), $bodyContent);
        $body = $this->_replaceBody($bodyContent);
        $xml = $header . $body;
        return $xml;
    }

    /**
     * receive request and enroll device.
     * @param  [array] $content, [string] $eventContent, [int]$platform
     * @return [array] response
     */
    private function _getInformationCommand($content, $eventContent, $platform)
    {
        $header = $this->_replaceHeader($content);
        $response = array();
        $response['CmdID'] = $eventContent->getCommandUuid();
        // create query get all information from device.
        $queries = "./DevDetail/Ext/Microsoft/DeviceName,./Vendor/MSFT/DeviceInstanceService/IMEI,";
        $queries.= "./DevInfo/Man,./DevInfo/Mod,./DevDetail/SwV,";
        $queries.= "./Vendor/MSFT/DeviceInstanceService/Roaming,./DevDetail/DevTyp,";
        $queries.= "./Vendor/MSFT/DeviceInstanceService/PhoneNumber,";
        $queries.= "./DevInfo/DevId,./DevDetail/Ext/WLANMACAddress,";
        $queries.= "./Vendor/MSFT/DeviceInstanceService/IMSI,./DevDetail/Ext/Microsoft/CommercializationOperator,";
        $queries.= "./Vendor/MSFT/DeviceInstanceService/Identity/Identity1/IMEI,";
        $queries.= "./Vendor/MSFT/DeviceInstanceService/Identity/Identity2/IMEI,";
        $queries.= "./Vendor/MSFT/DeviceInstanceService/Identity/Identity1/Roaming,";
        $queries.= "./Vendor/MSFT/DeviceInstanceService/Identity/Identity2/Roaming,";
        $queries.= "./Vendor/MSFT/DeviceInstanceService/Identity/Identity1/PhoneNumber,";
        $queries.= "./Vendor/MSFT/DeviceInstanceService/Identity/Identity2/PhoneNumber,";
        $queries.= "./Vendor/MSFT/DeviceInstanceService/Identity/Identity1/IMSI,";
        $queries.= "./Vendor/MSFT/DeviceInstanceService/Identity/Identity2/IMSI";
        $queriesArray = explode(",", $queries);
        //build body content of get device information command.
        $bodyContent = $this->convertToXML($queriesArray, $response, "<Get></Get>");
        $bodyContent = preg_replace("/\<\?xml version=\"1.0\"\?\>/", "", $bodyContent);
        $body = $this->_replaceBody($bodyContent);
        $xmlResponse = $header . $body;
        return $xmlResponse;
    }

    /**
     * receive request and enroll device.
     * @param  [array] $content, [string] $eventContent, [int]$platform
     * @return [array] response
     */
    private function _replaceHeader($content)
    {
        $libDir = \sfConfig::get("sf_lib_dir");
        $header = file_get_contents($libDir . "/Gcs/Services/xml/Header.xml");
        $header = preg_replace("/VerDTDValue/i", $content['SyncHdr']['VerDTD'], $header);
        $header = preg_replace("/VerProtoValue/i", $content['SyncHdr']['VerProto'], $header);
        $header = preg_replace("/SessionIDValue/i", $content['SyncHdr']['SessionID'], $header);
        $header = preg_replace("/mesIDValue/i", $content['SyncHdr']['MsgID'], $header);
        $header = preg_replace("/TargetURIValue/i", $content['SyncHdr']['Source']['LocURI'], $header);
        $header = preg_replace("/SourceURIValue/i", $content['SyncHdr']['Target']['LocURI'], $header);
        return $header;
    }

    /**
     * receive request and enroll device.
     * @param  [array] $content, [string] $eventContent, [int]$platform
     * @return [array] response
     */
    private function _replaceBody($bodyContent)
    {
        $libDir = \sfConfig::get("sf_lib_dir");
        $body = file_get_contents($libDir . "/Gcs/Services/xml/Body.xml");
        $body = preg_replace("/BodyValue/i", $bodyContent, $body);
        return $body;
    }

    /**
     * receive request and enroll device.
     * @param  [array] $info, [SimpleXMLElement] $xmlInfo, [int]$platform
     * @return [array] response
     */
    public function arrayToXml($info, &$xmlInfo)
    {
        foreach ($info as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xmlInfo->addChild("$key");
                    $this->arrayToXml($value, $subnode);
                } else {
                    $subnode = $xmlInfo->addChild("Item");
                    $this->arrayToXml($value, $subnode);
                }
            } else {
                $xmlInfo->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    /**
     * convert array to xml to reponse for device
     * @param  [array] $queries, [array] $response, [string]$node
     * @return [string] $xml
     */
    public function convertToXML($queries, $response, $node)
    {
        foreach ($queries as $query) {
            print_r($query, true);
            $test_array['Target'] = array(
                'LocURI' => $query
            );
            array_push($response, $test_array);
        }
        // creating object of SimpleXMLElement
        $xmlInfo = new \SimpleXMLElement($node);
        // function call to convert array to xml
        $this->arrayToXml($response, $xmlInfo);
        //saving generated xml file
        $xml = $xmlInfo->asXML();
        return $xml;
    }

    /**
     * receive request and enroll device.
     * @param  [array] $statuses
     * @return [string] status
     */
    private function _getCommandStatus($statuses)
    {
        $result = $this->getDataFromTag($statuses, 'Data', self::SUCCESS_STATUS);
        if ($result != FALSE) {
            return $result;
        }
        return self::ERROR;
    }

    /**
     * receive request and enroll device.
     * @param  [string] $commandUuid, [string] $status, [array]$content, [DeviceInventory] $device
     * @return [array] response
     */
    private function _handleResponse($commandUuid, $status, $content, $device)
    {
        $deviceInventoryModel = new DeviceInventoryRepository();
        $event = new DeviceEventRepository();
        $enrollRepository = new EnrollWPRepository();
        $deviceEvent = $event->getCommandByCommandUuid($commandUuid);
        if ($deviceEvent) {
            $deviceEvent->setModel($device->getModel());
            // if status is sucess.
            if ($status == self::SUCCESS_STATUS) {
                $deviceEvent->setStatus(self::COMPLETE);  // set status is finished.
                $eventName = $deviceEvent->getEventName();
                switch ($eventName) {
                    case self::GET_INFO:
                        $deviceData = $this->convertInfoFormRequest($content, 'Results');
                        $queryData['QueryResponses'] = $deviceData;
                        // save device information to database
                        $this->_deviceInformation($device, $queryData, $deviceEvent);
                        $event->pushNotification(array($deviceData['ChannelURI']), self:: WP_PUSH, null);
                        break;
                    case self::GENERATE_PFN:
                        $deviceData = $this->convertInfoFormRequest($content, 'Results');
                        $deviceInventoryModel->updateRegId($device, $deviceData['ChannelURI']);
                        $enrollRepository->updateChannelUriMdm($device->getUdid(), $deviceData['ChannelURI']);
                        $event->pushNotification(array($deviceData['ChannelURI']), self:: WP_PUSH, null);
                        break;
                    case self::LOCK_DEVICE:
                        // Save temporary PIN to database
                        $pin = $content['SyncBody']['Results']['Item']['Data'];
                        if ($pin) {
                            \MDMLogger::getInstance()->info("Lock Windows phone", "\n Device $device->id change new passcode: $pin");
                            $deviceInventoryModel->updatePasscode($device, base64_encode($pin));
                        }
                        break;
                    default:
                        break;
                }
            }
            // if status is error.
            else {
                $event->updateNoteAndStatus($deviceEvent, self:: INVALID_FORMAT_CODE, self::ERROR);
            }
            $deviceEvent->save();
            $deviceInventoryModel->setUpdateAtDeviceInventory($device->getId());
        }
        return null;
    }

    /**
     * receive request and enroll device.
     * @param  [DeviceInventory] $device, [array] $content, [DeviceEvent]$deviceEvent
     * @return [array] response
     */
    private function _deviceInformation($device, $content, $deviceEvent)
    {
        if (isset($content['QueryResponses'])) {
            $queryData = $content['QueryResponses'];
            // update information from request of device to database
            $deviceInventory = new DeviceInventoryRepository();
            $deviceInventory->updateDeviceInfo($device, $queryData, $deviceEvent);
            // save Inventory Information 
            $deviceInventory->updateInventoryInfo($device, $queryData);
        }
    }

    /**
     * command with status is success to response to mdm
     * @param  [array] $content
     * @return [string] response
     */
    private function _returnSuccess($content)
    {
        $header = $this->_replaceHeader($content);
        $body = $this->_replaceBody("");
        $xmlResponse = $header . $body;
        return $xmlResponse;
    }

    /**
     * generate command to get package family name to push notification
     * @param  [array] $content, [string] $commandUuid, [int] $platform
     * @return [string] response
     */
    public function generatePFN($content, $commandUuid, $platform)
    {
        // read header from header.xml
        $header = $this->_replaceHeader($content);
        // read body content of command generate Package family name
        $libDir = \sfConfig::get("sf_lib_dir");
        $push = file_get_contents($libDir . "/Gcs/Services/xml/push.xml");
        // replace commandUdid in body content
        $bodyContent = preg_replace("/CommandUUID/i", $commandUuid, $push);
        $wpAuthor = \sfConfig::get("app_windows_phone_author_data");
        $bodyContent = preg_replace("/SyncBody_Push_PFN_Data/", $wpAuthor['pfn'], $bodyContent);
        //get body from body.xml
        $body = $this->_replaceBody($bodyContent);
        $xmlResponse = $header . $body;
        return $xmlResponse;
    }

    /**
     * build unenroll command
     * @param  [array] $content, [string] $commandUuid, [int]$platform
     * @return [array] response
     */
    public function _unEnrollCommand($content, $commandUuid, $platform)
    {
        // read header from header.xml
        $header = $this->_replaceHeader($content);
        // get commandUuid of this command.
        // read body content of command generate Package family name
        $libDir = \sfConfig::get("sf_lib_dir");
        $unenroll = file_get_contents($libDir . "/Gcs/Services/xml/Unenroll.xml");
        // replace commandUdid in body content
        $bodyContent = preg_replace("/CommandUUID/i", $commandUuid, $unenroll);
        //get body from body.xml
        $body = $this->_replaceBody($bodyContent);
        $xmlResponse = $header . $body;
        return $xmlResponse;
    }

    /**
     * generate command to get package family name to push notification
     * @param  [array] $content
     * @return [int] userID
     */
    private function _getUserIdInRequest($content)
    {
        if (isset($content['SyncHdr']['Cred']['Data'])) {
            $userData = explode(":", base64_decode($content['SyncHdr']['Cred']['Data']));
            $username = $userData[0];
            $userRespository = new \Gcs\Repository\UserRepository();
            $userInfo = $userRespository->getUserInfoByUsername($username);
            if ($userInfo) {
                return $userInfo->getId();
            }
        }
        return null;
    }

    /**
     * get commandUuid from command request from device.
     * @param  [array] $content
     * @return [string] commandUuid
     */
    private function _getCommandUuid($content)
    {
        $commandStatuses = $content['SyncBody']['Status'];
        if (is_array(end($commandStatuses))) {
            foreach ($commandStatuses as $command) {
                if (substr($command['CmdRef'], 0, 3) == 'mdm') {
                    return $command['CmdRef'];
                }
            }
        } else {
            if (substr($commandStatuses['CmdRef'], 0, 3) == 'mdm') {
                return $commandStatuses['CmdRef'];
            }
        }
        return 0;
    }

    /**
     * check event device is unenrolled by user
     * @param  [array] $content
     * @return 
     */
    private function _unenrollByUser($content)
    {
        if (isset($content['SyncBody']['Alert'])) {
            $alerts = $content['SyncBody']['Alert'];
            $result = $this->getDataFromTag($alerts, 'Data', self::UNENROLL_BYUSER);
            if ($result != FALSE) {
                $this->_updateCommandAndEnrollStatus($content);
            }
        }
    }

    /**
     * update command and enroll status of device to error when device is unenroll
     * @param  [array] $content
     * @return 
     */
    private function _updateCommandAndEnrollStatus($content)
    {
        $deviceData = $this->convertInfoFormRequest($content, "Replace");
        $deviceInventory = new DeviceInventoryRepository();
        $deviceEvent = new DeviceEventRepository();
        $enrollRepository = new EnrollWPRepository();
        $device = $deviceInventory->getDeviceInventorybyUDID($deviceData['UDID']);
        if ($device) {
            $deviceInventory->updateRegId($device, null);
            $enrollRepository->unEnrollWP($deviceData['UDID']);
            if ($device->getEnrollStatus() == self::ENROLL_CODE) {
                $deviceInventory->updateEnrollStatus($device, self::ALLOW_REENROLL_CODE);
                $deviceEvent->changeCommandStatusError($device->getId());
            }
        }
    }

    /**
     * get data from tag in xml content
     * @param  [array] $content, [string] $tag, [string]$valueCompare
     * @return 
     */
    private function getDataFromTag($contents, $tag, $valueCompare)
    {
        if (is_array(end($contents))) {
            foreach ($contents as $content) {
                if ($content[$tag] == $valueCompare) {
                    return $content[$tag];
                }
            }
        } else {
            if ($contents[$tag] == $valueCompare) {
                return $contents[$tag];
            }
        }
        return FALSE;
    }

    /**
     * check event device is unenrolled 
     * @param  [DeviceInventory] $device,[array] $content, [string] $response
     * @return 
     */
    private function _deviceIsUnenrolled($device, $content, $response)
    {
        $enrollStatus = $device->getEnrollStatus();
        if ($enrollStatus == self::UNENROLL_CODE) {
            $commandUuid = rand(1, 1000);
            return $this->_unEnrollCommand($content, $commandUuid, 3);
        }
        return $response;
    }

    /**
     * check event device is unenrolled 
     * @param  [array] $content,[string] $udid
     * @return boolean
     */
    private function _getPFN($content, $udid)
    {
        $enrollRepository = new EnrollWPRepository();
        if (isset($content['SyncBody']['Status'])) {
            $statuses = $content['SyncBody']['Status'];
            $status = $this->_getCommandStatus($statuses);
            if ($status != self::ERROR) {
                $deviceData = $this->convertInfoFormRequest($content, 'Results');
                return $enrollRepository->updateChannelUriMdm($udid, $deviceData['ChannelURI']);
            }
        }
        return false;
    }

}
