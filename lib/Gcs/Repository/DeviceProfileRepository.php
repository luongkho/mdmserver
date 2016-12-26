<?php

/**
 * Description: Device Profile 
 * Get, Update profile for device
 * Modify History:
 *  September 10, 2015: tannc initial version
 */

namespace Gcs\Repository;

class DeviceProfileRepository
{

    /**
     * get all Profile from Device
     * @param [integer] $deviceId
     * @return [object] $deviceProfiles
     */
    public function getDeviceProfile($deviceId)
    {
        $deviceProfiles = \DeviceProfileTable::getInstance()->findByDeviceId($deviceId);
        return $deviceProfiles;
    }

}
