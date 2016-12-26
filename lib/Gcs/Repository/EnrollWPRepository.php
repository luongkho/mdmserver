<?php

/**
 * Description: Enrollment process of windows phone service
 * get, set information of device to EnrollWP table
 * update status of each device
 * Modify History:
 *  September 10, 2015: tannc initial version
 */

namespace Gcs\Repository;

class EnrollWPRepository
{

    const ENROLLED_WP = 3;
    const WAITING_MDM = 1;
    const WAITING_APP = 2;

    /**
     * get device inventory by userID and udid
     * @param [string] $userID, [string] $udid 
     * @return [Object] EnrollWp 
     */
    public function getDeviceByUserIDMDM($userID, $udid)
    {
        $enrollWP = \EnrollWpTable::getInstance()->findOnebyUserIdAndUdidAndStatus($userID, $udid, self::ENROLLED_WP);
        if ($enrollWP) {
            return $enrollWP;
        } else {
            $enrollWP = \EnrollWpTable::getInstance()->findOneByUdid($udid);
            if (!$enrollWP) {
                $enrollWP = \EnrollWpTable::getInstance()->findOnebyUserIdAndStatusOrStatus($userID, self::WAITING_APP, self::WAITING_MDM);
                if ($enrollWP) {
                    return $enrollWP;
                } else {
                    return FALSE;
                }
            } else {
                return $enrollWP;
            }
        }
    }

    /**
     * get device inventory by userID and hardwareId
     * @param [string] $userID 
     * @return [Object] EnrollWp 
     */
    public function getDeviceByUserIDApp($userID, $hardwareId)
    {
        $enrollWP = \EnrollWpTable::getInstance()->findOnebyUserIdAndHardwareIdAndStatus($userID, $hardwareId, self::ENROLLED_WP);
        if ($enrollWP) {
            return $enrollWP;
        } else {
            $enrollWP = \EnrollWpTable::getInstance()->findOneByHardwareId($hardwareId);
            if (!$enrollWP) {
                $enrollWP = \EnrollWpTable::getInstance()->findOnebyUserIdAndStatusOrStatus($userID, self::WAITING_APP, self::WAITING_MDM);
                if ($enrollWP) {
                    return $enrollWP;
                } else {
                    return FALSE;
                }
            } else {
                return $enrollWP;
            }
        }
    }

    /**
     * get device inventory by userID
     * @param [string] $userID 
     * @return [Object] EnrollWp 
     */
    public function _checkStatusEnrollWP($enrollWP, $udid)
    {
        $status = $enrollWP->getStatus();
        if ($status != ENROLLED_WP) {
            return $enrollWP;
        } else {
            if ($enrollWP->getUdid() != $udid) {
                return 1;
            } else {
                return null;
            }
        }
    }

    /**
     * get device inventory by userID
     * @param [string] $enrollWP, [int]$userID, [string] $udid, [string]$hardwareID, [int]$status
     * @return [Object] EnrollWp 
     */
    public function updateInfoEnrollWP($enrollWP, $userID, $udid, $hardwareID, $status)
    {
        $enrollWP->setUserId($userID);
        $enrollWP->setStatus($status);
        if (!is_null($udid)) {
            $enrollWP->setUdid($udid);
        }
        if (!is_null($hardwareID)) {
            $enrollWP->setHardwareId($hardwareID);
        }
        $this->checkUdidAndHardwareId($enrollWP);
        $enrollWP->save();
    }

    /**
     * check udid and hardwareID of one record in enroll_wp table
     * @param [string] $enrollWP 
     * @return  
     */
    public function checkUdidAndHardwareId($enrollWP)
    {
        $udid = $enrollWP->getUdid();
        $hardwareID = $enrollWP->getHardwareId();
        if (!is_null($udid) && !is_null($hardwareID)) {
            $enrollWP->setStatus(self::ENROLLED_WP);
        }
    }

    /**
     * updsate channelURI mdm for device
     * @param type $udid
     * @param type $channelUri
     */
    public function updateChannelUriMdm($udid, $channelUri)
    {
        $enrollWpInfo = $this->getEnrollWPByUdid($udid);
        if ($enrollWpInfo) {
            $enrollWpInfo->setChanneluriMdm($channelUri);
            $enrollWpInfo->save();
            return true;
        }
        return false;
    }

    /**
     * updsate channelURI app for device
     * @param type $hardwareId
     * @param type $channelUri
     */
    public function updateChannelUriApp($hardwareId, $channelUri)
    {
        $enrollWpInfo = $this->getEnrollWPByHardwareId($hardwareId);
        if ($enrollWpInfo) {
            $enrollWpInfo->setChanneluriApp($channelUri);
            $enrollWpInfo->save();
            return true;
        }
        return false;
    }

    /**
     * get enrollWP by hardware Id
     * @param [string] $udid 
     * @return [Object] enrollWP
     */
    public function getEnrollWPByUdid($udid)
    {
        return \EnrollWpTable::getInstance()->findOneByUdid($udid);
    }

    /**
     * get enrollWP by hardware Id
     * @param [string] $hardwareId 
     * @return [Object] enrollWP
     */
    public function getEnrollWPByHardwareId($hardwareId)
    {
        return \EnrollWpTable::getInstance()->findOneByHardwareId($hardwareId);
    }

    /**
     * unenroll for platform windows phone
     * @param [string] $udid 
     * @return [Object] enrollWP
     */
    public function unEnrollWP($udid)
    {
        $enrollWP = \EnrollWpTable::getInstance()->findOneByUdid($udid);
        if ($enrollWP) {
            $enrollWP->setChanneluriMdm(null);
            $enrollWP->setChanneluriApp(null);
            $enrollWP->setStatus(2);
            $enrollWP->save();
        }
    }

}
