<?php

/**
 * Description: Device Location service
 * Get, Update location of device
 * Modify History:
 *  September 10, 2015: luongmh initial version
 */

namespace Gcs\Repository;

use Gcs\Repository\DeviceInventoryRepository;
use Gcs\Repository\ProfileRepository;
use Gcs\Repository\ConfigRepository;

class DeviceLocationRepository
{

    /**
     * All device location information.
     *
     * @var array
     */
    private $attributes = array();

    /**
     * save Location of device
     * @param type $deviceUDID
     * @param type $lat
     * @param type $lon
     */
    public function saveLocation($deviceUDID, $lat, $lon)
    {
        $device = \DeviceInventoryTable::getInstance()->findOneByUdid($deviceUDID);
        if ($device) {
            $deviceId = $device->getId();
            $deviceLocation = \DeviceLocationTable::getInstance()->findOneByDeviceId($deviceId);
            if (!$deviceLocation) {
                $deviceLocation = new \DeviceLocation();
                $deviceLocation->setDeviceId($deviceId);
            }
            $deviceLocation->setLongitude($lon);
            $deviceLocation->setLatitude($lat);
            $deviceLocation->save();

            $deviceInventoryRepository = new DeviceInventoryRepository();
            $deviceInventoryRepository->setUpdateAtDeviceInventory($deviceId);
        }
    }

    /**
     * set location of device
     * @param type $device_id
     */
    public function setLocation($device_id)
    {
        $locationTable = \DeviceLocationTable::getInstance();
        $locationInformation = $locationTable->findOneBy('device_id', $device_id);
        if ($locationInformation['data']) {
            foreach ($locationInformation['data'] as $key => $value) {
                $this->attributes[$key] = $value;
            }
        }
    }

    /**
     * get Value of device by slug.
     *
     * @param [type] $slug [description]
     *
     * @return [type] [description]
     */
    public function getValueByAttributeName($slug)
    {
        if (isset($this->attributes[$slug])) {
            return $this->attributes[$slug];
        } else {
            return '-';
        }
    }

    /**
     * delete record if profile is "Location"
     * @param [integer] device Id and profile Id
     * @return
     */
    public function deleteDeviceLocation($profileId, $deviceId)
    {
        $profileRep = new ProfileRepository();
        $profile = $profileRep->getProfileById($profileId);
        $profileType = $profile->getConfigurationType();

        $configRep = new ConfigRepository();
        $locationType = $configRep->getLocationType();

        // If profile type is "Location"
        if (isset($locationType[$profileType])) {
            $this->deleteRecord($deviceId);
        }
    }

    /**
     * delete record by device Id
     * @param [integer] device Id
     * @return [bool] true if success
     */
    public function deleteRecord($deviceId)
    {
        $instance = $this->getRecordByDeviceId($deviceId);
        if ($instance) {
            $instance->delete();
        }
    }

    /**
     * find one record by device Id
     * @param [integer] device Id
     * @return [object] if found 
     */
    public function getRecordByDeviceId($deviceId)
    {
        return \DeviceLocationTable::getInstance()->findOneByDeviceId(intval($deviceId));
    }

}
