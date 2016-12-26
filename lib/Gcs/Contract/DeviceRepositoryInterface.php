<?php

/**
 * Description: Interface for Device service
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Contract;

interface DeviceRepositoryInterface {

    public function list_device($user_id);

    public function re_enroll($device_id);

    public function list_all_device($request, $platform, $deviceStatus);

    public function getOwner($device_id);

    public function setDeviveInformation($device_id);

    public function getValueByAttributeName($slug);

    public function getUpdateTimeOfGroup($group_id);
}
