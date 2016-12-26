<?php

/**
 * Description: Interface for DeviceEvent service
 * 
 * Modify History:
 *  September 10, 2015: cuongnd initial version
 */

namespace Gcs\Contract;

interface DeviceEventRepositoryInterface {

    public function pushNotification($regIds, $type);

    public function getCommandByDeviceId($deviceId);

    public function getFirstCommandByDeviceId($deviceId);

    public function saveCommand($deviceId, $commandName, $status, $userId);

    public function saveDeviceEvent($deviceId, $commandName, $manage_event_flag, $note, $status, $userId);

    public function updateNoteAndStatus($eventDevice, $note, $status);

    public function updateStatus($eventDevice, $status);

    public function unEnroll($deviceId);

    public function clearRegId($deviceId);
}
