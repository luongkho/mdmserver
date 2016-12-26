<?php

/**
 * Description: class for IOS mdm service
 *  Process enroll for IOS platform
 *  Verify status enroll device
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Services;

use Gcs\Services\CheckinAbstract;
use Gcs\Repository\DeviceInventoryRepository;

class CheckinIos extends CheckinAbstract
{

    // error code
    const INVALID_FORMAT_CODE = "1002";
    const REGID_UDID_NULL = "1012";
    // enroll status
    const ENROLLED = 0;
    const UNENROLL = 1;
    const ALLOW_REENROLL = 2;
    // common info
    const PLATFORM_IOS = "ios";
    const GETINFO = "DeviceInformation";
    const SECURITY = "SecurityInfo";
    const INSTALLED_APP = "InstalledApplicationList";
    const UNENROLL_COMMAND = "Unenroll";
    const AUTHENTICATE = "Authenticate";
    const INSTALL_APPICATION = "InstallApplication";

    /**
     * Loading pakaged openssl then general Self Signed from CSR
     * @return String
     */
    public function enroll($controller, $content, $log)
    {
        $response = array();
        if (isset($content['MessageType']) && isset($content['UDID'])) {
            if ($content['UDID'] == null) {
                \MDMLogger::getInstance()->error('', $log . "Parameter UDID or RegistrationID is invalid", array());
                $response = $this->buildCommand(self::REGID_UDID_NULL);
                return $response;
            }
            switch ($content['MessageType']) {
                case self::AUTHENTICATE:
                    break;
                default:
                    $this->_saveOrUpdateDevice($content, $log);
                    break;
            }
        }
        // format of request from device is invalid
        else {
            $response = $this->buildCommand(self::INVALID_FORMAT_CODE);
            \MDMLogger::getInstance()->debug('', __LINE__ . "::::" . print_r($response, true), array());
        }
        return $response;
    }

    /**
     * check enroll status for ios device.
     * @param  [DeviceInventory] $device, [string] $deviceToken, [array]$content, [string] $log
     * [string] $pushMagic, [string] $log
     * @return [boolean] 
     */
    private function _checkEnrollStatus($device, $deviceToken, $content, $log)
    {
        $flag = true;
        $enrollStatus = $device->getEnrollStatus();
        $pushMagic = $content['PushMagic'];
        switch ($enrollStatus) {
            case self::ENROLLED: // device is enrolled
                $unlockToken = base64_encode($content['UnlockToken']);
                if ($device->getPushMagic() != $pushMagic || $device->getUnlockToken() != $unlockToken) {
                    $device->setPushMagic($pushMagic);
                    $device->setUnlockToken($unlockToken);
                    $device->save();
                    $this->saveAndPushCommand($device, null, $pushMagic, self::INSTALL_APPICATION, self::PLATFORM_IOS, $log);
                }
                $flag = false;
                break;
            case self::UNENROLL: // device is unenrolled
                $device->setRegistrationId($deviceToken);
                $this->saveAndPushCommand($device, null, $pushMagic, self::UNENROLL_COMMAND, self::PLATFORM_IOS, $log, false);
                \MDMLogger::getInstance()->info('', $log . "Device is unenrolled", array());
                $flag = false;
                break;
            default:
                $device->setEnrollStatus(self::ENROLLED);
                break;
        }
        return $flag;
    }

    /**
     * save or update information of device to database
     * @param  [array] $content, [string] $log
     * @return 
     */
    private function _saveOrUpdateDevice($content, $log)
    {
        $udid = $content['UDID'];
        $deviceToken = base64_encode($content['Token']);
        $pushMagic = $content['PushMagic'];
        $deviceInventoryModel = new DeviceInventoryRepository();
        $device = $deviceInventoryModel->getDeviceInventorybyUDID($udid);
        // check device is enrolled or not.
        $unenroll = true;
        if (!$device) {
            $device = new \DeviceInventory();
        } else {
            $unenroll = $this->_checkEnrollStatus($device, $deviceToken, $content, $log);
        }
        if ($unenroll) {
            $deviceInventoryModel->updateDeviceInventory($device, $deviceToken, null, $udid, $content, 2);
            // save and push notification for 3 commands getDeviceInfo, getSecurityInfo, getInstalledAppList
            $this->saveAndPushCommand($device, null, $pushMagic, self::GETINFO, self::PLATFORM_IOS, $log);
            $this->saveAndPushCommand($device, null, $pushMagic, self::SECURITY, self::PLATFORM_IOS, $log);
            $this->saveAndPushCommand($device, null, $pushMagic, self::INSTALLED_APP, self::PLATFORM_IOS, $log);
            // save and push notification install App Container.
            $this->saveAndPushCommand($device, null, $pushMagic, self::INSTALL_APPICATION, self::PLATFORM_IOS, $log);
        }
    }

}
