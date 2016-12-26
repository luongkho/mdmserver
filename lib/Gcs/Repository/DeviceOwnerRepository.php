<?php

/**
 * Description: Owner Device Service
 * Get, Update owner of device
 * Modify History:
 *  September 10, 2015: luongmh initial version
 */

namespace Gcs\Repository;

class DeviceOwnerRepository
{

    private $owner;

    /**
     * set Owner by device id.
     *
     * @param string $device_id [description]
     */
    public function setOwner($device_id = '')
    {
        $ownerTable = \DeviceInventoryTable::getInstance();
        $this->owner = $ownerTable->findOneBy('id', $device_id);
    }

    /**
     * Get Owner Username.
     *
     * @param [type] $device_id [description]
     *
     * @return [type] [description]
     */
    public function getOwnerUsername()
    {
        $this->owner->getUserInfo()->getUsername();
    }

    /**
     * Get Owner Username.
     *
     * @param [type] $device_id [description]
     *
     * @return [type] [description]
     */
    public function getOwnerEmail()
    {
        return $this->owner->getUserInfo()->getEmail();
    }

    /**
     * Get Full name of owner.
     *
     * @param [type] $device_id [description]
     *
     * @return [type] [description]
     */
    public function getOwnerName()
    {
        return sprintf('%s %s', $this->owner->getUserInfo()->getFirstName(), $this->owner->getUserInfo()->getLastName());
    }

}
