<?php

/**
 * Description: Device inventory service
 *  Get information of devices
 *  Save, update information of each device to DeviceInventory table
 * Modify History:
 *  September 10, 2015: tannc initial version
 */

namespace Gcs\Repository;

use Gcs\Repository\DeviceApplicationRepository;
use Gcs\Repository\UserRepository;

class DeviceInventoryRepository
{

    const DEVICE_INFO = "Device";
    const NETWORK_INFO = "Operator Network";
    const PROFILES_INFO = "Profiles";
    const SECURITY_INFO = "Security";
    const SIM_INFO = "Sim";
    const ENROLLED = 0;
    const ERR_DEVICE_NOT_EXIST = "E2008";
    const IOS_PLATFORM = 2;

    /**
     * save information of device to DeviceInventory table
     * @param type $userId [int], platform [string]
     * @return type [boolean]
     */
    public function saveData($user_id = null, $platform = null)
    {
        $sfContext = \sfContext::getInstance();
        $cur_user_id = $sfContext->getUser()->getDecorator()->getUserId();
        if ($this->validate($user_id, $platform)) {
            /* Save Device */
            $userRep = new UserRepository();
            $user = $userRep->getUserById($user_id);
            $device = new \DeviceInventory();
            $device->setUserId($user_id);
            $device->setPlatform($platform);
            $device->setOwnerName($user->getFullName());
            $device->setOwnerEmail($user->getEmail());
            $device->save();
            $device_id = $device->getId();
            /* Save Event */
            $event = new \DeviceEvent();
            $event->setDeviceId($device_id);
            $event->setSentBy($cur_user_id);
            $event->save();

            return $device_id;
        }

        return false;
    }

    /**
     * validate user_id and platform
     * @param type $userId [int], platform [string]
     * @return type [boolean]
     */
    public function validate($userId, $platform)
    {
        return !empty($userId) && !empty($platform);
    }

    /**
     * update HardwareId to table DeviceInventory if available.
     * @param [DeviceInventory] $device, [int] $userID, [string] $udid
     * @return 
     */
    public function updateHardwareId($device, $userID, $udid)
    {
        $enrollWPInfo = \EnrollWpTable::getInstance()->findOneByUserIdAndUdid($userID, $udid);
        if ($enrollWPInfo) {
            $hardwareId = $enrollWPInfo->getHardwareId();
            if (!empty($hardwareId)) {
                $device->setHardwareId($hardwareId);
                $device->setUdid($udid);
                $device->save();
            }
        }
    }

    /**
     * updsate information of device when enroll
     * @param type $device [DeviceInventory], $regId [string], $userId [int],
     * @param type $udid[string], [array] $content, [int]$flatform
     * @return 
     */
    public function updateDeviceInventory($device, $regId, $userId, $udid, $content, $flatform)
    {
        $device->setUdid($udid);
        $device->setEnrollStatus(0);
        // set basic information of device.

        $device->setRegistrationId($regId);
        $device->setPlatform($flatform);
        if ($userId != null) {
            $device->setUserId($userId);
            $userRep = new UserRepository();
            $user = $userRep->getUserById($userId);
            $device->setOwnerName($user->getFullName());
            $device->setOwnerEmail($user->getEmail());
        }
        if (isset($content['Manufacturer'])) {
            $device->setManufacturer($content['Manufacturer']);
        }
        if (isset($content['DeviceName'])) {
            $device->setDeviceName($content['DeviceName']);
        }
        if (isset($content['Model'])) {
            $device->setModel($content['Model']);
        }
        if (isset($content['PushMagic'])) {
            $device->setPushMagic($content['PushMagic']);
        }
        if (isset($content['UnlockToken'])) {
            $device->setUnlockToken(base64_encode($content['UnlockToken']));
        }

        $device->save();
//    $this->updateInventoryInfo($device, $content);
    }

    /**
     * updsate information of device when enroll for windows phone
     * @param type $device [DeviceInventory], $devToken [string], $userId [int],
     * @param type $udid[string], [array] $content, [int]$flatform
     * @return 
     */
    public function updateDeviceInventoryWindowsPhone($device, $devToken, $userId, $udid, $content, $flatform)
    {
        $device->setUdid($udid);
        $device->setEnrollStatus(0);
        // set basic information of device.

        $device->setDeviceToken($devToken);
        $device->setPlatform($flatform);
        if ($userId != null) {
            $device->setUserId($userId);
            $userRep = new UserRepository();
            $user = $userRep->getUserById($userId);
            $device->setOwnerName($user->getFullName());
            $device->setOwnerEmail($user->getEmail());
        }
        if (isset($content['Manufacturer'])) {
            $device->setManufacturer($content['Manufacturer']);
        }
        if (isset($content['DeviceName'])) {
            $device->setDeviceName($content['DeviceName']);
        }
        if (isset($content['Model'])) {
            $device->setModel($content['Model']);
        }
        if (isset($content['PushMagic'])) {
            $device->setPushMagic($content['PushMagic']);
        }
        if (isset($content['UnlockToken'])) {
            $device->setUnlockToken(base64_encode($content['UnlockToken']));
        }

        $device->save();
//    $this->updateInventoryInfo($device, $content);
    }

    /**
     * updsate registrationID for device
     * @param type [DeviceInventory] $device, [string] $deviceToken
     * @return 
     */
    public function updateDeviceToken($device, $deviceToken)
    {
        $device->setDeviceToken($deviceToken);
        $device->save();
    }

    /**
     * updsate registrationID for device
     * @param type [DeviceInvetory] $device, [string] $regId
     * @return 
     */
    public function updateRegId($device, $regId)
    {
        $device->setRegistrationId($regId);
        $device->save();
    }

    /**
     * update passcode of device
     * @param type [DeviceInvetory] $device, [string] $passcode
     * @return  
     */
    public function updatePasscode($device, $passcode)
    {
        $device->setPasscode($passcode);
        $device->save();
    }

    /**
     * update information from request of device inventory to database
     * @param type $device[deviceInventory], $queryData [array], $deviceEvent [deviceEvent]
     * @return  
     */
    public function updateDeviceInfo($device, $queryData, $deviceEvent)
    {
        if (isset($queryData['DeviceName'])) {
            $device->setDeviceName($queryData['DeviceName']);
        }
        if (isset($queryData['OSVersion'])) {
            $device->setVersion($queryData['OSVersion']);
        }
        if (isset($queryData['IMEI'])) {
            $device->setImei($queryData['IMEI']);
        }
        if (isset($queryData['Manufacturer'])) {
            $device->setManufacturer($queryData['Manufacturer']);
        }
        if (isset($queryData['ProductName'])) {
            $device->setProductName($queryData['ProductName']);
        }
        if (isset($queryData['WiFiMAC'])) {
            $device->setWifiMacAddress($queryData['WiFiMAC']);
        }
        if (isset($queryData['DeviceType'])) {
            $device->setDeviceType($queryData['DeviceType']);
        }
        if (isset($queryData['Model'])) {
            $device->setModel($queryData['Model']);
        }
        // Force updated time
//    $time = date('Y-m-d H:i:s', time());
//    $device->setUpdatedAt($time);
        $device->save();
        if ($deviceEvent != null) {
            if (isset($queryData['Model'])) {
                $deviceEvent->setModel($queryData['Model']);
            }
            $deviceEvent->save();
        }
        $this->setUpdateAtDeviceInventory($device->getId());
    }

    /**
     * update information from request of inventory information to database
     * @param type $device [deviceInventory], $queryData [array]
     * @return  
     */
    public function updateInventoryInfo($device, $queryData)
    {
        try {
            $this->updateDeviceInformation($device, $queryData);
            $this->updateNetWorkInfo($device, $queryData);
            $this->updateSimInfo($device, $queryData);
//            $this->setUpdateAtDeviceInventory($device->getId());
            return true;
        } catch (Exception $ex) {

            return false;
        }
    }

    /**
     * update information of device from request of inventory information to database
     * @param type $device [deviceInventory], $queryData [array]
     * @return  
     */
    public function updateDeviceInformation($device, $queryData)
    {
        $invenAttGroup = \InventoryAttributeGroupTable::getInstance()->findOneByName(self::DEVICE_INFO);
        if (isset($invenAttGroup)) {
            $deviceInvenGroupId = $invenAttGroup->getId();
            $deviceId = $device->getId();
            $platform = $device->getPlatform();

            $result = array();
//      foreach ($queryData as $key => $value) {
//          $newData[$key] = $value;
//      }
            if (isset($queryData['DeviceName'])) {
                $result = array_merge($result, array('device_name' => $queryData['DeviceName']));
            }
            if (isset($queryData['Manufacturer'])) {
                $result = array_merge($result, array('manufacturer' => $queryData['Manufacturer']));
            }
            if (isset($queryData['ProductName'])) {
                $result = array_merge($result, array('product_name' => $queryData['ProductName']));
            }
            if (isset($queryData['OSVersion'])) {
                $result = array_merge($result, array('software_version' => $queryData['OSVersion']));
            }
            if (isset($queryData['SerialNumber'])) {
                $result = array_merge($result, array('serial_number' => $queryData['SerialNumber']));
            }
            if (isset($queryData['IMEI'])) {
                $result = array_merge($result, array('imei' => $queryData['IMEI']));
            }
            if (isset($queryData['UDID'])) {
                $result = array_merge($result, array('udid' => $queryData['UDID']));
            }
            if (isset($queryData['WiFiMAC'])) {
                $result = array_merge($result, array('wifi_mac_address' => $queryData['WiFiMAC']));
            }
            if (isset($queryData['BluetoothMAC'])) {
                $result = array_merge($result, array('bluetooth_mac_address' => $queryData['BluetoothMAC']));
            }
            if (isset($queryData['IsStoreAccountActive'])) {
                $result = array_merge($result, array('store_account_active' => strval(intval($queryData['IsStoreAccountActive']))));
            }
            if (isset($queryData['IsNotDisturbActive'])) {
                $result = array_merge($result, array('do_not_disturb_device' => strval(intval($queryData['IsNotDisturbActive']))));
            }
            if (isset($queryData['IsDoNotDisturbInEffect'])) {
                $result = array_merge($result, array('do_not_disturb_device' => strval(intval($queryData['IsDoNotDisturbInEffect']))));
            }
            if (isset($queryData['DeviceType'])) {
                $result = array_merge($result, array('device_type' => $queryData['DeviceType']));
            }
            if (isset($queryData['EasIdentifier'])) {
                $result = array_merge($result, array('eas_identifier' => $queryData['EasIdentifier']));
            }
            if (isset($queryData['EASDeviceIdentifier'])) {
                $result = array_merge($result, array('eas_identifier' => $queryData['EASDeviceIdentifier']));
            }
            if (isset($queryData['AvailableDeviceCapacity'])) {
                if ($platform == self::IOS_PLATFORM) {
                    $queryData['AvailableDeviceCapacity'] = $queryData['AvailableDeviceCapacity'] * 1024;
                }
                $result = array_merge($result, array('available_device_capacity' => $queryData['AvailableDeviceCapacity']));
            }
            if (isset($queryData['DeviceCapacity'])) {
                if ($platform == self::IOS_PLATFORM) {
                    $queryData['DeviceCapacity'] = $queryData['DeviceCapacity'] * 1024;
                }
                $result = array_merge($result, array('device_capacity' => $queryData['DeviceCapacity']));
            }
            if (isset($queryData['StorageName'])) {
                $result = array_merge($result, array('storage_name' => $queryData['StorageName']));
            }
            if (isset($queryData['Encrypted'])) {
                $result = array_merge($result, array('encrypted' => strval(intval($queryData['Encrypted']))));
            }
            if (isset($queryData['CurrentDataRoaming'])) {
                $result = array_merge($result, array('current_roaming_state' => strval(intval($queryData['CurrentDataRoaming']))));
            }
//      $value = serialize($result);
            $this->_saveInventoryInfo($deviceId, $deviceInvenGroupId, $result);
        }
    }

    /**
     * update information of sim from request of inventory information to database
     * @param type $device [deviceInventory], $queryData [array]
     * @return  
     */
    public function updateSimInfo($device, $queryData)
    {
        $invenAttGroup = \InventoryAttributeGroupTable::getInstance()->findOneByName(self::SIM_INFO);
        if (isset($invenAttGroup)) {
            $deviceInvenGroupId = $invenAttGroup->getId();
            $deviceId = $device->getId();

            $result = array();
//      foreach ($queryData as $key => $value) {
//          $newData[$key] = $value;
//      }

            $result = array();
            if (isset($queryData['ICCID'])) {
                $result = array_merge($result, array('iccd' => $queryData['ICCID']));
            }
            if (isset($queryData['PhoneNumber'])) {
                $result = array_merge($result, array('phone_number' => $queryData['PhoneNumber']));
            }
//      $value = serialize($result);
            $this->_saveInventoryInfo($deviceId, $deviceInvenGroupId, $result);
        }
    }

    /**
     * update information of network from request of inventory information to database
     * @param type $device [deviceInventory], $queryData [array]
     * @return  
     */
    public function updateNetWorkInfo($device, $queryData)
    {
        $invenAttGroup = \InventoryAttributeGroupTable::getInstance()->findOneByName(self::NETWORK_INFO);
        if (isset($invenAttGroup)) {
            $deviceInvenGroupId = $invenAttGroup->getId();
            $deviceId = $device->getId();
            $result = array();
//      foreach ($queryData as $key => $value) {
//          $newData[$key] = $value;
//      }
            $result = array();
            if (isset($queryData['OperatorName'])) {
                $result = array_merge($result, array('operator_name' => $queryData['OperatorName']));
            }
            if (isset($queryData['CurrentCarrierNetwork'])) {
                $result = array_merge($result, array('operator_name' => $queryData['CurrentCarrierNetwork']));
            }
            if (isset($queryData['CurrentCountry'])) {
                $result = array_merge($result, array('current_country' => $queryData['CurrentCountry']));
            }
            if (isset($queryData['CurrentMCC'])) {
                $result = array_merge($result, array('current_country' => $queryData['CurrentMCC']));
            }
            if (isset($queryData['CurrentNetwork'])) {
                $result = array_merge($result, array('current_network' => $queryData['CurrentNetwork']));
            }
            if (isset($queryData['CurrentMNC'])) {
                $result = array_merge($result, array('current_network' => $queryData['CurrentMNC']));
            }
            if (isset($queryData['HomeCountry'])) {
                $result = array_merge($result, array('home_country' => $queryData['HomeCountry']));
            }
            if (isset($queryData['SubscriberMCC'])) {
                $result = array_merge($result, array('home_country' => $queryData['SubscriberMCC']));
            }
            if (isset($queryData['HomeNetwork'])) {
                $result = array_merge($result, array('home_network' => $queryData['HomeNetwork']));
            }
            if (isset($queryData['SubscriberMNC'])) {
                $result = array_merge($result, array('home_network' => $queryData['SubscriberMNC']));
            }
            if (isset($queryData['CurrentRoamingState'])) {
                $result = array_merge($result, array('data_roaming' => strval(intval($queryData['CurrentRoamingState']))));
            }
            if (isset($queryData['IsRoaming'])) {
                $result = array_merge($result, array('data_roaming' => strval(intval($queryData['IsRoaming']))));
            }
            if (isset($queryData['DataRoamingEnabled'])) {
                $result = array_merge($result, array('current_roaming_state' => strval(intval($queryData['DataRoamingEnabled']))));
            }
            if (isset($queryData['HotspotEnabled'])) {
                $result = array_merge($result, array('hotspot_enabled' => strval(intval($queryData['HotspotEnabled']))));
            }
            if (isset($queryData['PersonalHotspotEnabled'])) {
                $result = array_merge($result, array('hotspot_enabled' => strval(intval($queryData['PersonalHotspotEnabled']))));
            }

//      $value = serialize($result);
            $this->_saveInventoryInfo($deviceId, $deviceInvenGroupId, $result);
        }
    }

    /**
     * Update purchase and warranty_end date of device
     * @param array [device info] $data 
     * @return [DeviceInventory] $device
     */
    public function updateTag($data)
    {
        $device = \DeviceInventoryTable::getInstance()->findOneBy('id', $data['id']);
        if (!$device) {
            return self::ERR_DEVICE_NOT_EXIST;
        }

        $device->setOrganization($data['organization']);
        $device->setLocation($data['location']);
        if (!empty($data['purchase_date'])) {
            $device->setPurchaseDate($data['purchase_date']);
        } else  {
            $device->setPurchaseDate(NULL);
        }
        if (!empty($data['warranty_end'])) {
            $device->setWarrantyEnd($data['warranty_end']);
        } else  {
            $device->setWarrantyEnd(NULL);
        }
        $device->save();
        return $device;
    }

    /**
     * update information of security from request of inventory information to database
     * @param type $device [deviceInventory], $queryData [array]
     * @return type 
     */
    public function updateSecurityInfo($device, $queryData)
    {
        $invenAttGroup = \InventoryAttributeGroupTable::getInstance()->findOneByName(self::SECURITY_INFO);
        if (isset($invenAttGroup)) {
            $deviceInvenGroupId = $invenAttGroup->getId();
            $deviceId = $device->getId();

            $result = array();
            if (isset($queryData['EncryptionStatus'])) {
                $result = array_merge($result, array('encryption_status' => $queryData['EncryptionStatus']));
            }
            if (isset($queryData['HardwareEncryptionCaps'])) {
                $result = array_merge($result, array('hardware_encryption_capabilities' => $queryData['HardwareEncryptionCaps']));
            }
            if (isset($queryData['PasscodePresent'])) {
                $result = array_merge($result, array('passcode_set' => strval(intval($queryData['PasscodePresent']))));
            }
            if (isset($queryData['PasscodeCompliant'])) {
                $result = array_merge($result, array('passcode_compliance' => strval(intval($queryData['PasscodeCompliant']))));
            }
            $this->_saveInventoryInfo($deviceId, $deviceInvenGroupId, $result);

//            $this->setUpdateAtDeviceInventory($deviceId);
        }
    }

    /**
     * save value to inventory information table
     * @param [int] $deviceId , [int] $deviceInvenGroupId, [array] $value
     * @return  
     */
    private function _saveInventoryInfo($deviceId, $deviceInvenGroupId, $newData)
    {
        $inventoryInformation = \InventoryInformationTable::getInstance()->findOneByDeviceIdAndInventoryGroupId($deviceId, $deviceInvenGroupId);
        if ($inventoryInformation == null) {
            $inventoryInformation = new \InventoryInformation();
            $inventoryInformation->setDeviceId($deviceId);
            $inventoryInformation->setInventoryGroupId($deviceInvenGroupId);
            $inventoryInformation->setValue(serialize($newData));
        } else {
            $value = $inventoryInformation->getValue();
            $oldData = unserialize($value);
            // Update new data if exist
            foreach ($newData as $k => $v) {
                $oldData[$k] = $v;
            }
            $inventoryInformation->setValue(serialize($oldData));
        }

        $inventoryInformation->save();
    }

    /**
     * get latest date updated application list
     * @param [int] $deviceId
     * @return [type] [date]
     */
    private function latestInformationUpdated($deviceId)
    {
        $table = \InventoryInformationTable::getInstance();
        $latestDate = $table->createQuery('d')->where('d.device_id = ?', $deviceId)
                        ->orderBy('d.updated_at desc')->fetchOne();
        if (!$latestDate) {
            return 0;
        }
        return strtotime($latestDate->getUpdatedAt());
    }

    /**
     * get lastest date updated of attribute information
     * @param type $deviceId
     * @return [date]
     */
    private function latestupdateAtInventory($deviceId)
    {
        $table = \DeviceInventoryTable::getInstance();
        $latestDate = $table->findOneBy('id', $deviceId);

        if (!$latestDate) {
            return 0;
        }
        return strtotime($latestDate->getUpdatedAt());
    }

    /**
     * get lastest date updated of location 
     * @param type $deviceId
     * @return [date]
     */
    private function latestupdateAtLocation($deviceId)
    {
        $table = \DeviceLocationTable::getInstance();
        $latestDate = $table->createQuery('d')->where('d.device_id = ?', $deviceId)
                        ->orderBy('d.updated_at desc')->fetchOne();
        if (!$latestDate) {
            return 0;
        }
        return strtotime($latestDate->getUpdatedAt());
    }

    /**
     * set updated at for each device when information is changed.
     * @param type $deviceId
     * @return
     */
    public function setUpdateAtDeviceInventory($deviceId)
    {
        $updatedAt = 0;
        $deviceApp = new DeviceApplicationRepository();
        $deviceEventRes = new DeviceEventRepository();
        $lastUpdateApp = strtotime($deviceApp->latestAppUpdated($deviceId));
        $lastUpdateInfo = $this->latestInformationUpdated($deviceId);
        $lastUpdateLocation = $this->latestupdateAtLocation($deviceId);
        $lastUpdateInventory = $this->latestupdateAtInventory($deviceId);
        $lastUpdateEvent = $deviceEventRes->getLastCommandByDeviceId($deviceId);

        $updatedAt = max($lastUpdateApp, $lastUpdateInfo, $lastUpdateLocation, $lastUpdateEvent);

        if ($updatedAt > $lastUpdateInventory) {
            $deviceInventory = \DeviceInventoryTable::getInstance()->findOneBy('id', $deviceId);
            $time = date('Y-m-d H:i:s', $updatedAt);
            $deviceInventory->setUpdatedAt($time);
            $deviceInventory->save();
        }
    }

    /**
     * save device token from ios app to database
     * @param [DeviceInventory] $device , [array] $content
     * @return type 
     */
    public function saveAppDeviceToken($device, $content)
    {
        if (isset($content['DeviceToken'])) {
            $deviceToken = base64_encode($content['DeviceToken']);
            if ($device->getEnrollStatus() == self::ENROLLED) {
                $device->setDeviceToken($deviceToken);
            }
        }
        $device->save();
    }

    /**
     * save userinfo from ios app to database
     * @param [DeviceInventory] $device , [array] $content
     * @return type 
     */
    public function saveAppUserInfo($device, $content)
    {
        if (isset($content['Username'])) {
            $username = $content['Username'];
            $userRepository = new UserRepository();
            $userInfo = $userRepository->getUserInfoByUsername($username);
            if ($userInfo) {
                $device->setUserId($userInfo->getId());
                $device->setOwnerName($userInfo->getFullName());
                $device->setOwnerEmail($userInfo->getEmail());
            }
        }
        $device->save();
    }

    /**
     * get device inventory by device_id
     * @param [Int] $deviceId 
     * @return [DeviceInventory] $device
     */
    public function getDeviceInventory($deviceId)
    {
        return \DeviceInventoryTable::getInstance()->findOneBy("id", $deviceId);
    }

    /**
     * get device inventory by udid
     * @param [Int] $udid 
     * @return [DeviceInventory] $device
     */
    public function getDeviceInventorybyUDID($udid)
    {
        return \DeviceInventoryTable::getInstance()->findOneByUdid($udid);
    }

    /**
     * Save temporary passcode for iOS unlock
     * @param [Int] $deviceId 
     * @return [string] $passcode | FALSE
     */
    public function saveTempPassiOS($deviceId)
    {
        $device = $this->getDeviceInventory($deviceId);
        $passcode = base64_encode(rand(1000, 9999));
        $device->setPasscode($passcode);
        $device->save();
        return $passcode;
    }

    /**
     * Clear temporaty passcode for iOS when send command Unlock to backend
     * @param [Int] $deviceId 
     */
    public function clearTempPassiOS($deviceId)
    {
        $device = $this->getDeviceInventory($deviceId);
        $device->setPasscode(NULL);
        $device->save();
    }

    /**
     * update enroll status of device.
     * @param [DeviceInventory] $device ,[int] $enrollStatus
     */
    public function updateEnrollStatus($device, $enrollStatus)
    {
        $device->setEnrollStatus($enrollStatus);
        $device->save();
    }

    /**
     * get device inventory by hardware Id
     * @param [string] $hardwareId 
     * @return [Object] $devices
     */
    public function getDeviceInventorybyHardwareId($hardwareId)
    {
        return \DeviceInventoryTable::getInstance()->findOneByHardwareId($hardwareId);
    }

}
