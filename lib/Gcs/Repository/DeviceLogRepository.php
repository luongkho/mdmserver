<?php

/**
 * Description: Device Log service
 * save log from device to database
 * Modify History:
 *  September 10, 2015: luongmh initial version
 */

namespace Gcs\Repository;

class DeviceLogRepository
{

    /**
     * save log from device to database
     * @param type $device
     * @param type $content
     */
    public function saveLog($device, $content)
    {
        $newLog = new \DeviceLog;
        $newLog->setDeviceId($device->getId())
                ->setLogContent($content['LogContent']);
        $newLog->save();
    }

}
