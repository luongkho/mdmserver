<?php

/**
 * Description: Device event service
 *  Push notification for each platform
 *  Get all command of each device
 *  Save, update information of each command to DeviceEvent table
 * Modify History:
 *  September 10, 2015: tannc initial version
 */

namespace Gcs\Repository;

use Gcs\Notification\NotificationCreator;

class DeviceEventRepository
{

    const GENERATE_PFN = "GeneratePFN";
    const EVENT_SUCCESS = 2;
    const LATEST_LOCATION = "GetLatestLocation";
    const READY = 0;

    /**
     * [push notification description]
     * @param  [string] $regIds [string]$type(android, ios, windowphone),
     * [string] $pushMagic (for push notification of ios)
     * @return [string] error
     */
    public function pushNotification($regIds, $type, $pushMagic)
    {
        $notificationCreate = new NotificationCreator();
        $notification = $notificationCreate->createNotification($type);
        $result = $notification->sendNotification($regIds, $pushMagic);
        return $result;
    }

    /**
     * [getCommandByDeviceId has status is ready]
     * @param  [int] $deviceId 
     * @return [DeviceEvent]         
     */
    public function getCommandByDeviceId($deviceId, $request_type = -1)
    {
        $eventModel = \DeviceEventTable::getInstance();
        // only take commands have status is ready (status = 0)
        if ($request_type > -1) {
            return $eventModel->findOneByDeviceIdAndStatusAndRequestType($deviceId, 0, $request_type);
        } else {
            return $eventModel->findOneByDeviceIdAndStatus($deviceId, 0);
        }
    }

    /**
     * [getFirstCommandByDeviceId description]
     * @param  [type] $deviceId [description]
     * @return [type]           
     */
    public function getFirstCommandByDeviceId($deviceId)
    {
        $commandModel = \CommandTable::getInstance();
        $query = $commandModel->createQuery('c')
                ->where('device_id = ?', $deviceId)
                ->andWhere('status == 0')
                ->limit(1)
                ->fetchOne();

        return $query;
    }

    /**
     * [save information to DeviceEvent table ]
     * @param  [int] $device_id,[string]$command_name,[int]$status, [int]$userId, [int] $profileId
     * @return [String]CommandUUID
     */
    public function saveCommand($deviceId, $commandName, $status, $userId, $profileId, $flag = true, $request_type = 0)
    {
        $deviceEventModel = new \DeviceEvent();
        $deviceEventModel->setDeviceId($deviceId);
        $deviceEventModel->setEventName($commandName);
        $deviceEventModel->setStatus($status);
        $deviceEventModel->setCommandUuid(uniqid('mdm_', true));
        $deviceEventModel->setRequestType($request_type);
        $device = \DeviceInventoryTable::getInstance()->findOneById($deviceId);
        if ($device) {
            $deviceModel = $device->getModel();
            $deviceEventModel->setModel($deviceModel);
        }
        switch ($commandName) {
            case "DeviceInformation":
            case "InstalledApplicationList":
            case "SecurityInfo":
            case "InstallApplication":
            case "GeneratePFN":
            case "GetLatestLocation":
                $deviceEventModel->setEventType('Information');
                $deviceEventModel->setManageEventFlag(0);
                break;
            case "RemoveProfile":
                $deviceEventModel->setCommandData($profileId);
                $deviceEventModel->setEventType('Security');
                break;
            case "InstallProfile":
                $profile = \ProfileTable::getInstance()->findOneById($profileId);
                $commandData = null;
                if ($profile) {
                    $commandData = $profileId . "," . $profile->getProfileName() . "," .
                            $profile->getPlatform() . "," . $profile->getConfigurationType();
                }
                $deviceEventModel->setCommandData($commandData);
                $deviceEventModel->setEventType('Security');
                break;
            default:
                $deviceEventModel->setEventType('Security');
                if (!$flag) {
                    $deviceEventModel->setEventType('Information');
                    $deviceEventModel->setManageEventFlag(0);
                }
                break;
        }
        if ($userId != null) {
            $userModel = \UserInfoTable::getInstance();
            $user = $userModel->findOneById($userId);
            if ($user != "") {
                $deviceEventModel->setSenderEmail($user->getEmail());
                $fullName = $user->getFirstName() . " " . $user->getLastName();
                $deviceEventModel->setOwnerName($fullName);
            }
        }
        $deviceEventModel->setSentBy($userId);
        $deviceEventModel->save();
        return $deviceEventModel->getCommandUuid();
    }

    /**
     * [save information to DeviceEvent table ]
     * @param  [int] $device_id, [string] $command_name, [int] $manage_event_flag,
     *  [int] $note, [int] $status, [int] $userId
     * @return [type]            [description]
     */
    public function saveDeviceEvent($deviceId, $commandName, $manage_event_flag, $note, $status, $userId)
    {
        $deviceEventModel = new \DeviceEvent();
        $deviceEventModel->setDeviceId($deviceId);
        $deviceEventModel->setEventName($commandName);
        $deviceEventModel->setManageEventFlag($manage_event_flag);
        $deviceEventModel->setNote($note);
        $deviceEventModel->setStatus($status);
        $deviceEventModel->setCommandUuid(uniqid('mdm_', true));

        $deviceModel = \DeviceInventoryTable::getInstance();
        $device = $deviceModel->findOneById($deviceId);
        if ($device) {
            $deviceModel = $device->getModel();
            $deviceEventModel->setModel($deviceModel);
        }
        if ($commandName == 'DeviceInformation') {
            $deviceEventModel->setEventType('Information');
        } else {
            $deviceEventModel->setEventType('Security');
        }
        // TO-DO: generate commandUDID and set to commandUDID.

        $userModel = \UserInfoTable::getInstance();
        $user = $userModel->findOneById($userId);
        if ($user != "") {
            $deviceEventModel->setSenderEmail($user->getEmail());
            $fullName = $user->getFirstName() . " " . $user->getLastName();
            $deviceEventModel->setOwnerName($fullName);
        }

        $deviceEventModel->setSentBy($userId);
        $deviceEventModel->save();
    }

    /**
     * update field note for device event table 
     * @param [DeviceEvent]$eventDevice, [int]$note, [int]$status
     * @param 
     */
    public function updateNoteAndStatus($eventDevice, $note, $status)
    {
        $eventDevice->setStatus($status);
        $eventDevice->setNote($note);
        $eventDevice->save();
    }

    /**
     * update field status for device event table 
     * @param [DeviceEvent]$eventDevice, [int]$status
     * @param 
     */
    public function updateStatus($eventDevice, $status)
    {
        $eventDevice->setStatus($status);
        $eventDevice->save();
    }

    /**
     * unEnroll device
     * @param  [int] $deviceId
     * @return [type]          
     */
    public function unEnroll($deviceId)
    {
        $deviceModel = \DeviceInventoryTable::getInstance()->findOneById($deviceId);
        $deviceEventModel = new DeviceEventRepository();

        if ($deviceModel) {
            // set enroll status to unenroll(1)
            $deviceModel->setEnrollStatus(1);
            $deviceModel->save();

            // update status of  all command belong to unenrolled device to Error
            // select all device_event of deviceId and status is ready
            $readyDeviceEvents = \DeviceEventTable::getInstance()->findbyDeviceIdAndStatus($deviceId, 0);
            foreach ($readyDeviceEvents as $deviceEvent) {
                if ($deviceEvent->getEventName() != 'Unenroll') {
                    $deviceEventModel->updateStatus($deviceEvent, 3);
                }
            }
            // select all device_event of deviceId and status is waiting 
            $waitingDeviceEvents = \DeviceEventTable::getInstance()->findbyDeviceIdAndStatus($deviceId, 1);
            foreach ($waitingDeviceEvents as $deviceEvent) {
                if ($deviceEvent->getEventName() != 'Unenroll') {
                    $deviceEventModel->updateStatus($deviceEvent, 3);
                }
            }
        }
    }

    /**
     * [clear registrationId when device is unenroll.
     * @param  [int] $deviceId 
     * @return [type]          
     */
    public function clearRegId($deviceId)
    {
        $deviceModel = \DeviceInventoryTable::getInstance()->findOneById($deviceId);
        if ($deviceModel) {
            //Delete RegistrationId - work for Andoird platform
            $deviceModel->setRegistrationId(null);
            //Delete PushMagic - work for IOS platform
            $deviceModel->setPushMagic(null);
            $deviceModel->setDeviceToken(null);
            $deviceModel->save();
        }
    }

    /**
     * [clear registrationId when device is unenroll.
     * @param  [int] $deviceId ,[int] $profileId
     * @return [type]          
     */
    public function saveProfile($deviceId, $profileId)
    {
        $deviceModel = \DeviceInventoryTable::getInstance()->findOneById($deviceId);
        if ($deviceModel) {
            $deviceModel->setRegistrationId(null);
            $deviceModel->save();
        }
    }

    /**
     * Get last command in device_event
     * @param [int] $deviceId
     * @return [date] updated_at
     */
    public function getLastCommandByDeviceId($deviceId)
    {
        $deviceEvent = \DeviceEventTable::getInstance()->createQuery('d')
                ->where('d.device_id = ?', $deviceId)
                ->orderBy('d.updated_at DESC')
                ->limit(1)
                ->fetchOne();
        return strtotime($deviceEvent->getUpdatedAt());
    }

    /**
     * Get last command in device_event
     * @param [String] $commandUuid
     * @return [DeviceEvent] $eventDevice
     */
    public function getCommandByCommandUuid($commandUuid)
    {
        $eventDevice = \DeviceEventTable::getInstance()->findOneByCommandUuid($commandUuid);
        return $eventDevice;
    }

    /**
     * get command based on command name, status and device id.
     * @param [string] $eventName, [int]$status, [int]$deviceId
     * @return [DeviceEvent] $eventDevice
     */
    public function getCommandByEventNameAndStatus($eventName, $status, $deviceId)
    {
        $eventDevice = \DeviceEventTable::getInstance()->findOneByEventNameAndStatusAndDeviceId($eventName, $status, $deviceId);
        return $eventDevice;
    }

    /**
     * change all command of the device which have status is waiting or ready to error
     * @param [int] $deviceId
     * @return 
     */
    public function changeCommandStatusError($deviceId)
    {
        $deviceEvents = \DeviceEventTable::getInstance()->findbyDeviceIdAndStatusOrStatus($deviceId, 0, 1);
        foreach ($deviceEvents as $deviceEvent) {
            if ($deviceEvent->getEventName() != 'Unenroll') {
                $this->updateStatus($deviceEvent, 3);
            }
        }
    }

    /**
     * get latest successful generatePFN command
     * @param [int] $deviceId
     * @return [object or null] $generatePFN
     */
    public function getLatestPFNCommand($deviceId)
    {
        $generatePFN = \DeviceEventTable::getInstance()->createQuery('e')
                        ->where('e.device_id = ?', $deviceId)->andWhere('e.event_name = ?', self::GENERATE_PFN)
                        ->andWhere('e.status = ?', self::EVENT_SUCCESS)
                        ->orderBy('e.updated_at DESC')->fetchOne();
        return $generatePFN;
    }

    /**
     * 
     * @param type $deviceId
     * @param type $request_type
     * @return boolean
     */
    public function checkExistedCommand($deviceId)
    {
        if (!($this->getCommandByEventNameAndStatus(self::LATEST_LOCATION, self::READY, $deviceId))) {
            return false;
        }
        return true;
    }

}
